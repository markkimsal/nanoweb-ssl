<?php

/*

Nanoweb Gzip Transfer Encoding Support Module
=============================================

Copyright (C) 2002-2003 Vincent Negrier aka. sIX <six@aegis-corp.org>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2, or (at your option)
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.


   # it now adds itself to the parser/filter chain
   # <mario@erphesfurt·de>

*/

class mod_gzip extends pfilter {

	function mod_gzip() {

		$this->modtype = "core_after_pfilters";
		$this->modname = "gzip content encoding support";

	}


	function init() {

		register_filter("gzip", $this, NW_PFILTER_ALL);

	}


	function main() {

		if ((access_query("gzipenable", 0) && $GLOBALS["gz_av"])) {

			global $lf;

			$this->pp = $lf;
			$this->content_length = $this->pp->content_length;
			$lf=$this;

		}

	}


	function filter_func(&$lf, $args) {

		global $htreq_headers, $out_add_headers, $first_chunk, $chunky;

		if ($first_chunk) { 

			$this->gz_method = false;
			foreach (array("deflate", "gzip", "compress") as $m) {
				if (strpos($htreq_headers["ACCEPT-ENCODING"], $m) !== false) {
					$this->gz_method = $m;
					break;
				}
			}

			if (!($this->gz_level=access_query("gziplevel", 0))) {
				$this->gz_level=3;
			}

			$this->engaged = $this->gz_method && empty($out_add_headers["Content-Encoding"]);
			$this->engaged = $this->engaged && ( !($chunky || (nw_use_chunked_encoding()==true)) || (access_query("gzipenable",0)>=2) );

		}

		if ($this->engaged) {

			switch ($this->gz_method) {
				case "deflate":
					$gz_content=gzdeflate($lf, $this->gz_level);
					break;
				case "compress":
					$gz_content=gzcompress($lf, $this->gz_level);
					break;
				case "gzip":
					$gz_content=gzencode($lf, $this->gz_level); //(..., FORCE_DEFLATE) ??
				default:
			}

#techo("compressing " . strlen($lf) . " bytes using " . $this->gz_method . " level=".$this->gz_level);

			if (!$chunky || isset($this->pp->content_length)) {

				if (!$maxratio=(access_query("gzipmaxratio", 0)/100)) $maxratio=0.90;

				if (!(strlen($gz_content)<(strlen($lf)*$maxratio))) {

					return(0);

				}

				$this->content_length = strlen($gz_content);
			}

			$lf=$gz_content;

			$out_add_headers["Content-Encoding"]=$this->gz_method;

		}
		else {

			$this->content_length = $this->pp->content_length;

		}
		
	}

}

?>
