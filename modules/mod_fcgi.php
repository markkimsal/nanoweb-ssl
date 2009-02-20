<?php

/*

Nanoweb FastCGI support module
==============================

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

define(FCGI_VERSION_1, 1);

define(FCGI_BEGIN_REQUEST, 1);
define(FCGI_ABORT_REQUEST, 2);
define(FCGI_END_REQUEST, 3);
define(FCGI_PARAMS, 4);
define(FCGI_STDIN, 5);
define(FCGI_STDOUT, 6);
define(FCGI_STDERR, 7);
define(FCGI_DATA, 8);
define(FCGI_GET_VALUES, 9);
define(FCGI_GET_VALUES_RESULT, 10);

class mod_fcgi {

	function mod_fcgi() {

		$this->modtype="parser_FCGI";
		$this->modname="FastCGI support";
	
	}

	function build_fcgi_packet($type, $content) {

		$clen=strlen($content);
				
		$packet=chr(FCGI_VERSION_1);
		$packet.=chr($type);
		$packet.=chr(0).chr(1); // Request id = 1
		$packet.=chr((int)($clen/256)).chr($clen%256); // Content length
		$packet.=chr(0).chr(0); // No padding and reserved
		$packet.=$content;

		return($packet);
	
	}

   function build_fcgi_nvpair($name, $value) {

		$nlen = strlen($name);
		$vlen = strlen($value);
			 
		if ($nlen < 128) {
		   
			$nvpair = chr($nlen);

		} else {
		   
			$nvpair = chr(($nlen >> 24) | 0x80) . chr(($nlen >> 16) & 0xFF) . chr(($nlen >> 8) & 0xFF) . chr($nlen & 0xFF);

		}

		if ($vlen < 128) {
		   
			$nvpair .= chr($vlen);

		} else {
		   
			$nvpair .= chr(($vlen >> 24) | 0x80) . chr(($vlen >> 16) & 0xFF) . chr(($vlen >> 8) & 0xFF) . chr($vlen & 0xFF);

		}

		return $nvpair . $name . $value;
     
	} 
	
	function decode_fcgi_packet($data) {

		$ret["version"]=ord($data{0});
		$ret["type"]=ord($data{1});
		$ret["length"]=(ord($data{4}) << 8)+ord($data{5});
		$ret["content"]=substr($data, 8, $ret["length"]);

		return($ret);
	
	}

	function parser_open($args, $filename, &$rq_err, &$cgi_headers) {

		global $conf, $add_errmsg;
		
		// Connect to FastCGI server

		$fcgi_server=explode(":", $args);

		if (!$this->sck=fsockopen($fcgi_server[0], $fcgi_server[1], $errno, $errstr, 5)) {

			$rq_err=500;
			$tmperr="mod_fcgi: unable to contact application server ($errno : $errstr).";
			$add_errmsg.=($tmperr."<br><br>");
			techo("WARN: ".$tmperr, NW_EL_WARNING);
			return (false);

		}

		// Begin session
		
		$begin_rq_packet=chr(0).chr(1).chr(0).chr(0).chr(0).chr(0).chr(0).chr(0);
		fwrite($this->sck, $this->build_fcgi_packet(FCGI_BEGIN_REQUEST, $begin_rq_packet));

		// Build params
		
		$fcgi_params_packet.=$this->build_fcgi_nvpair("GATEWAY_INTERFACE", "FastCGI/1.0");
		$nsv=nw_server_vars();
		if ($conf["global"]["fcgifilterpathinfo"][0]) unset($nsv["PATH_INFO"]);
		foreach($nsv as $key=>$var) $fcgi_params_packet.=$this->build_fcgi_nvpair($key, $var);

		if ($rq_hdrs=$GLOBALS["htreq_headers"]) foreach ($rq_hdrs as $key=>$val) $fcgi_params_packet.=$this->build_fcgi_nvpair("HTTP_".str_replace("-", "_", $key),$val);
		
		if ($GLOBALS["http_action"]=="POST" && $GLOBALS["htreq_content"]) {
		
			$fcgi_params_packet.=$this->build_fcgi_nvpair("CONTENT_TYPE", $rq_hdrs["CONTENT-TYPE"]);
			$fcgi_params_packet.=$this->build_fcgi_nvpair("CONTENT_LENGTH", $rq_hdrs["CONTENT-LENGTH"]);

			$stdin_content=$GLOBALS["htreq_content"];
			
		} else $stdin_content="";

		// Send params
		
		fwrite($this->sck, $this->build_fcgi_packet(FCGI_PARAMS, $fcgi_params_packet));
		fwrite($this->sck, $this->build_fcgi_packet(FCGI_PARAMS, ""));
		
		// Build and send stdin flow

		if ($stdin_content) fwrite($this->sck, $this->build_fcgi_packet(FCGI_STDIN, $stdin_content));
		fwrite($this->sck, $this->build_fcgi_packet(FCGI_STDIN, ""));

		// Read answers from fastcgi server

		$content="";

		while (($p1=strpos($content, "\r\n\r\n"))===false) {

			$tmpp=$this->decode_fcgi_packet($packet=fread($this->sck, 8));
			$tl=$tmpp["length"]%8;
			$tadd=($tl?(8-$tl):0);
			$resp=$this->decode_fcgi_packet($packet.fread($this->sck, $tmpp["length"]+$tadd));

			if ($valid_pck=($resp["type"]==FCGI_STDOUT || $resp["type"]==FCGI_STDERR)) $content.=$resp["content"];

			if ($resp["type"]==FCGI_STDERR) techo("WARN: mod_fcgi: app server returned error : '".$resp["content"]."'", NW_EL_WARNING);

		}

		if (feof($this->sck)) $this->peof=true;
		
		if ($p1) {
		
			$headers=explode("\n", trim(substr($content, 0, $p1)));
			$content=substr($content, $p1+4);

		}

		$GLOBALS["http_resp"]="";
		
		$cnh=access_query("fcginoheader");
		
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

		$this->parsed_output=$content;

	}

	function parser_get_output() {

		if (!$this->peof && !$this->parsed_output) {
		
			$tmpp=$this->decode_fcgi_packet($packet=fread($this->sck, 8));
			$tl=$tmpp["length"]%8;
			$tadd=($tl?(8-$tl):0);
			$resp=$this->decode_fcgi_packet($packet.fread($this->sck, $tmpp["length"]+$tadd));

			if ($valid_pck=($resp["type"]==FCGI_STDOUT || $resp["type"]==FCGI_STDERR)) {
				
				$content.=$resp["content"];

			} else {

				$this->peof=true;				
			
			}

			if ($resp["type"]==FCGI_STDERR) techo("WARN: mod_fcgi: app server returned error : '".$resp["content"]."'", NW_EL_WARNING);

		}

		if ($this->parsed_output) {

			$content=$this->parsed_output;
			$this->parsed_output="";
		
		}

		return($content);
	
	}
	
	function parser_eof() {

		return($this->peof);
	
	}
	
	function parser_close() {

		$this->peof=false;
		fclose($this->sck);

	}

}

?>
