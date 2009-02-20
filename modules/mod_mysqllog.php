<?php

/*

Nanoweb MySQL logging module
============================

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

class mod_mysqllog {

	function mod_mysqllog() {

		global $conf;
		
		$this->modtype="log";
		$this->modname="MySQL logging";

		if (!$cid=@mysql_pconnect($conf["global"]["mysqlloghost"][0], $conf["global"]["mysqlloguser"][0], $conf["global"]["mysqllogpassword"][0])) errexit("Unable to connect to database");
		mysql_close($cid);

	}

	function log_hit($vhost, $remote_host, $remote_ip, $logged_user, $http_request, $rq_err, $sent_content_length, $http_referer, $http_user_agent) {

		global $conf;

		if ($conf[$vhost]["mysqllog"][0]) $mlog=$conf[$vhost]["mysqllog"][0];
		else if ($conf["global"]["mysqllog"][0]) $mlog=$conf["global"]["mysqllog"][0];
		else $mlog=false;

		if ($mlog) {
		
			$cid=mysql_pconnect($conf["global"]["mysqlloghost"][0], $conf["global"]["mysqlloguser"][0], $conf["global"]["mysqllogpassword"][0]);
			mysql_select_db($conf["global"]["mysqllogdatabase"][0]);

			mysql_query("CREATE TABLE IF NOT EXISTS `".$mlog."` (`hit_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `hit_datetime` DATETIME NOT NULL, `hit_vhost` VARCHAR(64) NOT NULL, `hit_host` VARCHAR(128) NOT NULL, `hit_ip` VARCHAR(16) NOT NULL, `hit_authuser` VARCHAR(16) NOT NULL, `hit_request` VARCHAR(255) NOT NULL, `hit_status` INT NOT NULL, `hit_size` INT NOT NULL, `hit_referer` VARCHAR(255) NOT NULL, `hit_useragent` VARCHAR(255) NOT NULL, INDEX (`hit_vhost`))");
			
			mysql_query("INSERT INTO `".$mlog."` VALUES (0, now(), \"".addslashes($vhost)."\", \"".addslashes($remote_host)."\", \"".addslashes($remote_ip)."\", \"".addslashes($logged_user)."\", \"".addslashes($http_request)."\", ".$rq_err.", ".$sent_content_length.", \"".addslashes($http_referer)."\", \"".addslashes($http_user_agent)."\")");
			
			mysql_close($cid);

		}
	
	}

}

?>
