<?php

/*

Nanoweb .nwauth Auth Module
===========================

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


Usage
=====

Use these directives in a conf/vhost/access file to use mod_auth_nwauth

AuthRealm = your auth realm name here
AuthRequire = NWAUTH
AuthNwauthFilename = /var/www/vhosts/www.example.com/admin/.nwauth

*/

class mod_auth_nwauth {

	function mod_auth_nwauth() {

		$this->modtype="auth_nwauth";
		$this->modname="backwards compatible .nwauth authentication";

	}

	function auth($user, $pass, $args) {

		$authfile=file(access_query("authnwauthfilename", 0));
		$authcount=count($authfile);

		for ($a=1;$a<=$authcount;$a++) {
			
			$lp=explode(":", trim($authfile[$a]));
			$authdata["users"][$lp[0]]["login"]=$lp[0];
			$authdata["users"][$lp[0]]["des-password"]=$lp[1];

		}

		$hash=$authdata["users"][$user]["des-password"];
		$thash=crypt($pass ,substr($hash, 0, 2));

		return ($hash===$thash);

	}

}

?>
