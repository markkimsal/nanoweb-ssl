<?php

/*

Nanoweb PostgreSQL Auth Module
==============================

Copyright (C) 2002 Szilveszter Farkas aka. Phanatic <linux@psoftwares.hu>

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

Use these directives in a conf/vhost/access file to use mod_auth_pgsql

AuthRealm      = your auth realm name here
AuthRequire    = PGSQL
AuthPgsqlHost  = localhost
AuthPgsqlUser  = db_user
AuthPgsqlPass  = db_pass
AuthPgsqlDB    = db_name
AuthPgsqlTable = table_name
AuthPgsqlPassType = plain | md5
AuthPgsqlLoginColumn = login_field_name
AuthPgsqlPassColumn  = password_field_name

Password types are 

plain : password is plaintext
md5   : password is hashed using the md5 algorithm

*/

class mod_auth_pgsql {

	function mod_auth_pgsql() {

		$this->modtype="auth_pgsql";
		$this->modname="PostgreSQL authentication";

	}

	function auth($user, $pass, $args) {

		$host=access_query("authpgsqlhost", 0);
		$dbuser=access_query("authpgsqluser", 0);
		$dbpass=access_query("authpgsqlpass", 0);
		$dbname=access_query("authpgsqldb", 0);
		$tbname=access_query("authpgsqltable", 0);
		$lname=access_query("authpgsqllogincolumn", 0);
		$pname=access_query("authpgsqlpasscolumn", 0);
		
		$ps=trim($pass);
		
		switch (strtolower(access_query("authpgsqlpasstype", 0))) {

			case "md5":
			$pstr=md5($ps); 
			break;

			case "plain":
			default:
			$pstr=$ps;
		
		}
		
		if (is_callable("pg_connect")) {
		
			if ($cid=@pg_connect("host=$host user=$dbuser password=$dbpass dbname=$dbname")) {

				if ($q=@pg_query($cid, "SELECT * FROM $tbname WHERE $lname = '$user' AND $pname = '$pstr'")) {
					$r=pg_num_rows($q);
					pg_free_result($q);

					$auth=($r>0);
					
				} else {

					techo("WARN: mod_auth_pgsql could not fetch '$lname' and '$pname' from table '$tbname'", NW_EL_WARNING);
				
				}

			} else {

				techo("WARN: mod_auth_pgsql could not connect to database '$dbname@$host'", NW_EL_WARNING);
			
			}

		} else {

			techo("WARN: postgresql extension not built in your PHP binary", NW_EL_WARNING);
		
		}
		
		return($auth);
	
	}

}

?>
