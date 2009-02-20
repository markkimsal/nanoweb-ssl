<?php

/*

Nanoweb Static Content module
=============================

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

*/

class mod_static {

	function mod_static() {

		$this->modtype="parser_static";
		$this->modname="Static content support";
	
	}

	function parser_open($args, $filename, &$rq_err, &$cgi_headers) {

		global $conf, $htreq_headers;

		unset($this->fp);
		
		// Generate ETag value for resource
			
		$fmt=filemtime($filename);
		$fs=filesize($filename);
		$etag="\"".dechex(fileinode($filename)).":6r0x:".dechex($fmt).":".dechex($fs)."\"";

		$rq_ifm=$rq_ifnm=$rq_ims=$rq_ius=true;

		// Test If-Match request header (cache helper)

		if ($hdr_inm=$htreq_headers["IF-MATCH"]) {

			$rq_ifm=false;
			$inms=explode(",", trim($hdr_inm));

			foreach ($inms as $inm_tag) {
				
				$inm_tag=trim($inm_tag);
				
				if ($inm_tag==$etag || $inm_tag=="*") {

					$rq_err=304;
					$cgi_headers["ETag"]=$etag;
					$rq_ifm=true;

				}
			
			}

		}

		// Test If-None-Match request header (cache helper)

		if ($hdr_inm=$htreq_headers["IF-NONE-MATCH"]) {

			$inms=explode(",", trim($hdr_inm));

			foreach ($inms as $inm_tag) {
				
				$inm_tag=trim($inm_tag);
				
				if ($inm_tag==$etag || $inm_tag=="*") {

					$rq_err=304;
					$cgi_headers["ETag"]=$etag;
					$rq_ifnm=false;

				}
			
			}
		
		}
		
		// Test If-Unmodified-Since request header (cache helper)
		
		if ($lmdate=$htreq_headers["IF-UNMODIFIED-SINCE"]) {

			$lmdate=(float)strtotime($lmdate);

			if ($fmt>$lmdate) {

				$rq_err=304;
				$cgi_headers["Last-Modified"]=gmdate("D, d M Y H:i:s T", $fmt);
				$rq_ims=false;
			
			}
		
		}
		
		// Test If-Modified-Since request header (cache helper)
		
		if ($lmdate=$htreq_headers["IF-MODIFIED-SINCE"]) {

			$lmdate=(float)strtotime($lmdate);

			if ($fmt<=$lmdate) {

				$rq_err=304;
				$cgi_headers["Last-Modified"]=gmdate("D, d M Y H:i:s T", $fmt);
				$rq_ius=false;
			
			}
		
		}

		if (!($rq_ifm && $rq_ifnm && $rq_ims && $rq_ius)) {
			
			$this->peof=true;
			return(false);

		}
		
		// Cache helpers end

		$this->rng_from=0;
		$this->content_length=$this->rng_to=$fs;
			
		$cgi_headers["Last-Modified"]=gmdate("D, d M Y H:i:s T", $fmt);
		$cgi_headers["ETag"]=$etag;
		$cgi_headers["Accept-Ranges"]="bytes";

		if ($rngt=trim($htreq_headers["IF-RANGE"])) {

			$process_range=($rngt==$etag);
		
		} else $process_range=true;
		
		if (($process_range) && ($rhdr=$htreq_headers["RANGE"])) {

			// Client asked HTTP Resume

			if (!access_query("staticdisablepartial", 0)) {
			
				$tmparr=explode("=", $rhdr);

				if (strtolower(trim($tmparr[0]))=="bytes") {

					$rngarr=explode("-", $tmparr[1]);

					if ($rngarr[0]==="") {

						// Range: bytes=-###

						if ($rngarr[1]>$fs) {

							$rng_from="*";

						} else {
						
							$rng_from=$fs-$rngarr[1];
							$rng_to=$fs-1;

						}

					} else if ($rngarr[1]=="") {

						// Range: bytes=###-

						if ($rngarr[0]>$fs) {

							$rng_from="*";

						} else {

							$rng_from=$rngarr[0];
							$rng_to=$fs-1;
						
						}

					} else {

						// Range: bytes=###-###

						if (($rngarr[0]>$fs) || ($rngarr[1]>$fs) || ($rngarr[0]>$rngarr[1])) {

							$rng_from="*";

						} else {

							$rng_from=$rngarr[0];
							$rng_to=$rngarr[1];
						
						}
					
					}

				} else {

					// Ranges unit not supported

					$rng_from="*";
				
				}

				if ($rng_from==="*") {

					// Send all content with 416

					$rq_err=416;
					$cgi_headers["Content-Range"]="*";

				} else {

					// Send partial content

					$rq_err=206;
					$this->rng_from=$rng_from;
					$this->rng_to=$rng_to;
					$this->content_length=($rng_to-$rng_from)+1;
					$cgi_headers["Content-Range"]="bytes ".$rng_from."-".$rng_to."/".$fs;

				}
		
			} else {

				// Partial content has been disabled in conf

				$rq_err=416;
				$cgi_headers["Content-Range"]="*";
			
			}
		
		}

		if ($this->fp=@fopen($filename, NW_BSAFE_READ_OPEN)) {

			if ($this->rng_from) {
				
				fseek($this->fp, $rng_from);
				$this->cur_ptr=$rng_from;

			} else {

				$this->cur_ptr=0;

			}

			$this->peof=false;

			// Return a static_response if possible

			if ($this->content_length<=$conf["global"]["staticbuffersize"][0]) {

				$content=fread($this->fp, $this->content_length);
				$this->parser_close();

				return(new static_response($content));
			
			}

		} else {

			$rq_err=404;
			$this->peof=true;
		
		}

	}

	function parser_get_output() {

		if ($this->fp) {
		
			$sbf=$GLOBALS["conf"]["global"]["staticbuffersize"][0];
			
			if ($this->cur_ptr+$sbf<=$this->rng_to) $read_len=$sbf;
			else $read_len=$this->rng_to-$this->cur_ptr+1;
			
			$content=fread($this->fp, $read_len);
			$this->cur_ptr+=$read_len;
			$this->peof=$this->cur_ptr>=$this->rng_to;

			return($content);

		}
	
	}
	
	function parser_eof() {

		return($this->fp && $this->peof);
	
	}
	
	function parser_close() {

		if ($this->fp) fclose($this->fp);
		unset($this->content_length);

	}

}

?>
