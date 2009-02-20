<?php

/*

Nanoweb MySQL Auth Module
=========================

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

Use these directives in a conf/vhost/access file to use mod_auth_mysql

AuthRealm      = your auth realm name here
AuthRequire    = MYSQL
AuthMysqlHost  = localhost
AuthMysqlUser  = db_user
AuthMysqlPass  = db_pass
AuthMysqlDB    = db_name
AuthMysqlTable = table_name
AuthMysqlPassType = plain | crypt | md5 | mysql
AuthMysqlLoginColumn = login_field_name
AuthMysqlPassColumn  = password_field_name

Password types are 

plain : password is plaintext
crypt : password is hashed using the system crypt()
md5   : password is hashed using the md5 algorithm
mysql : password is hashed using the mysql password algorithm

*/

class mod_auth_mysql {

	function mod_auth_mysql() {

		$this->modtype="auth_mysql";
		$this->modname="MySQL authentication";

	}

	function auth($user, $pass, $args) {

		$host=access_query("authmysqlhost", 0);
		$dbuser=access_query("authmysqluser", 0);
		$dbpass=access_query("authmysqlpass", 0);
		$dbname=access_query("authmysqldb", 0);
		$tbname=access_query("authmysqltable", 0);
		$lname=access_query("authmysqllogincolumn", 0);
		$pname=access_query("authmysqlpasscolumn", 0);
		
		$ps="'".addslashes($pass)."'";
		
		switch (strtolower(access_query("authmysqlpasstype", 0))) {

			case "crypt": 
			$pstr="encrypt(".$ps.")"; 
			break;
			
			case "md5":
			$pstr="md5(".$ps.")"; 
			break;

			case "mysql":
			$pstr="password(".$ps.")"; 
			break;

			case "plain":
			default:
			$pstr=$ps;
		
		}
		
		if ($cid=@mysql_pconnect($host, $dbuser, $dbpass)) {

			mysql_select_db($dbname, $cid);
			
			if ($q=@mysql_query("select 1 from ".$tbname." where ".$lname."='".addslashes($user)."' and ".$pname."=".$pstr)) {
			
				$r=mysql_num_rows($q);
				mysql_free_result($q);

				$auth=($r>0);

			} else {

				techo("WARN: mod_auth_mysql could not fetch '$lname' and '$pname' from table '$tbname'", NW_EL_WARNING);
			
			}

		} else {

			techo("WARN: mod_auth_mysql could not connect to database '$dbname@$host'", NW_EL_WARNING);
		
		}

		return($auth);
	
	}

}

?>
