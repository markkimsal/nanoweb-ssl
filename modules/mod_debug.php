<?php

/*

Nanoweb debugging module
========================

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

class mod_debug {

	function mod_debug() {

		$this->modtype="core_before_response";
		$this->modname="nanoweb debugger (mostly a dev tool)";

	}

	function sprint_r($arr, $lvl=0) {

		$fill=$lvl*4;
		for ($a=0;$a<$fill;$a++) $filler.=" ";
		
		foreach ($arr as $key=>$val) {

			if ($key==="GLOBALS") {

			} else if (is_object($val)) {

			} else if (is_array($val)) {

				$ret.=$filler."[$key] => Array = { \n\n".$this->sprint_r($val, $lvl+1).$filler."}\n\n";
			
			} else {

				$ret.=$filler."[$key] => $val\n\n";

			}
		
		}

		return($ret);

	}

	function main() {

		global $conf, $rq_err, $mypid;

		if (in_array($rq_err, access_query("debugerror"))) {
		
			$fn=$conf["global"]["tempdirectory"][0]."/nwdebug.".(int)$mypid;

			$s=date("Ymd-His")." - nanoweb debug session -----------------------\n";
			$s.=$this->sprint_r($GLOBALS);
			$s.=date("Ymd-His")."------------------------------------------------\n\n";

			if ($f=@fopen($fn, "a")) {

				fwrite($f, $s);
				fclose($f);

			} else {

				techo("WARN: mod_debug was unable to open $fn for writing");
			
			}

		}
	
	}

}

?>
