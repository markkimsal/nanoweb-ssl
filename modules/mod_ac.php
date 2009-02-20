<?php

/*

Nanoweb Access Control Module
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

Usage (works in a vhost section or access file)

simple mode :

ACDenyHost = *.aol.com
ACBlockMessage = Go to hell

or

ACPolicy = deny
ACAllowIP = 10.0.0.*
ACAllowIP = 192.168.*
ACAllowHost = *.localdomain

or more advanced

ACClassHost = LAME *.aol.com
ACClassHost = LAME *.riaa.org
ACClassHost = PHEAR *.gov
ACDenyClass = *
ACBlockMessageClass = LAME Go to hell
ACBlockErrorClass = PHEAR 404
ACBlockMessageClass = PHEAR There is nothing here, really ...
ACIPBlockClass = PHEAR perm
ACIPBlockClass = LAME 3600

to only reject your favorite browser

ACClassHeader = IE User-Agent Mozilla/4.0 (compatible; MSIE*
ACClassHeader = IE User-Agent Mozilla/3.0 (compatible; MSIE*
ACClassHeader = LYNX User-Agent Lynx/*
ACDenyClass = IE

ACPolicy       : default access control policy (if not specified = allow)
ACDenyIP       : deny access to specified ip (wildcards allowed)
ACAllowIP      : allow access to specified ip (wildcards allowed)
ACDenyHost     : deny access to specified host (wildcards allowed)
ACAllowHost    : allow access to specified host (wildcards allowed)
ACDenyHeader   : deny access based on request header (wildcards allowed)
ACAllowHeader  : allow access based on request header (wildcards allowed)
ACBlockError   : error code thrown to denied clients (default is 403)
ACIPBlock      : duration in sec (or "perm") of the IP address ban
ACBlockMessage : additionnal error message to send to the client
ACClassIP/Host/...  : put the request in the specified class
ACBlockErrorClass   : block with error number for specified class
ACBlockMessageClass : block with error message for specified class
ACIPBlockClass      : duration in sec (or "perm") of the IP address ban

*/

class mod_ac {

	var $modtype="core_after_decode";
	var $modname="access control lists";

	var $ac_filters=array();
	
	function init() {

		if ($mtds=get_class_methods($this)) foreach ($mtds as $mname) if (strpos($mname, "acfilter_")===0) if ($fname=substr($mname, strpos($mname, "_")+1)) $this->ac_filters[$fname]=$mname;
	
	}

	// Adding a filter is easy, just define a new function here with the name
	// acfilter_yourfilter, and this will automagically make mod_ac handle new
	// ACAllow/Deny/Class[YourFilter] directives
	
	function acfilter_ip($filter) {

		global $remote_ip;
		
		if ($filter==$remote_ip) return(true);
		if ($filter==(substr($remote_ip, 0, strlen($filter)-1)."*")) return(true);
		
		return(false);
	
	}

	function acfilter_host($filter) {

		global $remote_host;
		
		$filter=strtolower($filter);
		$host=strtolower($remote_host);

		if ($filter==$host) return(true);
		if ($filter==("*".substr($host, (-(strlen($filter)-1))))) return(true);
		
		return(false);
	
	}
	
	function acfilter_header($ftok) {

		global $htreq_headers;
		
		$hdname=strtoupper(strtok($ftok, " "));
		$filter=strtolower(strtok(""));
		$th=strtolower($htreq_headers[$hdname]);

		if ($filter==$th) return(true);
		if ($filter==(substr($th, 0, strlen($filter)-1)."*")) return(true);

		return(false);
	
	}

	function classfilter($classname, $currentclass) {

		if (!$currentclass) return(false);
		
		$filter=strtolower($classname);
		$cc=strtolower($currentclass);

		if ($filter==$cc) return(true);
		if ($filter==(substr($cc, 0, strlen($filter)-1)."*")) return(true);

	}
	
	function fmatch($mtype) {

		$a=false;
		
		foreach ($this->ac_filters as $key=>$mtd) {
			
			if ($acf_arr=access_query("ac".$mtype.$key)) foreach ($acf_arr as $acf) if ($this->$mtd($acf)) {
			
				$a=true;
				break;

			}

			if ($acc_arr=access_query("acclass".$key)) foreach ($acc_arr as $acc) {
				
				$tcl=strtok($acc, " ");
				$tcf=strtok("");
				
				if ($this->$mtd($tcf)) {

					$ac_class=$tcl;
					break;

				}

			}

		}

		return(array($a, $ac_class));
	
	}
	
	function main() {

		global $add_errmsg, $pri_err, $remote_ip, $remote_host, $lf;

		switch (access_query("acpolicy", 0)) {

			case "deny":
			
			$ad=$this->fmatch("allow");
			$ad[0]=!$ad[0];
			
			if ($dca=access_query("acallowclass")) foreach ($dca as $dc) if ($this->classfilter($dc, $ad[1])) {
				
				$ad[0]=false;
				break;

			}

			break;

			case "allow":
			default:

			$ad=$this->fmatch("deny");

			if ($dca=access_query("acdenyclass")) foreach ($dca as $dc) if ($this->classfilter($dc, $ad[1])) {
				
				$ad[0]=true;
				break;

			}

			break;

		}

		if ($ad[0]) {

			if ($ac_class=$ad[1]) {

				if ($bea=access_query("acblockerrorclass")) foreach ($bea as $be) if (strtok($be, " ")==$ac_class) {

					$cl_err=strtok("");
					break;
				
				}
			
				if ($bma=access_query("acblockmessageclass")) foreach ($bma as $bm) if (strtok($bm, " ")==$ac_class) {

					$cl_msg=strtok("");
					break;
				
				}

				if ($iba=access_query("acipblockclass")) foreach ($iba as $ib) if (strtok($ib, " ")==$ac_class) {

					$cl_ibt=strtok("");
					break;
				
				}
			
			}

			$pri_err=$cl_err
			or $pri_err=(int)access_query("acblockerror", 0)
			or $pri_err=403;

			$amsg=$cl_msg
			or $amsg=access_query("acblockmessage", 0);

			$ibt=$cl_ibt
			or $ibt=access_query("acipblock", 0);

			if ($ibt) {

				if ($ac_class) $ibs=".".$ac_class;
				else $ibs="";

				if (strtolower($ibt)=="perm") {
				
					nw_block_ip_address($GLOBALS["remote_ip"], "PERM", "mod_ac".$ibs);

				} else {

					nw_block_ip_address($GLOBALS["remote_ip"], "TEMP", "mod_ac".$ibs, time()+$ibt);

				}
				
			}
			
			if ($amsg) $add_errmsg.=$amsg."<br><br>";
			
		}

	}

}

?>
