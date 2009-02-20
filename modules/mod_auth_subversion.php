<?php

/*

Nanoweb Subversion Auth Module
==============================

Copyright (C) 2004 Jimbo <jimbo@aegis-corp.org>

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

Use these directives in a conf/vhost/access file to use mod_auth_subversion

AuthRealm = your auth realm name here
AuthRequire = SUBVERSION
AuthSvnFile = /var/svn/conf/passwd

*/

class mod_auth_subversion {
  function mod_auth_subversion() {
    $this->modtype="auth_subversion";
    $this->modname="Subversion repository authentication";
  }

  function auth($user, $pass, $args) {
    foreach (access_query("authsvnfile") as $asvn) {
      $file =fopen($asvn,"r");	
      while (!feof($file))
      {
  	$lp = explode("=",ereg_replace(" *","",chop(fgets($file,4096))));
	if (($lp[0]==$user) && ($lp[1]==$pass)) {
	  $auth=true;
	  break;
	}
      }
    }
    return($auth);
  }
}

?>
