<?php

/*

Nanoweb Spammers Detection Module
=================================

Copyright (C) 2006 Vincent Negrier aka. sIX <six@aegis-corp.org>

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

class mod_nospam {

	function mod_nospam() {

		$this->modtype="core_after_decode";
		$this->modname="www spam detection";

	}

	function main() {

		global $conf, $query_string, $htreq_content;

		$sc = access_query("spamcheck", 0);

		if ($sc) {

			$cget = (strpos($sc, "GET") !== false);
			$cpost = (strpos($sc, "POST") !== false);
		
		} else {

			return "";

		}

		if ($cget) $dget = urldecode($query_string);
		if ($cpost) $dpost = urldecode($htreq_content);

		if ($rl = access_query("spamregex")) foreach ($rl as $k => $sreg) if (($cget && preg_match($sreg, $dget)) || ($dget && preg_match($sreg, $dpost))) {

			if (!isset($bt)) $bt = access_query("spamblocktime", 0);

			// Block source IP address
			
			$bsrc="mod_nospam.".$k;
			
			if (strtolower($bt)=="perm") {
			
				nw_block_ip_address($GLOBALS["remote_ip"], "PERM", $bsrc);

			} else {

				nw_block_ip_address($GLOBALS["remote_ip"], "TEMP", $bsrc, time() + $bt);

			}

			// Return 403 Forbidden
			
			$GLOBALS["pri_err"] = access_query("spamblockerror", 0);

			if ($msg = access_query("spamblockmessage", 0)) $GLOBALS["add_errmsg"] .= $msg . "<br><br>";
			
			return "";

		}
		
		if ($ladd = access_query("spamrewritelinks", 0)) {

			$rep = preg_replace("/(<a[^>]+href[^>]+)>/i", "\\1 {$ladd}>", array($dget, $dpost));

			if ($cget) $query_string = $rep[0];
			if ($cpost) $htreq_content = $rep[1];
		
		}
	
	}

}

?>