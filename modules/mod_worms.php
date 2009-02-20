<?php

/*

Nanoweb Worms Detection Module
==============================

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

class mod_worms {

	function mod_worms() {

		$this->modtype="core_before_decode";
		$this->modname="HTTP worms detection";
		$this->urls=array("/scripts/root.exe", "/default.ida");

	}

	function main() {

		global $conf, $query_string, $pri_err;

		if (strpos($query_string, "system(chr(") !== false) {
			
			$wormid = "phpbb";

		} else if ((strpos($query_string, "wget") !== false) && (strpos($query_string, "perl") !== false)) {

			$wormid = "probable";

		}

		if (!isset($wormid)) return;
	
		if ($bt=access_query("wormsblocktime", 0)) {
			
			// Block source IP address
			
			$bsrc="mod_worms.".$wormid;
			
			if (strtolower($bt)=="perm") {
			
				nw_block_ip_address($GLOBALS["remote_ip"], "PERM", $bsrc);

			} else {

				nw_block_ip_address($GLOBALS["remote_ip"], "TEMP", $bsrc, time()+$bt);

			}

		}
		
		if ($conf["global"]["wormsrun"]) while (list($key, $cmd)=each($conf["global"]["wormsrun"])) if ($cmd) {
		
			// Do WormsRun
			
			$cmd=str_replace("$"."REMOTE_IP", $GLOBALS["remote_ip"], $cmd);
			$cmd=str_replace("$"."REMOTE_HOST", $GLOBALS["remote_host"], $cmd);
		
			exec($cmd);

		}

		if ($conf["global"]["wormswpoptext"]) {

			// Do WormsWpopText
			
			while (list($key, $msgline)=each($conf["global"]["wormswpoptext"])) $msg.=$msgline."\n";
		
			$msg=str_replace("$"."SERVERNAME", $conf[$vhost]["servername"][0], $msg);
			$msg=str_replace("$"."SERVERADMIN", $conf[$vhost]["serveradmin"][0], $msg);

			if ($p=@popen("wpop ".$GLOBALS["remote_ip"], "w")) {

				fputs($p, $msg);
				pclose($p);
			
			} else {

				techo("mod_worms: unable to popen() wpop", NW_EL_WARNING);
			
			}

		}
		
		// Return 404 Not found
		
		$pri_err=404;
		return "";
	
	}

	function url(&$rq_err, &$out_contenttype, &$out_add_headers) {

		global $conf, $vhost;

		if (strpos($GLOBALS["http_uri"], "root.exe")!==false) $wormid="Nimda";
		else if ($GLOBALS["query_string"]{0}=="N") $wormid="CodeRed";
		else if ($GLOBALS["query_string"]{0}=="X") $wormid="CodeRed2";
		else $wormid="unknown";
		
		if ($bt=access_query("wormsblocktime", 0)) {
			
			// Block source IP address
			
			$bsrc="mod_worms.".$wormid;
			
			if (strtolower($bt)=="perm") {
			
				nw_block_ip_address($GLOBALS["remote_ip"], "PERM", $bsrc);

			} else {

				nw_block_ip_address($GLOBALS["remote_ip"], "TEMP", $bsrc, time()+$bt);

			}

		}
		
		if ($conf["global"]["wormsrun"]) while (list($key, $cmd)=each($conf["global"]["wormsrun"])) if ($cmd) {
		
			// Do WormsRun
			
			$cmd=str_replace("$"."REMOTE_IP", $GLOBALS["remote_ip"], $cmd);
			$cmd=str_replace("$"."REMOTE_HOST", $GLOBALS["remote_host"], $cmd);
		
			exec($cmd);

		}

		if ($conf["global"]["wormswpoptext"]) {

			// Do WormsWpopText
			
			while (list($key, $msgline)=each($conf["global"]["wormswpoptext"])) $msg.=$msgline."\n";
		
			$msg=str_replace("$"."SERVERNAME", $conf[$vhost]["servername"][0], $msg);
			$msg=str_replace("$"."SERVERADMIN", $conf[$vhost]["serveradmin"][0], $msg);

			if ($p=@popen("wpop ".$GLOBALS["remote_ip"], "w")) {

				fputs($p, $msg);
				pclose($p);
			
			} else {

				techo("mod_worms: unable to popen() wpop", NW_EL_WARNING);
			
			}

		}
		
		// Return 404 Not found
		
		$rq_err=404;
		return("");
	
	}

}

?>
