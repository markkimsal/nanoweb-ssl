<?php

/*

Nanoweb DoS Evasive Maneuvers Module
====================================

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

class mod_dosevasive {

	function mod_dosevasive() {

		$this->modtype="core_after_decode";
		$this->modname="DoS evasive maneuvers";
	
	}

	function main() {

		global $real_uri, $vhost, $add_errmsg, $pri_err, $query_string;
		static $detable, $detimer;

		$t=time();
		$tmax=access_query("dosevasivetimer", 0) or $tmax=10;
		$dmax=access_query("dosevasivemaxreqs", 0) or $dmax=5;
		
		if ($t>($detimer+$tmax)) {
			
			// Clean table on timer
			
			$detable=array();
			$detimer=$t;

		}

		if ($detable[$vhost.$real_uri.$query_string]>=$dmax) {

			// Discard request with DosEvasiveError if requested more than DosEvasiveMaxReqs in DocEvasiveTimer seconds

			$e=access_query("dosevasiveerror", 0) or $e=403;
			$pri_err=$e;
			$add_errmsg="You are not allowed to request a resource more than <b>".(int)$dmax."</b> times in <b>".(int)$tmax."</b> seconds.<br><br>";

			if ($bt=access_query("dosevasiveblocktime", 0)) {
				
				if (strtolower($bt)=="perm") {
				
					nw_block_ip_address($GLOBALS["remote_ip"], "PERM", "mod_dosevasive");

				} else {

					nw_block_ip_address($GLOBALS["remote_ip"], "TEMP", "mod_dosevasive", time()+$bt);

				}

			}

		}
		
		// Update url table

		$detable[$vhost.$real_uri.$query_string]++;

	}

}

?>
