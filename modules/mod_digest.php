<?php

/*

Nanoweb content digest module
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

class mod_digest {

	var $modtype="core_before_response";
	var $modname="MD5 content digest generation";
	
	function main() {

		global $http_action, $out_add_headers;

		$ml=$http_action=="HEAD"?$GLOBALS["hlf"]:$GLOBALS["lf"];
		if (is_a($ml, "static_response") && $ml->str && access_query("digestmd5", 0)) $out_add_headers["Content-MD5"]=base64_encode(pack("H*", md5($ml->str)));
	
	}

}

?>
