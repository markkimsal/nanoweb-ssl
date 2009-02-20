<?php

/*

Nanoweb CGI support module
==========================

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

class mod_cgi {

	var $modtype="parser_CGI";
	var $modname="CGI support";
	
	var $use_proc_open;
	var $request_env;

	var $po;
	var $p;
	var $peof;
	
	// Init

	function init() {

		// Register also as a core hook module
		
		$GLOBALS["modules"]["core_after_decode"][]=&$this;

		if (is_callable("proc_open")) $this->use_proc_open=true;

	}

	// After decode hook

	function main() {

		global $accessdir, $docroot, $http_uri, $pri_parser, $pri_err, $add_errmsg;

		if (is_file($docroot.$http_uri)) {
		
			if ($cgiscd=access_query("cgiscriptsdir")) foreach ($cgiscd as $cgidir) if (strpos(rtrim($accessdir, "/"), rtrim($cgidir, "/"))===0) {

				if (is_callable("is_executable")) {
				
					$isexec=is_executable($docroot.$http_uri);

				} else $isexec=true;

				if ($isexec) {
				
					$pri_parser='CGI $SCRIPT_FILENAME';

				} else {

					switch (access_query("cgiscriptnoexec", 0)) {

						case "error":

						$pri_err=500;
						$add_errmsg="mod_cgi: the file is not executable<br><br>";

						break;


						case "raw":
						default:
						
					}
					
				}

				break;

			}
	
		}
	
	}
	
	// Parser functions	
	
	function parser_open($args, $filename, &$rq_err, &$cgi_headers) {

		global $conf, $os, $htreq_headers;

		$cgiexec=$args;

		if ($phpopts=access_query("cgiphpoption")) foreach ($phpopts as $opt) $cgiexec.=" -d ".$opt;

		$nsv=nw_server_vars(true);
		if ($conf["global"]["cgifilterpathinfo"][0]) unset($nsv["PATH_INFO"]);

		putenv("GATEWAY_INTERFACE=CGI/1.1");

		foreach($nsv as $key=>$var) putenv($key."=".$var);

		$this->request_env=$nsv;

		if ($htreq_headers["CONTENT-LENGTH"]) {
		
			putenv("CONTENT_TYPE=".$htreq_headers["CONTENT-TYPE"]);
			putenv("CONTENT_LENGTH=".$htreq_headers["CONTENT-LENGTH"]);

			if ($this->use_proc_open) {

				$ds=array(0 => array("pipe", "r"), 1 => array("pipe", "w"));

				if ($this->po=proc_open($cgiexec, $ds, $fds)) {

					$this->peof=false;
					fwrite($fds[0], $GLOBALS["htreq_content"]);
					fclose($fds[0]);
					$this->p=$fds[1];

				} else {

					$this->peof=true;
					$rq_err=500;
					techo("WARN: cannot proc_open() pipes to '".$cgiexec."'", NW_EL_WARNING);

				}
			
			} else {
			
				$tdn=$conf["global"]["tempdir"][0]
				or $tdn=$conf["global"]["tempdirectory"][0];
				
				$tmp_filename=$tdn.DIRECTORY_SEPARATOR."nweb_cgi_post.".$GLOBALS["mypid"];

				$mask=umask();
				umask(0177);
				
				if ($ftmp=@fopen($tmp_filename, "w")) {

					fwrite($ftmp, $GLOBALS["htreq_content"]);
					fclose($ftmp);

				} else {

					$this->peof=true;
					$rq_err=500;
					techo("WARN: unable to open temporary file '".$tmp_filename."' for writing", NW_EL_WARNING);
				
				}

				umask($mask);
				
				$this->tmpfile=$tmp_filename;
				$cgipiped=$cgiexec."<".$tmp_filename;
				
				if ($this->p=@popen($cgipiped, NW_BSAFE_READ_OPEN)) {

					$this->peof=false;

				} else {
					
					$this->peof=true;
					$rq_err=500;
					techo("WARN: cannot popen() pipe to '".$cgiexec."'", NW_EL_WARNING);

				}
		
			}
		
		} else {

			if ($this->use_proc_open) {

				$ds=array(1 => array("pipe", "w"));

				if ($this->po=proc_open($cgiexec, $ds, $fds)) {

					$this->peof=false;
					$this->p=$fds[1];
									
				} else {

					$this->peof=true;
					$rq_err=500;
					techo("WARN: cannot proc_open() pipe to '".$cgiexec."'", NW_EL_WARNING);

				}
			
			} else {
				
				if ($this->p=@popen($cgiexec, NW_BSAFE_READ_OPEN)) {

					$this->peof=false;

				} else {
					
					$this->peof=true;
					$rq_err=500;
					techo("WARN: cannot open pipe to '".$cgiexec."'", NW_EL_WARNING);

				}

			}
		
		}

		if ($this->p) while ($lastread!="\r\n" && $lastread!="\n") {
			
			if (!$lastread=fgets($this->p, 1024)) break;
			$content.=$lastread;
		}

		if ( (($p1=strpos($content, "\r\n\r\n"))!==false) || (($p1=strpos($content, "\n\n"))!==false) ) {
		
			if ((strpos($content, "\r\n\r\n"))!==false) $pn=4; else $pn=2;
			$headers=explode("\n", trim(substr($content, 0, $p1)));
			$content=substr($content, $p1+$pn);

		}

		$GLOBALS["http_resp"]="";
		
		$cnh=access_query("cginoheader");
		
		foreach ($headers as $s) if ($s=trim($s)) {

			if (substr($s, 0, 5)=="HTTP/") {

				$hd_key="STATUS";
				strtok($s, " ");
			
			} else {
			
				$hd_key=strtok($s, ":");

			}

			$hd_val=trim(strtok(""));
			$hku=strtoupper($hd_key);
			
			if ($cnh) foreach ($cnh as $nohdr) if ($hku==strtoupper($nohdr)) $hd_key="";
			
			if ($hd_key) {
			
				if ($hku=="SET-COOKIE") {
					
					$cgi_headers["cookies"][]=$hd_val;

				} else {

					$cgi_headers[$hd_key]=$hd_val;

				}

			}
		
		}

	}

	function parser_get_output() {

		$tmp=fread($this->p, 1024);
		if (feof($this->p)) $this->peof=true;
		return($tmp);
	
	}
	
	function parser_eof() {

		return($this->peof);
	
	}
	
	function parser_close() {

		if ($this->tmpfile && is_file($this->tmpfile)) unlink($this->tmpfile);

		if ($this->use_proc_open) {
			
			fclose($this->p);
			proc_close($this->po);
		
		} else {
			
			@pclose($this->p);

		}

		if (is_array($this->request_env)) {
			
			foreach (array_keys($this->request_env) as $cleanenv) putenv($cleanenv."=");
			unset($this->request_env);

		}

	}

}

?>
