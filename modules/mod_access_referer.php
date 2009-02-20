<?php

/*

Nanoweb Server HTTP Referer Based Access Module
===============================================

Copyright (C) 2002-2004 Vincent Negrier aka. sIX <six@aegis-corp.org>

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

class mod_access_referer {

	var $modtype = "core_after_decode";
	var $modname = "Deny linking from external sites";

    function main() {

        global $conf, $pri_err, $add_errmsg;

		if (!access_query("referercheck", 0)) return;
		
		$ref = $GLOBALS["htreq_headers"]["REFERER"];

		if (!$ref) return;
		
		if (strpos($ref, "http://".strtolower($GLOBALS["vhost"])) === 0) return;

		foreach (access_query("refererallow") as $ref_allow) if (strpos(strtolower($ref), strtolower($ref_allow)) === 0) return;
		
		$pri_err = 403;
		$add_errmsg = "External links to this resource are not allowed.<br><br>Please inform the maintainer of the originating web page at <a href='$ref'>$ref</a>.<br><br>";

    }

}

?>
