<?php

/*

Nanoweb SSI Parsing module
==========================

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

class mod_include {

	function mod_include() {

		$this->modtype="parser_SSI";
		$this->modname="Server Side Includes support";
	
	}

	function parser_open($args, $filename, $rq_err, $cgi_headers) {

		$f=fopen($filename, NW_BSAFE_READ_OPEN);
		$content=fread($f, filesize($filename));
		fclose($f);
		
		while (($p1=strpos($content, "<!--#"))!==false) {

			$s=substr($content, $p1+5);
			
			if (!$p2=strpos($s, "-->")) {
				
				techo("WARN: SSI parse error in ".$filename, NW_EL_WARNING);
				$rq_err=500;

			}
			
			$s=trim(substr($s, 0, $p2));
			$tmp=explode("=", $s);

			if (count($tmp)!=2) {

				techo("WARN: SSI parse error in ".$filename, NW_EL_WARNING);
				$rq_err=500;

			}

			$cmd=strtolower(trim($tmp[0]));
			$arg=trim($tmp[1]," \"");

			switch($cmd) {

				case "include virtual":
				$ext=strstr($arg, ".");
				$tmp=loadfile($docroot.$arg, substr($ext, 1), $rq_err, $add_headers);
				$repl=$tmp->parser_get_output();
				$tmp->parser_close();
				break;

				case "include file":
				$ext=strstr($arg, ".");
				$tmp=loadfile("./".$arg, substr($ext, 1), $rq_err, $add_headers);
				$repl=$tmp->parser_get_output();
				$tmp->parser_close();
				break;

				case "exec cmd":
				$repl=`$arg`;
				break;

			}

			$content=substr($content, 0, $p1).$repl.substr($content, $p1+$p2+8);
	
		}

		$this->parsed_content=$content;
	
	}

	function parser_get_output() {

		$tmp=$this->parsed_content;
		$this->parsed_content="";

		return($tmp);
	
	}
	
	function parser_eof() {

		return($this->parsed_content==="");
	
	}
	
	function parser_close() {

	}
	
}

?>
