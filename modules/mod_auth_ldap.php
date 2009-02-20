<?php

/*

Nanoweb LDAP Auth Module
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


Usage
=====

Use these directives in a conf/vhost/access file to use mod_auth_nwauth

AuthRealm = your auth realm name here
AuthRequire = LDAP
AuthLDAPServer = ldap.example.com
AuthLDAPBindDN = cn=%AUTH_USER%,ou=people,dc=example,dc=com
AuthLDAPMatchFilter = (&(attrfilter=val)(httpaccess=on))

In AuthLDAPBindDN, %AUTH_USER% is replaced with the actual login provided by
the HTTP client. You may also use %AUTH_USER_U% and %AUTH_USER_D% if you want
to allow user@domain type logins.

*/

class mod_auth_ldap {

	var $modtype="auth_ldap";
	var $modname="LDAP authentication";
	
	function init() {

		if (!is_callable("ldap_connect")) {

			techo("WARN: mod_auth_ldap needs a php binary compiled with LDAP support", NW_EL_WARNING);
			$this->ldapless_php=true;
		
		}
	
	}
	
	function auth($user, $pass, $args) {

		if ($this->ldapless_php) {

			return(false);

		} else if ($ldsrvs=access_query("authldapserver")) {

			foreach ($ldsrvs as $ld_srv) if ($ld_cid=ldap_connect($ld_srv)) break;
	
			if (!$ld_cid) {

				techo("WARN: mod_auth_ldap: unable to connect to server(s)", NW_EL_WARNING);
				return(false);
			
			}

		} else {

			techo("WARN: mod_auth_ldap: no AuthLDAPServer specified", NW_EL_WARNING);
			ldap_close($ld_cid);
			return(false);
		
		}
		
		$ld_dn=access_query("authldapbinddn", 0);
		$eu=explode("@", $user);
		
		$ld_dn=str_replace("%AUTH_USER%", $user, $ld_dn);
		$ld_dn=str_replace("%AUTH_USER_U%", $eu[0], $ld_dn);
		$ld_dn=str_replace("%AUTH_USER_D%", $eu[1], $ld_dn);

		if ($ld_bind=ldap_bind($ld_cid, $ld_dn, $pass)) {

			if ($ld_filter=access_query("authldapmatchfilter", 0)) {

				if ($ld_q=ldap_search($ld_cid, $ld_dn, $ld_filter)) {

					if ($a=ldap_count_entries($ld_cid, $ld_q)) {
					
						ldap_close($ld_cid);
						return(true);

					} else {

						ldap_close($ld_cid);
						return(false);

					}
				
				} else {

					ldap_close($ld_cid);
					return(false);
				
				}
			
			} else {

				ldap_close($ld_cid);
				return(true);
			
			}
		
		} else {
			
			ldap_close($ld_cid);
			return(false);

		}
		
	}

}

?>
