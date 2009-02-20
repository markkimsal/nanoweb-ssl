<?php

/*

Nanoweb ip blocking administration helper module
================================================

Copyright (C) 2002-2005 Vincent Negrier aka. sIX <six@aegis-corp.org>

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

class mod_blockadm {

	var $modtype = "url";
	var $modname = "ip address blocking admin helper";
	var $urls = array("/blockadm");
	
	function adm_allowed() {

		global $conf, $remote_ip;
		
		foreach ($conf["global"]["blockadmallowip"] as $allowed) if (($remote_ip === $allowed) || ((substr($remote_ip, 0, strlen($allowed) - 1) . "*") === $allowed)) return true;

		return false;
	
	}
	
	function url(&$rq_err, &$out_coutenttype, &$add_headers) {

		global $query_string;
		
		if ($this->adm_allowed()) {

			$rq_err = 200;
			$out_contenttype = "text/plain";
			
			parse_str($query_string, $params);

			if (!$params["addr"]) {

				return "ERROR: you must specify an IP address to block or unblock";

			}

			if (strtolower($params["dur"]) == "perm") {

				$type = "PERM";
				$expires = 0;

			} else {

				$type = "TEMP";
				$expires = time() + ($params["dur"] ? $params["dur"] : 3600);

			}

			switch (strtolower($params["act"])) {

				case "unblock":
				
				nw_unblock_ip_address($params["addr"], "mod_blockadm");
				
				$msg = "mod_blockadm : unblocked IP address ".$params["addr"];
				
				break;

				
				case "block":
				default:

				nw_block_ip_address($params["addr"], $type, "mod_blockadm", $expires);
				
				$msg = "mod_blockadm : blocked IP address ".$params["addr"]." (".strtolower($type).")";

				break;
			
			}
			
			return $msg;

		} else {

			$rq_err = 404;

			return false;
		
		}
		
	}

}

?>
