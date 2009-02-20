#!/usr/local/bin/php -q
<?php

/*

Nanoweb, the aEGiS PHP web server
=================================

Copyright (C) 2002-2005 Vincent Negrier aka. sIX <six@aegis-corp.org>

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

error_reporting(E_PARSE | E_ERROR);
if (strpos($opts='^'.implode('_', $_SERVER["argv"]).'$', "--debug") + strpos($opts, "--verbose")) { error_reporting(E_ALL); } 

define("VERSION", "2.2.9");

// Hard configuration and defaults

define("DEFAULT_CONFIG_FILE", (((strpos(strtoupper(PHP_OS), "WIN")===0) || (strpos(strtoupper(PHP_OS), "CYGWIN")===0))?"C:\\nanoweb\\":"/etc/nanoweb/")."nanoweb.conf");
define("DEFAULT_LISTEN_ADDR", "0.0.0.0");
define("DEFAULT_LISTEN_PORT", 80);
define("DEFAULT_LISTEN_QUEUE", 20);
define("DEFAULT_MIMETYPES", "/etc/mime.types");
define("DEFAULT_CONTENT_TYPE", "text/plain");
define("DEFAULT_REQUEST_TIMEOUT", 15);
define("DEFAULT_ACCESS_FILE", ".nwaccess");
define("DEFAULT_DOCROOT", "./");
define("DEFAULT_LOGFILE", "./access.log");
define("DEFAULT_LOGTYPE", "combined");
define("DEFAULT_FBWELCOMEFILE", ".welcome");
define("DEFAULT_STATIC_BUFFER_SIZE", 1048576);
define("DEFAULT_CONTENT_HANDLER", "static");
define("DEFAULT_MAX_SERVERS", 25);
define("DEFAULT_ACCESS_POLICY", "override");
define("DEFAULT_SERVER_THEME", "default");
define("DEFAULT_SERVER_LANG", "en-us");

define("SCK_WRITE_PACKET_SIZE", 8192);
define("SCK_READ_PACKET_SIZE", 4096);
define("SCK_READ_SELECT_TIMEOUT", 2);
define("SCK_MAX_STALL_TIME", 60);

define("SPM_CACHES_LIFETIME", 15);

define("HTTP_VERSION", "HTTP/1.1");

define("SERVER_STRING", "aEGiS_nanoweb");
define("SERVER_STRING_V", SERVER_STRING."/".VERSION);

define("INT_MSGSIZE", 4096);

define("NM_HIT", "  HIT");
define("NM_RESTART_LOGGERS", "LGRST");
define("NM_SERVER_STATE", "SRVST");
define("NM_RELOAD_THEME", "RLTHM");
define("NM_BLOCK_IP", "BANIP");
define("NM_UNBLOCK_IP", "DBNIP");

define("NW_BAD_OUTSIDE_DOCROOT", 1);
define("NW_BAD_DOT_FILE", 2);
define("NW_BAD_WIN_DEVICE", 3);

define("NW_SB_STATUS", 0);
define("NW_SB_PEERHOST", 1);
define("NW_SB_FORKTIME", 2);

define("NW_EL_DEBUG", 1);
define("NW_EL_HIT", 2);
define("NW_EL_NOTICE", 4);
define("NW_EL_BLOCKING", 8);
define("NW_EL_WARNING", 16);
define("NW_EL_ERROR", 32);
define("NW_EL_ALL", 255);
define("NW_EL_DEFAULT", NW_EL_NOTICE | NW_EL_BLOCKING | NW_EL_WARNING | NW_EL_ERROR);

define("NW_TMPL_SIGNATURE", "server_signature");
define("NW_TMPL_ERROR_PAGE", "error_page");
define("NW_TMPL_ERROR_RESOURCE", "error_resource");
define("NW_TMPL_ERROR_ADMIN", "error_admin");

define("REQUIRED_PHP_VERSION", "4.2.0");

// Internally used global vars

$HTTP_HEADERS=array(100 => "100 Continue",
			200 => "200 OK",
			201 => "201 Created",
			204 => "204 No Content",
			206 => "206 Partial Content",
			300 => "300 Multiple Choices",
			301 => "301 Moved Permanently",
			302 => "302 Found",
			303 => "303 See Other",
			304 => "304 Not Modified",
			307 => "307 Temporary Redirect",
			400 => "400 Bad Request",
			401 => "401 Unauthorized",
			403 => "403 Forbidden",
			404 => "404 Not Found",
			405 => "405 Method Not Allowed",
			406 => "406 Not Acceptable",
			408 => "408 Request Timeout",
			410 => "410 Gone",
			413 => "413 Request Entity Too Large",
			414 => "414 Request URI Too Long",
			415 => "415 Unsupported Media Type",
			416 => "416 Requested Range Not Satisfiable",
			417 => "417 Expectation Failed",
			500 => "500 Internal Server Error",
			501 => "501 Method Not Implemented",
			503 => "503 Service Unavailable",
			506 => "506 Variant Also Negotiates");

$TEST_FUNCS=array(	"pcntl_fork"	=> false, 
			"socket_create"	=> true, 
			"posix_setuid"	=> false, 
			"gzencode"	=> false);

$conf_defaults=array(	"listeninterface"	=> DEFAULT_LISTEN_ADDR,
			"listenport"		=> DEFAULT_LISTEN_PORT,
			"listenqueue"		=> DEFAULT_LISTEN_QUEUE,
			"mimetypes"		=> DEFAULT_MIMETYPES,
			"requesttimeout"	=> DEFAULT_REQUEST_TIMEOUT,
			"accessfile"	=> DEFAULT_ACCESS_FILE,
			"documentroot"		=> DEFAULT_DOCROOT,
			"log"			=> DEFAULT_LOGFILE,
			"logtype"		=> DEFAULT_LOGTYPE,
			"fbwelcomefile" 	=> DEFAULT_FBWELCOMEFILE,
			"defaultcontenttype" => DEFAULT_CONTENT_TYPE,
			"staticbuffersize" => DEFAULT_STATIC_BUFFER_SIZE,
			"defaulthandler" => DEFAULT_CONTENT_HANDLER,
			"maxservers"	=> DEFAULT_MAX_SERVERS,
			"accesspolicy"	=> DEFAULT_ACCESS_POLICY,
			"servertheme" => DEFAULT_SERVER_THEME);

$conf_vhosts_propagate=array(	"documentroot",
			"directoryindex",
			"serveradmin",
			"user",
			"group",
			"logdir",
			"log",
			"logtype",
			"filebrowser",
			"fbshowdotfiles",
			"fbwelcomefile",
			"userdir",
			"ignoredotfiles",
			"allowsymlinkto",
			"maxrequestbodylength",
			"maxrequesturilength");

// Needed as long as PHP filetype() is broken on win32

$win_devices=array("nul", "con", "aux", "prn", "clock$", "com1", "com2", "com3", "com4", "com5", "com6", "com7", "com8", "lpt1", "lpt2", "lpt3", "lpt4", "lpt5", "lpt6", "lpt7", "lpt8");

// Command line help page

$cmdline_help=<<<EOT
Usage: nanoweb.php [/path/to/nanoweb.conf] [options]

nanoweb supports the following command line options :

--help                                -h : this help screen
--version                             -v : show version info
--config=/path/to/nanoweb.conf        -c : configuration file
--set-option="optionname=optionvalue" -o : set configuration option
--add-option="optionname=optionvalue" -a : add configuration option
--start-daemon                        -d : start nanoweb and run in background
--config-test                         -t : test configuration and exit
--quiet                               -q : don't send text to console


EOT;

// Static response class

class static_response {

	var $content_length;

	function static_response($str) {

		$this->str=$str;
		$this->content_length=strlen($str);
	
	}

	function parser_open($args, $filename, &$rq_err, &$cgi_headers) {

	}

	function parser_get_output() {

		$s=$this->str;
		$this->str="";
		return($s);

	}

	function parser_eof() {

		return($this->str === "");
	
	}

	function parser_close() {

	}

}

$null_response =& new static_response("");
$lf=$null_response;

// Functions

function _parseconfig_close_part(&$conf, $cpart, $nodefaults=false) {

	if (!$conf[$cpart]["_nw_pcp"]) {
	
		if (!$nodefaults) {
		
			if ($cpart=="global") {
				
				// Use default values for the global config scope
				
				foreach ($GLOBALS["conf_defaults"] as $key=>$dval) if (!isset($conf[$cpart][$key])) $conf[$cpart][$key][0]=$dval;

			} else {

				// Propagate appropriate directives to vhosts

				foreach ($GLOBALS["conf_vhosts_propagate"] as $key=>$pkey) if (!isset($conf[$cpart][$pkey])) $conf[$cpart][$pkey]=$conf["global"][$pkey];
				if (!$conf[$cpart]["servername"]) $conf[$cpart]["servername"][]=$cpart;

			}
		
		}
		
		// Transform some directives (Dir = Idx Value)
		
		foreach (array("parseext", "errordocument", "errorheader") as $dir) {
		
			if ($conf[$cpart][$dir]) {

				foreach ($conf[$cpart][$dir] as $ps) {

					$ext=strtolower(strtok($ps, " "));
					$dext=trim(strtok(""));
					
					$conf[$cpart]["_".$dir]["_".$ext]=$dext;

				}
			
			}

		}
	
	$conf[$cpart]["_nw_pcp"]=true;
	
	}

}

function _parseconfig_parse_line($str) {

	$cnfl=array();
	ereg("([^ =\n\t]+)[ \t]*=?[ \t]*([^\n]+)", $str, $cnfl);
	return(array(strtolower(trim($cnfl[1])), trim($cnfl[2])));

}

function parseconfig($conf_arr, $nodefaults=false) {

	$cpart="global";
	$_pcp=array("global");
	$included_confs[$GLOBALS["conffile"]]=true;
	
	if ($clen=count($conf_arr)) {
		
		// Pass 1 (build the $conf_arr array)

		$key=-1;
		
		while ($key++<=$clen) {

			$str=$conf_arr[$key];
			
			list($cnfk, $cnfv)=_parseconfig_parse_line($str);

			if ($cnfk=="configdir") {

				$confdir=$cnfv;
			
			} else if ($cnfk=="include") {

				$ifn=($confdir?($confdir.DIRECTORY_SEPARATOR):"").$cnfv;
				if (!@is_readable($ifn)) $ifn=$cnfv;
				
				if ($included_confs[$ifn]) {

					$conf_err="configuration includes loop detected line ".$key." : '".trim($str)."'";
					break;
			
				}
			
				if (!@is_readable($ifn)) {

					$conf_err="unable to include configuration file line ".$key." : '".$ifn."'";
					break;
				
				}

				$subconf_arr=file($ifn);

				$conf_arr=array_merge(array_slice($conf_arr, 0, $key), $subconf_arr, array_slice($conf_arr, $key+1));
				$key=-1;
				$clen=count($conf_arr);
				$included_confs[$ifn]=true;
			
			}

		}

		// Pass 2 (build the $conf array)
		
		foreach ($conf_arr as $key=>$str) {

			switch ($str[0]) {

				case "#":
				case ";":
				case "\n":
				case "":
				break;
				
				case "[":

				if ($cpart!="global") $_pcp[]=$cpart;
				
				$cpart=substr(trim($str), 1, -1);
				if ($cpart{0}=="/") $cpart="global";

				if ($cpart!="global") unset($conf[$cpart]);
				
				break;

				default:

				list($cnfk, $cnfv)=_parseconfig_parse_line($str);

				switch ($cnfk) {

					case "documentroot": 
					
					$rp=nw_realpath($cnfv);

					if (!($rp && @is_dir($rp))) {
						
						$conf_err="directory not found at line ".$key." : '".trim($str)."'";
						unset($conf[$cpart]);
						$cpart="_err:".$cpart;

					}
					
					if (substr($rp, -1)!=DIRECTORY_SEPARATOR) $rp.=DIRECTORY_SEPARATOR;
					$conf[$cpart][$cnfk][]=$rp;

					break;

					case "serveralias": 
					if ($cnfv!=$cpart) $conf[$cnfv]=&$conf[$cpart];
					break;
					
					case "alias":
					$conf[$cpart][$cnfk][]=$cnfv;
					$aliases=explode(" ", $cnfv);
					$conf[$cpart]["_aliases"][$aliases[0]]=$aliases[1];
					break;
					
					case "serverlog":

					$lname=strtok($cnfv, " ");
					
					if ($lmode=strtok("")) {

						$lbmode=0;
						foreach($GLOBALS["srvlog_levels"] as $lvl=>$bin_lvl) if (strpos($lmode, $lvl)!==false) $lbmode|=$bin_lvl;
						foreach($GLOBALS["srvlog_levels"] as $lvl=>$bin_lvl) if (strpos($lmode, "-".$lvl)!==false) $lbmode&=~$bin_lvl;

					} else {
						
						$lbmode=NW_EL_DEFAULT;

					}
					
					$conf[$cpart]["_serverlog"][$lname]=$lbmode;
					
					break;

					case "loadtheme":
					$conf[$cpart][$cnfk][]=$cnfv;
					$conf[$cpart]["servertheme"][]=$cnfv;
					break;

					default: 
					$conf[$cpart][$cnfk][]=$cnfv;
					break;

				}

				break;

			}
			
		}

	}
	
	// Pass 3 (close all $conf sections)
	
	if ($cpart!="global") $_pcp[]=$cpart;
	
	foreach ($_pcp as $clpart) _parseconfig_close_part($conf, $clpart, $nodefaults);

	return($conf_err?$conf_err:$conf);

}

function cmdline_conf_upd($conf, $cmdline_conf_overrides, $cmdline_conf_adds) {

	foreach ($cmdline_conf_overrides as $cs) {

		$ca=explode("=", $cs);
		$conf["global"][strtolower($ca[0])]=array($ca[1]);

	}

	foreach ($cmdline_conf_adds as $cs) {

		$ca=explode("=", $cs);
		$conf["global"][strtolower($ca[0])][]=$ca[1];

	}

	return($conf);

}

function load_modules($conf) {

	global $mod_tokens;
	
	$mod_tokens=array();
	
	if ($lm_arr=$conf["global"]["loadmodule"]) foreach ($lm_arr as $key=>$modname) {

		$clsname=basename($modname, ".php");
		
		if (!is_file($modname)) {

			foreach (access_query("modulesdir") as $md) if (is_file($md.DIRECTORY_SEPARATOR.$modname)) {

				$moddir=$md;
				break;
			
			}
			
		} else $moddir="";
		
		if (!$ld_clss[$clsname]) {
			
			$nload=(!class_exists($clsname));
			
			// Try to load with given path
			
			@include_once($modname);
			$modloaded=class_exists($clsname);

			if (!$modloaded) {

				// And try with modulesdir if not found

				@include_once($moddir.$modname);
				$modloaded=class_exists($clsname);
			
			}

			if ($modloaded) {
			
				$ld_clss[$clsname]=true;
				$tmp=&new $clsname;
				$modules[$tmp->modtype][]=&$tmp;
				$tmp_modlist[]=array($clsname, $tmp->modname);

				if (is_array($tmp->urls)) foreach ($tmp->urls as $url) $modules["url:".$url]=&$tmp;
				if (is_array($tmp->methods)) foreach ($tmp->methods as $method) $modules["method:".$method]=&$tmp;
				if (is_string($mt=$tmp->sig_token)) $mod_tokens[$tmp->modname]=$mt;

				if ($nload) techo("loaded module : ".$tmp->modname);

			} else {

				techo("WARN: unable to load module '".$modname."'", NW_EL_WARNING);
			
			}
	
		}
	
	}

	$GLOBALS["stats_modlist"]=$tmp_modlist;

	return ($modules);

}

function load_theme($themefname, $load_notice=false, $reload=false) {

	$tfn=($GLOBALS["conf"]["global"]["configdir"][0]?($GLOBALS["conf"]["global"]["configdir"][0].DIRECTORY_SEPARATOR):"").$themefname;
	if (!@is_readable($tfn)) $tfn=$themefname;
	
	if ($thmarr=@file($tfn)) {

		$ts=0;
		
		foreach ($thmarr as $thml) {

			if (strtolower(rtrim($thml))=="[/".$thm_sc."]") {

				$theme[$thm_sc]=substr($theme[$thm_sc], 0, -1);
				--$ts;
				$thm_sc="";
				
			} else if (($thml{0}=="[") && (substr(rtrim($thml), -1)=="]")) {

				$thm_sc=strtolower(substr(rtrim($thml), 1, -1));

			} else if ($thm_sc) {

				$theme[$thm_sc].=$thml;
				$ts+=strlen($thml);
			
			}

		}

		if ($theme["theme_id"]) {

			if (!$theme["theme_name"]) $theme["theme_name"]=$theme["theme_id"];
			$theme["theme_id"]=trim($theme["theme_id"]);
			$theme["theme_name"]=trim($theme["theme_name"]);
			
			$theme["theme_language"]=trim($theme["theme_language"])
			or $theme["theme_language"]=DEFAULT_SERVER_LANG;

			if ($load_notice) techo(($reload?"re":"")."loaded theme : ".$theme["theme_name"]." (".$ts." bytes)");

		} else {

			techo("WARN: invalid theme file '".$tfn."'", NW_EL_WARNING);
		
		}

		clearstatcache();

		$theme["_fname"]=$tfn;
		$theme["_mtime"]=filemtime($tfn);
		$theme["_pmode"]=$GLOBALS["pmode"];
	
	} else {

		techo("WARN: unable to load theme file '".$tfn."'", NW_EL_WARNING);
	
	}

	return($theme);

}

function load_themes($conf) {

	if (is_array($conf["global"]["loadtheme"])) foreach ($conf["global"]["loadtheme"] as $themefname) {

		$theme=load_theme($themefname, true);
		$themes[$theme["theme_id"]]=$theme;
		$themes[$theme["_fname"]]=$theme;
	
	} else {

		techo("WARN: 'LoadTheme' directive not found in config file", NW_EL_WARNING);
	
	}

	return($themes);

}

function modules_init($method="init") {

	global $modules;
	
	foreach ($modules as $modclass)	if (is_array($modclass)) {
		
		for ($a=0;$a<count($modclass);$a++) {
		
			if ((method_exists($modclass[$a], $method)) && (!$modinit[$mc=get_class($modclass[$a])])) {
				
				$modclass[$a]->$method();
				$modinit[$mc]=true;

			}

		} 
	
	} else {

		if ((method_exists($modclass, $method)) && (!$modinit[$mc=get_class($modclass)])) {
			
			$modclass->$method();
			$modinit[$mc]=true;

		}

	}

}

function load_access_files($dir, &$access, $rec=0) {

	global $conf, $access_cache;

	if (is_array($z=$access_cache[$dir])) {

		// Access cache hit
		
		$access=$z;
		return;
	
	} else {
	
		// Access cache miss
		
		if (!$rec) $access=array();
		
		$ndir=substr($dir, 0, strrpos($dir, DIRECTORY_SEPARATOR));

		$cont = false;

		$r_mdir = nw_realpath($ndir.DIRECTORY_SEPARATOR);
		$cont_dirs = array(nw_realpath($GLOBALS["docroot"]));
		foreach ($conf[$GLOBALS["vhost"]]["allowsymlinkto"] as $als) $cont_dirs[] = nw_realpath($als);
	
		foreach ($cont_dirs as $cdir) if (strpos($r_mdir, $cdir)===0) {
			
			load_access_files($ndir, $access, $rec+1);
			break;

		}
		
		$afn=$dir.DIRECTORY_SEPARATOR.$conf["global"]["accessfile"][0];

		if (is_readable($afn) && ($accesstmp=@file($afn)) && ($tmp_access=parseconfig($accesstmp, true))) foreach ($tmp_access as $key=>$val_arr) {
				
			foreach ($val_arr as $ckey=>$cval_arr) {
				
				$ap=$GLOBALS["access_policy"][$ckey] or
				$ap=$conf["global"]["accesspolicy"][0];

				switch ($ap) {

					case "override":
					$access[$key][$ckey]=$cval_arr;
					break;

					case "merge":
					$access[$key][$ckey]=array_merge($access[$key][$ckey], $cval_arr);
					break;

				}

			}

			if (!$rec) break;
		
		}

		$access_cache[$dir]=$access;
	
	}

}

function access_query($key, $idx=false) {

	global $access, $conf;

	$ap=$GLOBALS["access_policy"][$key] or
	$ap=$conf["global"]["accesspolicy"][0];

	switch ($ap) {

		case "override":
		$tmp=$access["global"][$key] or
		$tmp=$conf[$GLOBALS["vhost"]][$key] or
		$tmp=$conf["global"][$key];
		break;

		case "merge":
		$tmp=array_merge($conf["global"][$key] ? $conf["global"][$key] : array(), $conf[$GLOBALS["vhost"]][$key] ? $conf[$GLOBALS["vhost"]][$key] : array(), $access["global"][$key] ? $access["global"][$key] : array());
		break;

	}

	if ($idx===false) {

		return($tmp);

	} else {

		return($tmp[$idx]);
	
	}

}

function core_modules_hook($hname) {

	if ($mh_arr=&$GLOBALS["modules"]["core_".$hname]) foreach (array_keys($mh_arr) as $a) $mh_arr[$a]->main();

}

function log_ids() {

	global $conf;
	
	if ($setgid=@posix_getgrnam($conf["global"]["loggergroup"][0])) $g=$setgid["gid"];
	else if ($setgid=@posix_getgrnam($conf["global"]["group"][0])) $g=$setgid["gid"];

	if ($setuid=@posix_getpwnam($conf["global"]["loggeruser"][0])) $u=$setuid["uid"];
	else if ($setuid=@posix_getpwnam($conf["global"]["user"][0])) $u=$setuid["uid"];

	return(array("uid" => $u, "gid" => $g));

}

function log_srv($str, $loglevel=NW_EL_NOTICE) {

	if ($srvlog_arr=$GLOBALS["conf"]["global"]["_serverlog"]) foreach ($srvlog_arr as $s=>$bmode) if ($loglevel & $bmode) {
		
		if (($GLOBALS["pmode"]=="master") && (!file_exists($s))) $chown=true;
		
		if ($sl=@fopen($s, NW_BSAFE_APP_OPEN)) {

			fputs($sl, $str);
			fclose($sl);
			
		}

		if ($chown && $GLOBALS["posix_av"] && ($lids=log_ids())) {

			chgrp($s, $lids["gid"]);
			chown($s, $lids["uid"]);

		}

	}

}

function nw_gethostbyaddr($ip) {

	static $ns_lastreq;
	
	if ($ip != $ns_lastreq[0]) {
		
		$hostname = @gethostbyaddr($ip);
		$fwr_ip = @gethostbyname($hostname);

		if ($ip != $fwr_ip) {
			
			// Inconsistent DNS data
			
			$hostname = $ip;

		}
		
		$ns_lastreq = array($ip, $hostname);

	}

	return($ns_lastreq[1]);

}

function nw_realpath($dir) {

	global $rp_cache;
	
	if ($rp=$rp_cache[$dir]) {

		return($rp);
	
	} else {

		return($rp_cache[$dir]=realpath($dir));
	
	}

}

function nw_server_string() {

	switch (strtolower(access_query("serversignature", 0))) {

		case "fake": return(access_query("serverfakesignature", 0));
		case "off": return("");
		case "prod": return(SERVER_STRING);
		case "min": return(SERVER_STRING_V);
		case "os": return(SERVER_STRING_V." (".PHP_OS.")");
		case "php": return(SERVER_STRING_V." (".PHP_OS."; PHP/".phpversion().")");

		case "full": 
		default:
		return(SERVER_STRING_V." (".PHP_OS."; PHP/".phpversion().($GLOBALS["mod_tokens"]?"; ":"").implode("; ", $GLOBALS["mod_tokens"]).")");

	}
	
}

function _genpage_signature() {

	return(nw_apply_template(NW_TMPL_SIGNATURE, array("server_string" => nw_server_string(), "server_name" => $GLOBALS["conf"][$GLOBALS["vhost"]]["servername"][0]), true));
	
}

function nw_apply_template($template, $args, $no_add=false) {

	global $themes;
	
	$sts = access_query("servertheme");
	$lts = access_query("loadtheme");
	
	$thmid=array_pop($sts);
	$fname=$themes[$thmid]["_fname"];
	
	if ($thmid==$fname) $thmid=$themes[$thmid]["theme_id"];
	if ((!is_array($themes[$thmid])) && ($ltid=array_pop($lts))) $fname=$ltid;
	
	clearstatcache();
	
	if (($themes[$thmid]["_mtime"]!=filemtime($fname)) && ($tmp_thm=load_theme($fname))) {

		if ($themes[$thmid]["_pmode"]=="master") int_sendtomaster(NM_RELOAD_THEME, $thmid);

		$themes[$thmid]=$tmp_thm;
		$themes[$fname]=$tmp_thm;
		$themes[$tmp_thm["_fname"]]=$tmp_thm;

	}

	$tlang=strtolower($themes[$thmid]["theme_language"]);

	if (($al=$GLOBALS["htreq_headers"]["ACCEPT-LANGUAGE"]) && ($als=nw_decode_mq_hdr($al))) {

		foreach (array_keys($als) as $lang) {
			
			$lang=strtolower($lang);
			
			if (isset($themes[$thmid][$template.":".$lang])) {

				$tname=$template.":".$lang;
				break;
			
			} else if ($tlang==$lang) {

				break;
			
			}

		}

	}

	if (!$tname) $tname=$template;
	
	$tmpl=$themes[$thmid][$tname] or
	$tmpl=$themes[DEFAULT_SERVER_THEME][$tname];

	foreach ($args as $k=>$v) $tr_arr["@$".strtolower($k)."@"]=$v;
	if (!$no_add) $tr_arr['@$server_signature@']=_genpage_signature();

	$trt=strtr($tmpl, $tr_arr);

	while ((($p=strpos($trt, "@!"))!==false) && (($p2=strpos(substr($trt, $p+2), "@"))!==false)) {

		$ret=substr($trt, 0, $p);
		$ret.=access_query(strtolower(substr($trt, $p+2, $p2)), 0);
		$ret.=substr($trt, $p+$p2+3);

		$trt=$ret;

	}

	return($trt);

}

function nw_server_vars($include_cgi_vars=false) {

	global $conf;
	
	$filename=$GLOBALS["docroot"].$GLOBALS["http_uri"];
	
	$nsv["SERVER_SOFTWARE"]=nw_server_string();
	$nsv["SERVER_NAME"]=$conf[$GLOBALS["vhost"]]["servername"][0];
	$nsv["SERVER_PROTOCOL"]=HTTP_VERSION;
	$nsv["SERVER_PORT"]=$GLOBALS["lport"];
	$nsv["SERVER_ADDR"]=$conf["global"]["listeninterface"][0];
	$nsv["SERVER_API"]=VERSION;
	$nsv["SERVER_ADMIN"]=$conf[$GLOBALS["vhost"]]["serveradmin"][0];
	$nsv["REQUEST_METHOD"]=$GLOBALS["http_action"];
	$nsv["PATH_TRANSLATED"]=$nsv["SCRIPT_FILENAME"]=nw_realpath($filename);
	$nsv["SCRIPT_NAME"]="/".$GLOBALS["docroot_prefix"].$GLOBALS["http_uri"];
	$nsv["QUERY_STRING"]=$GLOBALS["query_string"];
	$nsv["REMOTE_HOST"]=$GLOBALS["remote_host"];
	$nsv["REMOTE_ADDR"]=$GLOBALS["remote_ip"];
	$nsv["REMOTE_PORT"]=$GLOBALS["remote_port"];
	$nsv["AUTH_TYPE"]=$GLOBALS["auth_type"];
	$nsv["DOCUMENT_ROOT"]=$GLOBALS["docroot"];
	$nsv["REQUEST_URI"]="/".$GLOBALS["real_uri"].($nsv["QUERY_STRING"]?("?".$nsv["QUERY_STRING"]):"");
	$nsv["PATH_INFO"]=$GLOBALS["path_info"];

	if (($GLOBALS["logged_user"]) && ($GLOBALS["logged_user"] != " ")) {

		$nsv["REMOTE_USER"] = $GLOBALS["logged_user"];

	}

	if ($asv=access_query("addservervar")) foreach ($asv as $str) {

		$k=strtok($str, " ");
		$v=strtok("");
		if ($k) $nsv[$k]=$v;
	
	}

	if ($GLOBALS["add_nsv"]) foreach ($GLOBALS["add_nsv"] as $key=>$val) $nsv[$key]=$val;

	if ($include_cgi_vars && ($rq_hdrs=$GLOBALS["htreq_headers"])) foreach($rq_hdrs as $key=>$val) $nsv["HTTP_".str_replace("-", "_", $key)]=$val;

	return $nsv;

}

function nw_url_addslash($s) {

	$ret = strtok($s, "?")."/";
	if (($q = strtok("")) !== false) $ret .= "?".$q;

	return $ret;

}

function techo($s, $level=NW_EL_NOTICE, $flush=false) {

	global $conf;

	static $srv_buf;
	
	$tl=date("Ymd:His")." $s\n";

	if (!$conf["_complete"] && !$flush) {

		$srv_buf[]=array($tl, $level);
	
	} else {
	
		if (($conf["global"]["servermode"][0]!="inetd") && !$GLOBALS["quiet"]) {

			if ($srv_buf) foreach ($srv_buf as $sb_arr) echo $sb_arr[0];
			echo $tl;
			flush();

		}

		if ($srv_buf) {
			
			foreach ($srv_buf as $sb_arr) log_srv($sb_arr[0], $sb_arr[1]);
	
			$srv_buf=array();

		}

		log_srv($tl, $level);

	}

}

function errexit($s, $errno=-1) {

	global $pidfile, $start_daemon;
	
	$estr="FATAL: ".$s;
	techo($estr, NW_EL_ERROR, true);
	if ($pidfile) unlink($pidfile);

	if ($start_daemon && ($stderr=@fopen("php://stderr", "w"))) {

		fputs($stderr, $estr."\n");
		fclose($stderr);

	}

	exit($errno);

}

function url_to_absolute($url) {

	return("http://".$GLOBALS["conf"][$GLOBALS["vhost"]]["servername"][0].(($GLOBALS["lport"]!=80)?(":".$GLOBALS["lport"]):"").($url!="/"?"/":"").$url);

}

function loadfile($filename, $extension, &$rq_err, &$cgi_headers, $force_parser=false) {
	
	global $conf, $modules, $add_nsv;
	
	if (is_link($filename)) $filename=readlink($filename);
	chdir(dirname(nw_realpath($filename)));
	$filename=basename($filename);

	if (($parser=$force_parser) || ($parser=trim(access_query("_parseext", "_".strtolower($extension))))) {

		// Parsed content
		
		if (strpos($parser, " ")!==false) {
		
			$ps_type=strtok($parser, " ");
			$ps_arg=strtok("");
			if (strpos($ps_arg, '$')!==false) foreach (nw_server_vars() as $nkey=>$nval) $ps_arg=str_replace('$'.$nkey, $nval, $ps_arg);

			if (!$force_parser) {

				$add_nsv["REDIRECT_STATUS"]=$rq_err;
				$add_nsv["REDIRECT_URL"]="/".$GLOBALS["real_uri"];

			}
		
		} else $ps_type=$parser;

	} else {

		// Static content

		$ps_type=$conf["global"]["defaulthandler"][0];

	}

	if ($ps=$modules["parser_".$ps_type][0]) {

		if (is_object($rop=$ps->parser_open($ps_arg, $filename, $rq_err, $cgi_headers))) $ps=$rop;

	} else {

		$rq_err=500;
		$GLOBALS["add_errmsg"]="Unable to find an appropriate parser for this content type.<br><br>";
		$ps=$GLOBALS["null_response"];
	
	}

	return($ps);

}

function nw_host_to_vhost($host, $lport=80) {
					
	global $conf;
	
	// Try vhost=host:port
	
	if (is_array($conf[$phost=($host.":".$lport)])) return($phost);
	
	// Try vhost=host
	
	if (is_array($conf[$host])) return($host);

	// Try wildcards

	$hlen=strlen($host);

	for($vhlen=0;$vhlen<=$hlen;$vhlen++) {
		
		$whost="*".substr($host, $vhlen);
		if (is_array($conf[$phost=($whost.":".$lport)])) return($phost);
		if (is_array($conf[$whost])) return($whost);

	}

	// Or set to global

	return("global");

}

function nw_error_page($rq_err) {
	
	global $HTTP_HEADERS, $http_resource, $conf, $vhost, $add_errmsg;
	
	$err["error_code"]=$rq_err;
	$err["error_label"]=$HTTP_HEADERS[$rq_err];
	$err["error_add_message"]=$add_errmsg;

	$add_errmsg = "";
	
	$err["error_resource"]=($http_resource?(nw_apply_template(NW_TMPL_ERROR_RESOURCE, array("resource_name" => htmlentities($http_resource)), true)):"");
	if ($conf[$vhost]["serveradmin"][0]) $err["error_admin"]=nw_apply_template(NW_TMPL_ERROR_ADMIN, array("admin" => $conf[$vhost]["serveradmin"][0]), true);
	
	$err_page=nw_apply_template(NW_TMPL_ERROR_PAGE, $err) or
	$err_page="<html><head><title>".$HTTP_HEADERS[$rq_err]."</title></head><body><h1>".$HTTP_HEADERS[$rq_err]."</h1></body></html>";

	return($err_page);

}

function nw_use_chunked_encoding() {

	if (!isset($GLOBALS["lf"]->content_length) && $GLOBALS["keepalive"]) {

		if ($GLOBALS["http_version"]>="1.1") {

			return(true);
		
		} else {

			return("CLOSE");
		
		}
	
	} else return(false);

}

function nw_decode_mq_hdr($s) {

	if ($l=explode(",", $s)) foreach ($l as $e) {

		list($v, $q)=explode(";", $e);
		if ($q) list($d, $qn)=explode("=", $q);
		if (!$qn) $qn=1;
		if ($v) $r[$v]=$qn;
	
	}

	arsort($r);

	return($r);

}

function nw_allow_list($ext) {
						
	$tmp_marr=array();

	foreach ($GLOBALS["modules"] as $tmpmod) if (method_exists($tmpmod, "options")) if ($mod_methods=$tmpmod->options()) foreach ($mod_methods as $mod_method) if (!isset($tmp_marr[$mod_method])) $tmp_marr[$mod_method]=$mod_method;
		
	return ("GET, ".(access_query("_parseext", "_".strtolower($rq_file["extension"]))?"POST, ":"")."HEAD, OPTIONS".(count($tmp_marr)?", ":"").implode(", ", $tmp_marr));

}

function build_response_headers() {

	global $HTTP_HEADERS, $rq_err, $out_contenttype, $out_add_headers, $conf, $lf;
	
	if ($out_add_headers) {
		
		foreach ($out_add_headers as $key=>$val) switch (strtoupper($key)) {
			
			case "CONTENT-TYPE":
			$out_contenttype=$val;
			break;

			case "LOCATION":
			$rq_err=302;
			$add_headers.=$key.": ".$val."\r\n";
			break;
			
			case "COOKIES":
			foreach ($val as $cval) $add_headers.="Set-Cookie: ".$cval."\r\n";
			break;

			case "STATUS":
			
			$st=(int)strtok($val, " ");

			if ($stx=trim(strtok(""))) {

				$http_resp=$st." ".$stx;
			
			} else if ($stx=$HTTP_HEADERS[$st]) {

				$http_resp=$stx;
			
			} else {

				$http_resp=$st;
			
			}

			$rq_err=$st;
			
			break;

			default:		
			$add_headers.=$key.": ".$val."\r\n";
				
		}

	}
	
	$clf=($GLOBALS["http_action"]=="HEAD"?$GLOBALS["hlf"]:$lf);
	
	$out_headers=HTTP_VERSION." ".($http_resp?trim($http_resp):$HTTP_HEADERS[$rq_err])."\r\n";
	$out_headers.="Date: ".gmdate("D, d M Y H:i:s T")."\r\n";
	if ($ss=nw_server_string()) $out_headers.="Server: ".$ss."\r\n";
	$out_headers.="Content-Type: ".$out_contenttype."\r\n";

	if ($ahlist=access_query("addheader")) foreach ($ahlist as $val) $out_headers.=trim($val)."\r\n";
	if (($rq_err>=400) && ($eh=access_query("_errorheader", "_".$rq_err))) $out_headers.=$eh."\r\n";
	
	$out_headers.=$add_headers;

	if ($GLOBALS["keepalive"]) {
	
		$out_headers.="Connection: Keep-Alive\r\n";
		$out_headers.="Keep-Alive: timeout=".(int)$conf["global"]["requesttimeout"][0].", max=".(int)($conf["global"]["keepalive"][0])."\r\n";
		
	} else {
		
		$out_headers.="Connection: close\r\n";

	}
	
	if ($GLOBALS["chunked"]) { 
		
		$out_headers.="Transfer-Encoding: chunked\r\n";

	} else {

		if (is_int($clf->content_length)) $out_headers.="Content-Length: ".$clf->content_length."\r\n";

	}

	return($out_headers);

}

function nanoweb_init($conffile) {

	global $conf, $themes, $cmdline_conf_overrides, $cmdline_conf_adds, $modules, $posix_av, $pcntl_av, $gz_av, $mime, $access_policy, $sysusr, $sysgrp, $icnt, $banned_ips, $srvlog_levels;
	
	$dc=get_defined_constants();
	foreach ($dc as $cname=>$cval) if (substr($cname, 0, 6)=="NW_EL_") $srvlog_levels[strtolower(substr($cname, 6))]=$cval;

	$iconf=parseconfig(file($conffile));

	if (is_string($iconf)) {

		if ($icnt) {
			
			techo($iconf, NW_EL_WARNING);
			return(false);

		} else {

			errexit($iconf);
		
		}
	
	} else if (is_array($iconf)) {

		$conf=$iconf;
	
	}

	$conf=cmdline_conf_upd($conf, $cmdline_conf_overrides, $cmdline_conf_adds);
	$modules=load_modules($conf);
	modules_init();
	$themes=load_themes($conf);

	++$icnt;

	$ap_aliases=array(	"parseext"		=> "_parseext",
						"alias"			=> "_aliases",
						"errordocument"	=> "_errordocument",
						"errorheader"	=> "_errorheader"		);

	$access_policy=array();
	foreach ($conf["global"]["accessoverride"] as $ov_dir) if ($ov_dir) $access_policy[strtolower($ov_dir)]="override";
	foreach ($conf["global"]["accessmerge"] as $mg_dir) if ($mg_dir) $access_policy[strtolower($mg_dir)]="merge";
	foreach ($conf["global"]["accessblock"] as $bl_dir) if ($bl_dir) $access_policy[strtolower($bl_dir)]="block";

	foreach ($ap_aliases as $rk=>$ak) if ($access_policy[$rk]) $access_policy[$ak]=$access_policy[$rk];

	$posix_av=is_callable("posix_setuid");
	$pcntl_av=is_callable("pcntl_fork");
	$gz_av=is_callable("gzencode");

	if (count($themes)==0) techo("WARN: No theme loaded, server generated content is disabled", NW_EL_WARNING);
	
	if ($posix_av) foreach ($conf as $vconf) {

		if ($u=$vconf["user"][0]) $sysusr[$u]=@posix_getpwnam($u);
		if ($g=$vconf["group"][0]) $sysgrp[$g]=@posix_getgrnam($g);
	
	}


	if ((!$conf["global"]["singleprocessmode"][0]) && (!$posix_av || !$pcntl_av || ($conf["global"]["servermode"][0]=="inetd"))) {

		techo("WARN: forcing single process mode", NW_EL_WARNING);
		$conf["global"]["singleprocessmode"][0]=true;

	}

	if ($conf["global"]["servermode"][0]=="inetd") {
		
		unset($conf["global"]["logtoconsole"]);
		unset($conf["global"]["pidfile"]);

	}

	if ($conf["global"]["singleprocessmode"][0]) {
		
		$conf["global"]["loggerprocess"]=0;

		if ($conf["global"]["keepalive"][0]) techo("WARN: KeepAlive should be set to 0 in single process mode", NW_EL_WARNING);

	}

	if ($pcntl_av) {

		pcntl_signal(SIGTERM, "nanoweb_shutdown");
		pcntl_signal(SIGHUP, "nanoweb_reload");

	}

	$mime=array();
	
	if (!@is_readable($conf["global"]["mimetypes"][0])) {

		techo("WARN: unable to read mime types file (".$conf["global"]["mimetypes"][0]."), using internals", NW_EL_WARNING);

		$mime=array(	"html" => "text/html",
						"xml"  => "text/xml",
						"gif"  => "image/gif",
						"jpeg" => "image/jpeg",
						"png"  => "image/png",
						"tgz"  => "application/gtar");

	} else if ($mimetypes=@file($conf["global"]["mimetypes"][0])) {
		
		foreach ($mimetypes as $s) if (trim($s) && ($s{0}!="#")) if (ereg("([a-zA-Z0-9/.-]+)[ \t]+([a-zA-Z0-9 -]+)", $s, $res)) if ($exts=explode(" ", trim($res[2]))) foreach ($exts as $ext) if (trim($res[1]) && trim($ext)) $mime[$ext]=trim($res[1]);

		unset($mimetypes);

	}

	if ($at=$conf["global"]["addtype"]) foreach ($at as $adt) {

		$mt=strtok(trim($adt), " ");
		while ($s=strtok(" ")) $mime[ltrim($s, ".")]=$mt;

	}

	$conf["_complete"]=true;

	$banned_ips=array();

	if (is_array($conf["global"]["blockipaddr"])) foreach ($conf["global"]["blockipaddr"] as $ip) nw_block_ip_address($ip, "PERM", "config.BlockIPAddr");

	return(true);

}

function nanoweb_shutdown($sig_no=SIGTERM) {

	global $lsocks, $pidfile, $loggers_sck, $conf, $pmode;
	
	if ($pmode=="master") {
	
		modules_init("shutdown");
		
		if ($lsocks) foreach ($lsocks as $sock) socket_close($sock);

		if ($nb_loggers=$conf["global"]["loggerprocess"][0]) {
		
			techo("halting loggers");
			
			for ($a=0;$a<$nb_loggers;$a++) {

				$pkt="TERM";
				
				socket_write($loggers_sck, $pkt);
				usleep(100000);
			
			}

			sleep(1);
		
		}

		if ($pidfile) unlink($pidfile);
		techo("daemon stopped\n");

	}

	exit(0);

}

function nanoweb_reload($sig_no=SIGHUP) {

	global $mypid, $conffile, $conf, $loggers_sck, $logger_pids, $killed_loggers, $access_cache, $rp_cache;

	if (!$mypid) {
		
		techo("received SIGHUP, reloading configuration ...");
		
		clearstatcache();
		unset($access_cache);
		unset($rp_cache);

		if (nanoweb_init($conffile)) {

			if ($nb_loggers=$conf["global"]["loggerprocess"][0]) {
			
				techo("restarting loggers");
				
				foreach ($logger_pids as $lid) {

					$pkt="TERM";
					
					socket_write($loggers_sck, $pkt);
					usleep(100000);

					$killed_loggers[] = $lid;

				}

				sleep(1);
			
			}
		
			techo("configuration reloaded from ".$conffile);
		
		} else {

			techo("configuration was not reloaded", NW_EL_WARNING);

		}

	}

}

function read_request(&$sck_connected, &$dp, &$pn, $maxlen=0) {
			
	global $conf;

	static $rr_buffer;
	
	$wstart=time();
		
	if ($rr_buffer!=="") {
		
		$buf=$rr_buffer;
		$tnreq=true;

	}
	
	while (!$rq_finished && $sck_connected) {
	
		if (!$tnreq) {
		
			$fdset=$GLOBALS["pfdset"];

			if ($conf["global"]["servermode"][0]=="inetd") {

				// Inetd

				if (feof($GLOBALS["inetd_in"])) {
					
					$tmp=false;
					$sck_connected=false;

				} else $tmp=fgetc($GLOBALS["inetd_in"]);

			} else {
			
				if ($ns=socket_select($fdset, $write=NULL, $fdset, SCK_READ_SELECT_TIMEOUT)) $tmp=@socket_read($GLOBALS["msgsock"], SCK_READ_PACKET_SIZE); else $tmp=false;

			} 

		}

		if ($tmp || $tmp==="0" || $tnreq) {
			
			$tnreq=false;
			$wstart=time();
			$buf.=$tmp;
			$pn=0;

			if (!$maxlen) {
			
				if (!$rnloop) {
					
					$buf=ltrim($buf);
					$rnloop=true;

				}

				if (($dp=strpos($buf, "\r\n\r\n"))!==false) $pn=4;
				else if (($dp=strpos($buf, "\n\n"))!==false) $pn=2;

				if ($pn) $rq_finished=true;

			} else {

				if (strlen($buf)>=$maxlen) $rq_finished=true;

			}

		} else if (($ns) || ((!$ns) && ((time()-$wstart)>=$conf["global"]["requesttimeout"][0]))) $sck_connected=false;

	}
	
	if (!$maxlen) {

		$tbuf=substr($buf, 0, $dp+$pn);
		$rr_buffer=substr($buf, $dp+$pn);
		$buf=$tbuf;
	
	} else {

		$tbuf=substr($buf, 0, $maxlen);
		$rr_buffer=substr($buf, $maxlen);
		$buf=$tbuf;

	}
	
	return($buf);

}

function send_response($response, &$sck_connected) {

	global $msgsock;
	
	$resp_len=strlen($response);

	while($sent_len<$resp_len && $sck_connected) {

		if ($GLOBALS["conf"]["global"]["servermode"][0]=="inetd") {

			// Inetd
			
			echo $response;
			$sent_len=strlen($response);

		} else {

			$fdset=$GLOBALS["pfdset"];
			
			if (($sent_len+SCK_WRITE_PACKET_SIZE)>$resp_len) $size=$resp_len-$sent_len; else $size=SCK_WRITE_PACKET_SIZE;
			if (($ret=@socket_write($msgsock, substr($response, $sent_len, $size), $size))>0) $sent_len+=$ret;

			if ($ret===false) {
				
				if (socket_last_error($msgsock)==SOCKET_EWOULDBLOCK) {
					
					socket_clear_error($msgsock);
					if (!socket_select($read=NULL, $fdset, $except=NULL, SCK_MAX_STALL_TIME)) $sck_connected=false;

				} else {

					$sck_connected=false;
				
				}

			}

		}

	}

	return($sent_len);

}

function int_sendtomaster($msg_type, $args=false) {

	if ((!$GLOBALS["conf"]["global"]["singleprocessmode"][0]) && ($GLOBALS["pmode"]!="master")) {
	
		$msg=$msg_type;
		if ($args!==false) $msg.=serialize($args);
		
		$ret=socket_write($GLOBALS["master_sck"], $msg);

		if ($ret!=strlen($msg)) {

			techo("WARN: master process is not responding", NW_EL_WARNING);
		
		}

	}

}

function _server_report_state($s, $remote_host="") {

	$tmp=array($GLOBALS["mypid"], $s);
	if ($remote_host) $tmp[]=$remote_host;

	int_sendtomaster(NM_SERVER_STATE, $tmp);

}

function nw_block_ip_address($ip_addr, $type, $source, $expires=0) {

	global $conf, $pmode, $banned_ips;

	if ((($conf["global"]["singleprocessmode"][0]) || ($pmode=="master")) && (!$banned_ips[$ip_addr])) {
	
		$banned_ips[$ip_addr]=array("type" => $type, "source" => $source, "expires" => $expires);
		techo($source." : blocked IP address ".$ip_addr." (".strtolower($type).")", NW_EL_BLOCKING);

	} else {

		int_sendtomaster(NM_BLOCK_IP, array($ip_addr, $type, $source, $expires));
	
	}

}

function nw_unblock_ip_address($ip_addr, $msg=false) {

	global $conf, $pmode, $banned_ips;

	if ((($conf["global"]["singleprocessmode"][0]) || ($pmode=="master")) && ($banned_ips[$ip_addr])) {
	
		$source=strtok($banned_ips[$ip_addr]["source"], ".");
		$rejs=$banned_ips[$ip_addr]["rejects"];
		
		unset($banned_ips[$ip_addr]);
		techo($source." : unblocked IP address ".$ip_addr." (".(int)$rejs." rejs".($msg===false?"":(", ".$msg)).")", NW_EL_BLOCKING);

	} else {

		int_sendtomaster(NM_UNBLOCK_IP, $ip_addr);
	
	}

}

function logger_run($logger_id) {

	global $conf, $children_logsck, $modules, $plgset, $pmode;

	$pmode="logger";

	pcntl_signal(SIGTERM, SIG_DFL);
	pcntl_signal(SIGHUP, SIG_IGN);

	$mypid=posix_getpid();

	$lids=log_ids();
	posix_setgid($lids["gid"]);
	posix_setuid($lids["uid"]);

	techo("logger process #".$logger_id." is running (pid=".$mypid.")");

	while (!$logger_exit) {
		
		$r=socket_read($children_logsck, INT_MSGSIZE);

		switch($r) {

			case "TERM": 
			$logger_exit=true;
			break;

			default:

			$l=unserialize($r);

			// Reverse DNS query if the server hasn't done it before
			
			if (($conf["global"]["hostnamelookups"][0]) && ($conf["global"]["hostnamelookupsby"][0]=="logger") && ($rhost=nw_gethostbyaddr($l[2]))) $l[1]=$rhost;

			// And call the logging modules
			
			if ($nb_log_mods=count($modules["log"])) for ($a=0;$a<$nb_log_mods;$a++) $modules["log"][$a]->log_hit($l[0], $l[1], $l[2], $l[3], $l[4], $l[5], $l[6], $l[7], $l[8]);

			break;
			
		}
	
	}

	techo("logger process #".$logger_id." stopped");

	exit(0);

}

function spawn_loggers($nb_loggers) {
	
	global $logger_pids;

	static $logger_id;

	for ($a=0;$a<$nb_loggers;++$a) {

		$pid=pcntl_fork();
		++$logger_id;		

		if ($pid===0) {
			
			logger_run($logger_id);

		} else {
			
			$logger_pids[$pid]=$logger_id;
			if ($nb_loggers>1) usleep(100000);

		}
			
	}

}

// Begin

set_time_limit(0);
$pmode="master";

techo("aEGiS nanoweb/".VERSION." (C) 2002-2005 by sIX / aEGiS");

$stats_start=time();

if (version_compare(phpversion(), REQUIRED_PHP_VERSION)<0) errexit("nanoweb needs PHP >= ".REQUIRED_PHP_VERSION." (you are using ".phpversion().")");
if (version_compare(phpversion(), "4.3.0")>=0) $sckv3=true;

$os=((strpos(strtolower(PHP_OS), "win")===0) || (strpos(strtolower(PHP_OS), "cygwin")!==false))?"win32":"unix";

switch ($os) {

	case "win32":
	define("NW_BSAFE_READ_OPEN", "rb");
	define("NW_BSAFE_WRITE_OPEN", "wb");
	define("NW_BSAFE_APP_OPEN", "ab");
	if (!defined("SOCKET_EWOULDBLOCK")) define("SOCKET_EWOULDBLOCK", 10035);
	break;

	default:
	define("NW_BSAFE_READ_OPEN", "r");
	define("NW_BSAFE_WRITE_OPEN", "w");
	define("NW_BSAFE_APP_OPEN", "a");
	if (!defined("SOCKET_EWOULDBLOCK")) define("SOCKET_EWOULDBLOCK", 11);
	break;

}

foreach ($TEST_FUNCS as $f_name=>$f_mandatory) if (!is_callable($f_name)) {

	if ($f_mandatory) errexit("function '".$f_name."' not available, aborting");
	else techo("WARN: function '".$f_name."' not available", NW_EL_WARNING);

}

// Parse command line

if ($_SERVER["argc"]>1) for($a=1;$a<$_SERVER["argc"];$a++) {

	if (($a==1) && (substr($_SERVER["argv"][$a], 0, 1)!="-")) {

		$cmdline_cf=$_SERVER["argv"][1];
	
	} else {

		$ca=explode("=", $_SERVER["argv"][$a]);
		$ck=array_shift($ca);
		$cv=implode("=", $ca);

		switch($ck) {

			case "-?":
			case "-h":
			case "--help":
			die($cmdline_help);
			break;

			case "-v":
			case "--version":
			die(VERSION."\n");
			break;
			
			case "-c":
			case "--config":
			$cmdline_cf=$cv;
			break;

			case "-o":
			case "--set-option":
			$cmdline_conf_overrides[]=$cv;
			break;

			case "-a":
			case "--add-option":
			$cmdline_conf_adds[]=$cv;
			break;

			case "-d":
			case "--start-daemon":
			$start_daemon=true;
			break;
			
			case "-q":
			case "--quiet":
			$quiet=true;
			break;

			case "--debug":
			$nw_debug=true;
			case "--verbose":
			break;
			
			case "--config-test":
			case "-t":
			$config_test=true;
			break;
			
			default:
			errexit("unknown argument : ".$_SERVER["argv"][$a].", try --help");
			break;

		}
	
	}
	
}

if ($cmdline_cf) $conffile=$cmdline_cf; else $conffile=DEFAULT_CONFIG_FILE;
if (!is_readable($conffile)) errexit("unable to read configuration (".$conffile."), aborting");

unset($cmdline_help);

nanoweb_init($conffile);

if ($config_test) {
	
	techo("configuration test successful");
	exit(0);

}

if ($conf["global"]["servermode"][0]!="inetd") {

	// Create socket(s) and start listening
	
	if ($sckv3) {
		
		$setsockopt="socket_set_option";
		$getsockopt="socket_get_option";

	} else {
		
		$setsockopt="socket_setopt";
		$getsockopt="socket_getopt";

	}
	
	foreach ($conf["global"]["listenport"] as $lport) {
	
		$lport=(int)$lport;
		
		if (($sock = @socket_create(AF_INET, SOCK_STREAM, 0))<0) errexit("socket create failed : ".socket_strerror(socket_last_error()));

		if (is_callable($setsockopt) && is_callable($getsockopt)) {
		
			$setsockopt($sock, SOL_SOCKET, SO_REUSEADDR, 1);

			$sbuf=$getsockopt($sock, SOL_SOCKET, SO_SNDBUF);
			$rbuf=$getsockopt($sock, SOL_SOCKET, SO_RCVBUF);

			if ($sbuf<(SCK_WRITE_PACKET_SIZE*32)) $setsockopt($sock, SOL_SOCKET, SO_SNDBUF, SCK_WRITE_PACKET_SIZE*32);
			if ($rbuf<(SCK_READ_PACKET_SIZE*32)) $setsockopt($sock, SOL_SOCKET, SO_RCVBUF, SCK_READ_PACKET_SIZE*32);

		}
		
		if (!@socket_bind($sock, $conf["global"]["listeninterface"][0], $lport)) errexit("socket bind failed on port ".$lport." : ".socket_strerror(socket_last_error($sock)));
		if (!@socket_listen($sock, $conf["global"]["listenqueue"][0])) errexit("socket listen failed on port ".$lport." : ".socket_strerror(socket_last_error($sock)));

		socket_set_nonblock($sock);

		$lsocks[$lport]=$sock;
		$lports[$sock]=$lport;

	}

}

if ($pcntl_av) {

	$sck_pair=array();
	socket_create_pair(AF_UNIX, SOCK_DGRAM, 0, $sck_pair);

	$children_sck=&$sck_pair[0];
	$master_sck=&$sck_pair[1];

	socket_set_nonblock($children_sck);
	socket_set_nonblock($master_sck);

}

$plnset=array($children_sck);
foreach ($lsocks as $sck) $plnset[]=$sck;

if ($conf["global"]["servermode"][0]!="inetd") {

	techo("listening on port".(count($lports)>1?"s":"")." ".implode(", ", $lports));

	$stdfd = fopen("php://stdin", "r");
	fclose($stdfd);
	$stdfd = fopen(($os == "unix") ? "/dev/null" : "NUL", "r");

} else {

	$inetd_in=fopen("php://stdin", NW_BSAFE_READ_OPEN);
	set_file_buffer($inetd_in, 0);

	techo("running in inetd mode");

}

$def_cnx=($conf["global"]["servermode"][0]=="inetd");

if ($start_daemon) {

	if (!$posix_av || !$pcntl_av) errexit("posix and pcntl PHP extensions are needed for --start-daemon");

	$npid = pcntl_fork();

	if ($npid == -1) {
		
		errexit("unable to pcntl_fork()");

	} else if ($npid) {

		exit(0);

	}

	posix_setsid();
	usleep(100000);

	$npid = pcntl_fork();

	if ($npid == -1) {
		
		errexit("unable to pcntl_fork()");

	} else if ($npid) {

		techo("running in background");

		exit(0);

	}

}

if ($nb_loggers=$conf["global"]["loggerprocess"][0]) {

	// Prepare and spawn logger processes if specified
	
	techo("spawning loggers");

	$sck_pair=array();
	socket_create_pair(AF_UNIX, SOCK_DGRAM, 0, $sck_pair);

	$children_logsck=&$sck_pair[0];
	$loggers_sck=&$sck_pair[1];
	
	socket_set_nonblock($loggers_sck);
	
	spawn_loggers($nb_loggers);

} else {

	// Be sure not to ask anything to loggers

	$conf["global"]["hostnamelookupsby"][0]="server";

}

if ($posix_av && $conf["global"]["pidfile"][0]) {
	
	$pidfile=$conf["global"]["pidfile"][0];
	
	if ($f=fopen($pidfile, "w")) {
	
		fputs($f, (string)posix_getpid()."\n");
		fclose($f);

	} else {

		techo("WARN: unable to open pid file '".$pidfile."'", NW_EL_WARNING);
		unset($pidfile);
	
	}

}

if (empty($nw_debug)) error_reporting(E_PARSE | E_ERROR);

if ($conf["global"]["servermode"][0]!="inetd") techo("ready and accepting connections");

while (true) {

	$cnx=$def_cnx;
	
	while (!$cnx) {

		declare (ticks = 1) {
		
			// Allow to catch signals here			
			
			$lnset=$plnset;

		}
		
		$ns=socket_select($lnset, $write=NULL, $except=NULL, 1);
		
		if ($ns) foreach ($lnset as $lnact) {
				
			if ($lnact==$children_sck) {
			
				while ($msg=socket_read($children_sck, INT_MSGSIZE)) {

					// Message from a child process
					
					$mtype=substr($msg, 0, 5);
					if (strlen($msg)>5) $mcontent=unserialize(substr($msg, 5));

					switch ($mtype) {

						case NM_HIT: 
							
						// content : 0 => pid, 1 => request status, 2 => length, 3 => vhost
						
						if (is_array($mcontent)) {

							++$stats_resperr[$mcontent[1]];
							++$stats_vhosts[$conf[$mcontent[3]]["servername"][0]];
							++$stats_hits;
							$stats_xfer+=(float)$mcontent[2];
							
							if ($scoreboard[$spid=$mcontent[0]]) {
							
								$scoreboard[$spid][NW_SB_STATUS]="(waiting for request)";

							}

						}

						break;


						case NM_RESTART_LOGGERS:

						techo("respawning loggers");
						spawn_loggers($conf["global"]["loggerprocess"][0]);
						
						break;


						case NM_RELOAD_THEME:

						// content : theme id
						
						clearstatcache();

						if ((is_array($themes[$mcontent])) && ($themes[$mcontent]["_mtime"]!=filemtime($fname=$themes[$mcontent]["_fname"]))) {
							
							$tmp_thm=load_theme($fname, true, true);
							$themes[$mcontent]=$tmp_thm;
							$themes[$fname]=$tmp_thm;
							$themes[$tmp_thm["_fname"]]=$tmp_thm;

						}

						break;


						case NM_SERVER_STATE:

						// content : 0 => pid, 1 => status text, 2 => remote host
						
						if ($scoreboard[$spid=$mcontent[0]]) {
						
							$scoreboard[$spid][NW_SB_STATUS]=$mcontent[1];
							if ($mcontent[2]) $scoreboard[$spid][NW_SB_PEERHOST]=$mcontent[2];

						}
						
						break;


						case NM_BLOCK_IP:

						// content : 0 => ip address, 1 => type (PERM|TEMP), 2 => source, 3 => expiration

						if (is_array($mcontent)) nw_block_ip_address($mcontent[0], $mcontent[1], $mcontent[2], $mcontent[3]);

						break;
					

						case NM_UNBLOCK_IP:

						// content : ip address
						
						if ($mcontent) nw_unblock_ip_address($mcontent);
						
						break;
					
					}

				}

			} else {

				$sock=$lnact;
				$lport=$lports[$sock];

				if ((($active_servers<$conf["global"]["maxservers"][0]) || (!$conf["global"]["maxservers"][0])) && (is_resource($msgsock=@socket_accept($sock)))) {
				
					// We do have a connection
					
					$remote_ip=$remote_port=0;
					socket_getpeername($msgsock, $remote_ip, $remote_port);
					
					if (is_array($banned_ips[$remote_ip])) {

						// Disconnect if IP address is banned

						techo("rejected connection #".(++$banned_ips[$remote_ip]["rejects"])." from blocked IP address ".$remote_ip, NW_EL_BLOCKING);
						socket_close($msgsock);
											
						++$stats_rej;
					
					} else if ($remote_ip) {
					
						// Or handle the new connection
						
						$cnx=true;

						++$stats_cnx;

					} else {

						// Cannot obtain peer IP address, something is wrong (but not worth throwing a notice)
						
						socket_close($msgsock);
					
					}
				
				}
			
			}
		
		} 

		if (!$conf["global"]["singleprocessmode"][0]) while (($deadpid=pcntl_waitpid(-1, $cstat, WNOHANG)) && ($deadpid!=-1)) {

			// Dead child

			if (($dead_logger=$logger_pids[$deadpid]) && ($conf["global"]["loggerprocess"][0])) {

				// Dead logger (this is abnormal, we have to restart one)

				if (in_array($dead_logger, $killed_loggers)) {

					unset($killed_loggers[array_search($dead_logger, $killed_loggers)]);
				
				} else {
				
					techo("logger process #".$dead_logger." died (pid=".$deadpid."), respawning", NW_EL_WARNING);

				}
					
				unset($logger_pids[$deadpid]);
				
				spawn_loggers(1);
			
			} else {

				// Dead child server, clear servers table

				unset($scoreboard[$deadpid]);
				--$active_servers;
			
			}
		
		}

		$ct=time();
		foreach ($banned_ips as $ip_addr=>$bip) if (($bip["type"]=="TEMP") && ($bip["expires"]<=$ct)) nw_unblock_ip_address($ip_addr, "expired");
		
	}
	
	if ($conf["global"]["singleprocessmode"][0]) {
		
		$pid=0;

		// Invalidate access and rp caches every SPM_CACHES_LIFETIME connections

		if (($stats_cnx%SPM_CACHES_LIFETIME)==0) {
			
			clearstatcache();
			unset($access_cache);
			unset($rp_cache);

		}

	} else {
		
		$pid=pcntl_fork();
		if ($pid===0) $pmode="server";

	}

	if ($pid===0) {
	
		if ($posix_av) $mypid=posix_getpid();
		
		if (!$conf["global"]["singleprocessmode"][0]) {
			
			foreach ($lsocks as $sock) socket_close($sock);
			
			set_time_limit($conf["global"]["childlifetime"][0]);

		}
		
		if ($conf["global"]["servermode"][0]!="inetd") {
		
			socket_set_nonblock($msgsock);
			$pfdset=array($msgsock);

			if (($conf["global"]["hostnamelookups"][0]) && ($conf["global"]["hostnamelookupsby"][0]!="logger") && ($rhost=nw_gethostbyaddr($remote_ip))) {
				
				$remote_host=$rhost;
				_server_report_state("(connected)", $remote_host);

			} else {
			
				$remote_host=$remote_ip;

			}

		} else {

			$remote_ip=getenv("INETD_REMOTE_IP");
			$remote_port=getenv("INETD_REMOTE_PORT");
		
		}

		$rq_count=0;
		
		while ($cnx) {
		
			$sck_connected=true;
			$http_continue=false;
			$http_rq_block=$buf=read_request($sck_connected, $dp, $pn);
			$pri_redir=$http_uri=$out_headers="";
			$pri_err=$pri_redir_code=$rq_err=0;
			$add_nsv=$htreq_headers=$out_add_headers=array();

			if ($sck_connected) {

				if (strlen($buf)!=$dp+4) $add_req=substr($buf, $dp+4); else $add_req="";
				$tmp_arr=explode("\n", substr($buf, 0, $dp));
				$l=false;
				
				foreach ($tmp_arr as $s) {
				
					$s=trim($s);

					if (!$l) {

						$http_action=strtok($s, " ");
						$http_resource=strtok(" ");
						$http_protocol=strtoupper(strtok("/"));
						$http_version=strtok("");
						$l=true;

						if ($http_protocol!="HTTP") {

							// Invalid protocol

							$pri_err=400;
							$add_errmsg="Unable to serve requested protocol.<br><br>";
						
						}

					} else {

						if (strpos($s, ":")===false) {

							// Invalid request header

							$pri_err=400;
							$add_errmsg="Invalid request header received.<br><br>";
						
						} else {
						
							$hd_key=strtoupper(strtok($s, ":"));
							$hd_val=trim(strtok(""));

							if (isset($htreq_headers[$hd_key])) {

								$pri_err=400;
								$add_errmsg="Duplicate request header '{$hd_key}'<br><br>";

							} else {
							
								$htreq_headers[$hd_key]=$hd_val;

							}

						}

					}
				
				}

				// Decode Host header
					
				$host=strtok(trim(strtolower($htreq_headers["HOST"])), ":");
				$vhost=nw_host_to_vhost($host, $lport);

				if ($auth_hdr=$htreq_headers["AUTHORIZATION"]) {

					// Decode HTTP Authentication header

					$dtmp=explode(" ", $auth_hdr);
					$auth_type=$dtmp[0];
					$auth_lp=explode(":", base64_decode($dtmp[1]));
					$auth_user=$auth_lp[0];
					$auth_pass=$auth_lp[1];
				
				} else $auth_type=$auth_user=$auth_pass="";

				// Decode Keep-Alive header
				
				$keepalive=(strtolower(trim($htreq_headers["CONNECTION"]))=="keep-alive" && (int)$conf["global"]["keepalive"][0]>1);
				if ($keepalive && (++$rq_count>=(int)$conf["global"]["keepalive"][0])) $keepalive=false;
				$cnx=$keepalive;
				
				// Set Uid and Gid

				$cfgid=$conf[$vhost]["group"][0];
				$cfuid=$conf[$vhost]["user"][0];

				if ($posix_av) {
				
					$ugtok=$sysusr[$cfuid]["uid"].$sysgrp[$cfgid]["gid"];
					
					if ($uid_set) {

						if ($uid_set!=$ugtok) {

							// Keep-alive request for another user/group vhost, this is bad
							
							$pri_err=400;
						
						}
					
					} else {
					
						if ($setgid=$sysgrp[$cfgid]["gid"]) posix_setgid($setgid);
						if ($setuid=$sysusr[$cfuid]["uid"]) posix_setuid($setuid);

						$uid_set=$ugtok;
					
					}
				
				}
				
				$docroot=$conf[$vhost]["documentroot"][0];
				$docroot_prefix="";

				if ($exp_hdr=$htreq_headers["EXPECT"]) {

					// Enforce HTTP Expect header

					if (trim(strtolower($exp_hdr))=="100-continue") {
						
						$http_continue=true;

					} else {

						$pri_err=417;
					
					}
					
				}
				
				if ($p1=$http_resource) {

					$p1=explode("?", $p1);
					$real_uri=ltrim($http_uri=rawurldecode($p1[0]), "/");
					$http_uri=str_replace(chr(0), "", $http_uri);
					$query_string=$p1[1];

					$hu=$docroot.$real_uri;

					// Load access files if needed
					
					unset($access);
					
					if (is_dir($hu)) {
						
						$uridir=substr($http_uri, 1);
						
					} else if (is_dir($docroot.($uridn=dirname($http_uri)))) {

						$uridir=substr($uridn, 1);
					
					} else $uridir="";

					if (($accessdir=nw_realpath($docroot.$uridir)) && ($conf["global"]["accessfile"][0])) load_access_files($accessdir, $access);

					core_modules_hook("before_decode");

					foreach (access_query("_aliases") as $key=>$val) if (strpos(rtrim($http_uri, "/"), rtrim($key, "/"))===0) {

						// Alias
						
						$docroot=$val.((substr($val, -1)==DIRECTORY_SEPARATOR)?"":DIRECTORY_SEPARATOR);
						$docroot_prefix=trim($key, "/")."/";
						$http_uri=str_replace($key, "", $http_uri);

						if ((is_dir($docroot.$http_uri)) && (substr($docroot.$http_uri, -1)!="/")) $pri_redir=nw_url_addslash($http_resource);

						break;

					}

					$http_uri=ltrim($http_uri, "/");
					
					if ($http_uri{0}=="~") {
						
						// User directory
						
						if (($udadd=$conf[$vhost]["userdir"][0]) && ($posix_av)) {

							$upos=strpos($http_uri, "/");
							
							$udname=substr($http_uri, 1, (($upos===false)?(strlen($http_uri)):($upos-1)));
							$udres=(($upos===false)?"":(substr($http_uri, $upos+1)));
							
							if ($udinf=@posix_getpwnam($udname)) {

								$tmpdr=$udinf["dir"].DIRECTORY_SEPARATOR.$udadd.DIRECTORY_SEPARATOR;
								
								if (is_dir($tmpdr)) {

									if ((is_dir($tmpdr.$udres)) && (substr($http_uri, -1)!="/")) {
										
										$pri_redir=nw_url_addslash($http_resource);

									} else {
								
										$docroot=$tmpdr;
										$docroot_prefix="~".$udname."/";
										$http_uri=$udres;
								
									}
								
								} else {

									// User exists but does not have a public html directory
									
									$pri_err=404;
								
								}
							
							} else {

								// User does not exists
								
								$pri_err=404;
							
							}

						}
					
					}
					
					if (is_dir($docroot.$http_uri) && !$pri_redir) {
						
						if ($http_uri && substr($http_uri, -1)!="/") {

							$pri_redir=nw_url_addslash($http_resource);
						
						} else if ($dilist=access_query("directoryindex", 0)) {
						
							$dis=explode(" ", $dilist);

							foreach ($dis as $diname) {
								
								switch ($diname{0}) {
								
									case DIRECTORY_SEPARATOR:

									if (@is_readable($diname)) {

										$docroot=dirname($diname).DIREECTORY_SEPARATOR;
										$http_uri=basename($diname);
										break;
									
									}
									
									break;
									
									default:
								
									if (@is_readable($docroot.$http_uri.$diname)) {

										$http_uri.=$diname;
										break;

									}
							
								}
							
							}

						}

					}

					$path_info="";
				
					if (access_query("allowpathinfo", 0) && !file_exists($docroot.$http_uri)) {
						
						// Try path_info

						$new_uri=$http_uri;
						
						while (!@is_file($docroot.$new_uri) && $new_uri) {

							$new_uri=substr($new_uri, 0, strrpos($new_uri, "/"));

							if (!@is_file($docroot.$new_uri) && $pie_arr=access_query("pathinfotryext")) foreach ($pie_arr as $pie_ext) if (@is_file($docroot.$new_uri.".".$pie_ext)) {

								$new_uri.=".".$pie_ext;
								break;
							
							}

						}

						if ($new_uri) {

							// Path_info found

							$path_info=substr($http_uri, strlen($new_uri));
							$http_uri=$new_uri;
						
						}
					
					}
					
					$rq_file=pathinfo($http_uri);

				}

				$hbn=basename($http_uri);
				unset($bad_rq);
				
				// File access security tests
				
				if (nw_realpath($docroot.$http_uri) && (strpos(nw_realpath($docroot.$http_uri), nw_realpath($docroot))===false)) {
					
					$bad_rq=NW_BAD_OUTSIDE_DOCROOT;

				} 
				
				if (($conf[$vhost]["ignoredotfiles"][0]) && ($hbn{0}==".") && ($hbn!="..") && ($hbn!=".")) {

					$bad_rq=NW_BAD_DOT_FILE;
				
				}

				if (($os == "win32") && in_array(strtolower(strtok($hbn, ".")), $win_devices)) {
					
					$bad_rq=NW_BAD_WIN_DEVICE;

				}
				
				if (($bad_rq==NW_BAD_OUTSIDE_DOCROOT) && ($als_arr=$conf[$vhost]["allowsymlinkto"])) {
					
					// Test for outside-docroot access exemptions (AllowSymlinkTo)
					
					$tdir=$http_uri;

					while ($tdir) {
						
						if ((is_link($docroot.$tdir)) && (strpos(nw_realpath(dirname($docroot.$tdir)), nw_realpath($docroot))===0)) foreach ($als_arr as $als) if (strpos(nw_realpath($docroot.$http_uri), nw_realpath($als))===0) {

							unset($bad_rq);
							break 2;

						}
						
						$tdir=substr($tdir, 0, strrpos($tdir, "/"));
					
					}

				}

				if ($bad_rq) switch ($bad_rq) {

					case NW_BAD_OUTSIDE_DOCROOT:
					techo("NOTICE: discarded request outside of document root (".$docroot.$http_uri.")");
					$http_uri="";
					$pri_err=404;
					break;

					case NW_BAD_DOT_FILE:
					techo("NOTICE: discarded request for dot file (".$docroot.$http_uri.")");
					$http_uri="";
					$pri_err=404;
					break;

					case NW_BAD_WIN_DEVICE:
					techo("NOTICE: discarded request for windows device file (".$docroot.$http_uri.")");
					$http_uri="";
					$pri_err=404;
					break;
					
				}

				$sst=$http_action." http://".$htreq_headers["HOST"]."/".$real_uri.($query_string?("?".$query_string):"");
				_server_report_state($sst);

				if ($hu!=($docroot.$http_uri)) {
				
					// Reload access files if needed
					
					$hu=$docroot.$http_uri;

					unset($access);
					
					if (is_dir($hu)) {
						
						$uridir=$http_uri;

					} else if (is_dir($docroot.($uridn=dirname($http_uri)))) {

						$uridir=$uridn;
					
					} else $uridir="";

					if (($accessdir=nw_realpath($docroot.$uridir)) && ($conf["global"]["accessfile"][0])) {
						
						load_access_files($accessdir, $access);

					}

				}
				
				$out_contenttype=$default_ct=access_query("defaultcontenttype", 0);
				
				// AuthLocation handler
				
				$bypass_auth = false;
				
				if ($authls=access_query("authlocation")) {
					
					$bypass_auth = true;
					
					foreach ($authls as $authl) if (strpos("/".$real_uri, $authl) === 0) {

						$bypass_auth = false;
						break;

					}
						
				}
				
				// Auth handler
				
				$logged_user="";
				
				if (($rauths=access_query("authrequire")) && (!$bypass_auth)) {
					
					foreach ($rauths as $rauth) {

						if ($spos=strpos($rauth, " ")) {
						
							$authtype=strtolower(strtok($rauth, " "));
							$authargs=trim(strtok(""));

						} else {

							$authtype=strtolower($rauth);
							$authargs="";

						}
						
						$authmodn="auth_".strtolower($authtype);

						if (is_object($modules[$authmodn][0])) {
							
							if ($modules[$authmodn][0]->auth($auth_user, $auth_pass, $authargs)) {

								$logged_user=$auth_user;
								break;
							
							}

						} else {

							techo("WARN: authentication module not found for type '".$authtype."'", NW_EL_WARNING);
					
						}

					}
					
					if ($logged_user==="") {
					
						$logged_user=" ";
						$pri_err=401;
						$out_add_headers["WWW-Authenticate"]="Basic realm=\"".access_query("authrealm", 0)."\"";
						if ($emsg=access_query("authmessage", 0)) $add_errmsg.=$emsg."<br><br>";

					}
				
				}
				
				// Test for maximum URI length

				if (($conf[$vhost]["maxrequesturilength"][0]) && (strlen($http_resource)>$conf[$vhost]["maxrequesturilength"][0])) {

					$pri_err=414;
				
				}
				
				if ($htreq_headers["CONTENT-LENGTH"]) {

					// Read request content if there is one (POST requests)
					
					if (($maxblen=$conf[$vhost]["maxrequestbodylength"][0]) && ((int)$htreq_headers["CONTENT-LENGTH"]>$maxblen)) {

						// Request content is too large

						$pri_err=413;
					
					} else {
							
						if ($http_continue && !$pri_err) send_response(HTTP_VERSION." ".$HTTP_HEADERS[100]."\r\n\r\n", $sck_connected);

						$buf=$add_req;
						if (strlen($buf)<$htreq_headers["CONTENT-LENGTH"]) $buf.=read_request($sck_connected, $dp, $pn, $htreq_headers["CONTENT-LENGTH"]-strlen($buf));
						$htreq_content=substr($buf, 0, $htreq_headers["CONTENT-LENGTH"]);

					}
				
				}
				
				core_modules_hook("after_decode");

				if ($sck_connected) {

					switch ($http_action) {

						case "POST":

						if ((!access_query("_parseext", "_".strtolower($rq_file["extension"]))) && (is_file($docroot.$http_uri)) && (!$pri_parser)) {
							
							// Disallow POST on static content
							
							$pri_err=405;
							$out_add_headers["Allow"]=nw_allow_list($rq_file["extension"]);

						}
						
						case "GET":
						case "HEAD":

						if ($pri_err) {

							// Internal setting of http error

							$rq_err=$pri_err;
						
						} else if ($pri_redir) {

							// Internal redirection

							if ($rq_err<300) {
								
								$rq_err=$pri_redir_code
								or $rq_err=302;

							}

							$out_add_headers["Location"]=$pri_redir;
							if (version_compare($http_version, "1.0")<=0) $out_add_headers["URI"]=$pri_redir;

						} else if (is_object($umod=&$modules["url:/".$http_uri])) {

							// Module URL Hook

							if ($umod->modtype=="url2") {

								$lf=$umod;
								$lf->parser_open("", $real_uri, $rq_err, $out_add_headers, $out_contenttype);
							
							} else {
							
								$lf =& new static_response($umod->url($rq_err, $out_contenttype, $out_add_headers));

							}
						
						} else if (is_dir($docroot.$http_uri)) {

							// Directory without index

							$rq_err=404;
							core_modules_hook("directory_handler");

						} else if (!is_file($docroot.$http_uri)) {
							
							// 404 Not Found

							$rq_err=404;

						} else if (!is_readable($docroot.$http_uri)) {
							
							// 403 Forbidden
							
							$rq_err=403;

						} else  {

							// 200 OK

							$rq_err=200;
							if ($pp=access_query("forcehandler", 0)) $pri_parser=$pp;
							$lf=loadfile($docroot.$http_uri, $rq_file["extension"], $rq_err, $out_add_headers, $pri_parser);

							/* libphpHACK */
							#<off># if (isset($__nw_libphp_script)) { include($__nw_libphp_script); exit; }
							
							if ($mimetype=$mime[strtolower($rq_file["extension"])]) {
								
								// Lookup mime type in internal table
								
								$out_contenttype=$mimetype;
								
							} else if (is_callable("mime_content_type") && (!access_query("disablemimemagic", 0)) && ($mimetype=mime_content_type($docroot.$http_uri))) {

								// Fallback to mime magic if available

								$out_contenttype=$mimetype;

							} else {
								
								// Or use default

								$out_contenttype=$default_ct;

							}

						}

						break;

						case "OPTIONS":
						
						$rq_err=200;
						$out_add_headers["Allow"]=nw_allow_list($rq_file["extension"]);
						
						break;

						default: 

						if ($mmod=$modules["method:".$http_action]) {

							$rq_err=200;
							$lf=$mmod;
							$lf->parser_open("", $real_uri, $rq_err, $out_add_headers, $out_contenttype);

						} else if (!$http_action) {

							$rq_err=400;
							
						} else {
							
							$rq_err=501;

						}

						break;

					}

					unset($pri_parser);

					if ($rq_err!=200 && $rq_err!=416) {

						// Error messages
						
						if ($rq_err>=400) {

							if (($errordoc=trim(access_query("_errordocument", "_".$rq_err))) && (@is_readable($docroot.$errordoc))) {

									$add_nsv["REDIRECT_STATUS"]=$rq_err;
									$add_nsv["REDIRECT_URL"]="/".$GLOBALS["real_uri"];
									$http_uri=$errordoc;
									$errext=substr(strrchr($errordoc, "."), 1);
									$lf=loadfile($docroot.$errordoc, $errext, $rq_err, $out_add_headers);

									if ($mimetype=$mime[strtolower($errext)]) $out_contenttype=$mimetype; else $out_contenttype=$default_ct;
								
							} else {
							
								$out_contenttype="text/html";
								$lf =& new static_response(nw_error_page($rq_err));
								
								if ($errordoc) techo("WARN: unable to read error document : [".$rq_err."] ".$errordoc, NW_EL_WARNING);

							}
						
							$cnx=false;
						
						} else if ($rq_err>=301) {

							$lf=$null_response;
						
						}
					
					}

					if ($http_action=="HEAD") {
						
						$plen=$lf->content_length;
						$hlf=$lf;
						$lf=$null_response;
						$lf->content_length=$plen;

					}

					core_modules_hook("before_response");

					if (!$rq_err) $rq_err=500;
					
					$chunked=nw_use_chunked_encoding();
					if ($chunked==="CLOSE") $cnx=$keepalive=$chunked=false;

					// Send the response headers and content

					$sent_len=0;
					$first_chunk=true;

					while ((($buf = $lf->parser_get_output()) !== "") || $first_chunk) {

						if ($first_chunk) {

							$hbuf=build_response_headers()."\r\n";
						
						}
						
						if ($chunked) {

							$chunk_header=dechex(strlen($buf))."\r\n";
							$metasize=strlen($chunk_header)+2;
							$rbytes=send_response($hbuf.($buf!==""?$chunk_header.$buf."\r\n":""), $sck_connected);
							$sent_len+=($rbytes-$metasize);

						} else {

							$sent_len+=send_response($hbuf.$buf, $sck_connected);
						
						}

						if ($first_chunk) {
						
							$sent_len-=strlen($hbuf);
							$hbuf="";
							$first_chunk=false;

						}
						
						if ($lf->parser_eof() || !$sck_connected || ($buf === NULL)) break;

					}

					$lf->parser_close();

					if ($chunked) {
						
						$meta_len=0;
						send_response("0\r\n\r\n", $sck_connected);

					}

					if (!$sck_connected) $cnx=false;
					if (($sent_content_length=$sent_len-$meta_len)<0) $sent_content_length=0;

					if ($conf["global"]["singleprocessmode"][0]) {
		
						// Increment stats
						
						++$stats_resperr[$rq_err];
						++$stats_vhosts[$conf[$vhost]["servername"][0]];
						++$stats_hits;
						$stats_xfer+=(float)$sent_len;

					} else {
					
						// Report hit to master
						
						int_sendtomaster(NM_HIT, array($mypid, $rq_err, $sent_len, $vhost));

					}
					
					if ($conf["global"]["loggerprocess"][0]) {
					
						// Send the logging infos to dedicated processes
						
						$log_arr=array($vhost, $remote_host, $remote_ip, $logged_user, trim($tmp_arr[0]), $rq_err, $sent_content_length, $htreq_headers["REFERER"], $htreq_headers["USER-AGENT"]);

						$logmsg=serialize($log_arr);
						$msglen=strlen($logmsg);

						if ($msglen>INT_MSGSIZE) {

							techo("WARN: internal communication error (packet too long)", NW_EL_WARNING);
						
						} else {

							$r=socket_write($loggers_sck, $logmsg);

							if ($r!=$msglen) techo("WARN: unable to communicate with logger process", NW_EL_WARNING);
							
						}

					} else {

						// Or do it ourselves
					
						if ($nb_loggers=count($modules["log"])) {

							for ($a=0;$a<$nb_loggers;$a++) $modules["log"][$a]->log_hit($vhost, $remote_host, $remote_ip, $logged_user, trim($tmp_arr[0]), $rq_err, $sent_content_length, $htreq_headers["REFERER"], $htreq_headers["USER-AGENT"]);

						}

					}

					$hlf=$lf=$null_response;

				}
			
			} else {

				$cnx=false;
			
			}

		}
		
		socket_shutdown($msgsock);
		socket_close($msgsock);
		
		if ((!$conf["global"]["singleprocessmode"][0]) || ($conf["global"]["servermode"][0]=="inetd")) exit(0);
	
	} else if ($pid==-1) {

		// Fork failed
		
		techo("WARN: unable to pcntl_fork()", NW_EL_WARNING);

	} else {

		// Fork successful
		
		$scoreboard[$pid][NW_SB_STATUS]="(connected)";
		$scoreboard[$pid][NW_SB_PEERHOST]=$remote_ip;
		$scoreboard[$pid][NW_SB_FORKTIME]=time();

		++$active_servers;
	
	}
	
}

?>
