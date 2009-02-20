<?php

/*

Nanoweb standard logging module
===============================

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

class mod_stdlog {

	var $modtype="log";
	var $modname="starndard logging";
	
	function log_strfilter($s) {

		return (str_replace("\"", "\\\"", $s));
	
	}
	
	function log_hit($vhost, $remote_host, $remote_ip, $logged_user, $http_request, $rq_err, $sent_content_length, $http_referer, $http_user_agent) {

		global $conf;
		
		$logline=$remote_host." - ".(($logged_user && $logged_user!=" ")?$logged_user:"-")." [".date("d/M/Y:H:i:s O")."] \"".mod_stdlog::log_strfilter($http_request)."\" ".$rq_err." ".(int)$sent_content_length;

		switch (strtolower($conf[$vhost]["logtype"][0])) {

			case "common":
			case "clf":
			$logline.="\n";
			break;

			case "common-with-vhost":
			case "clf-vhost":
			$logline=$vhost." ".$logline."\n";
			break;
			
			case "combined":
			default:
			$logline.=" \"".($http_referer?mod_stdlog::log_strfilter($http_referer):"-")."\" \"".($http_user_agent?mod_stdlog::log_strfilter($http_user_agent):"-")."\"\n";
			break;

		}

		$srv_logline="[".$vhost."] ".$logline;
		
		if ($conf["global"]["loghitstoconsole"][0] && !$GLOBALS["quiet"]) echo $srv_logline;
		
		log_srv($srv_logline, NW_EL_HIT);

		$fdir=$conf[$vhost]["logdir"][0];
		
		if ($fn_ar=$conf[$vhost]["log"]) foreach ($fn_ar as $fname) {
		
			if (($GLOBALS["os"]=="unix")?($fname{0}=="/"):(($fname{0}=="\\") || ($fname{1}==":"))) {
				
				$lfn=$fname;

			} else {

				$lfn=$fdir.DIRECTORY_SEPARATOR.$fname;

			}
			
			if ($lf=@fopen($lfn, NW_BSAFE_APP_OPEN)) {

				fputs($lf, $logline);
				fclose($lf);
			
			} else {

				techo("WARN: unable to write to log file '".$lfn."'", NW_EL_WARNING);
			
			}
	
		}
	
	}

}

?>
