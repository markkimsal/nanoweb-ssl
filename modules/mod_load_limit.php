<?php

/*

Nanoweb Server Load Based Access Limiter Module
===============================================

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


class mod_load_limit {

    function mod_load_limit(){

        $this->modtype="core_after_decode";
        $this->modname="access limit based on server load average";

    }

    function main(){

        global $conf, $pri_err, $pri_redir, $add_errmsg, $pri_redir_code;

		$maxload=(float)access_query("loadlimit", 0);
		$action=access_query("loadlimitaction", 0) or $action="error";
		
		$pl=@file("/proc/loadavg");
		$lg=explode(" ", $pl[0]);

		$loadavg=(float)$lg[0];
		
		if ($loadavg>$maxload) {

			switch ($action) {
			
				case "redir":
				$pri_redir=$this->nsv_str_replace(access_query("loadlimitredirect", 0));
				$pri_redir_code=307;
				break;
				
				case "error":
				$err=access_query("loadlimiterror", 0) or $err=503;
				$pri_err=$err;
				$msg=access_query("loadlimiterrormessage", 0) or $msg="Server load is too high (<b>%CUR_LOAD/%MAX_LOAD</b>), try again in a few moments.";
				$msg=str_replace("%CUR_LOAD", sprintf("%.1f", $loadavg), $msg);
				$msg=str_replace("%MAX_LOAD", sprintf("%.1f", $maxload), $msg);
				$add_errmsg=$msg."<br><br>";
				break;

			}

		}

    }

	function nsv_str_replace($s) {

		if (strpos($s, '%')!==false) if ($nsv=nw_server_vars(strpos($s, '%HTTP')!==false)) foreach ($nsv as $key=>$val) $s=str_replace('%'.$key, $val, $s);
		return($s);

	}

}

?>
