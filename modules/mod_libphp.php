<?php

/*

	mod_libphp for nanoweb
	¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯

	This module is guessworking; you can adjust it to your own needs
	under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2, or (at your option)
	any later version.

	This module is distributed in the hope that it will work someday,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. In many cases
	it will break all your PHP scripts, so please make some checks!


	------------------ THIS IS MUCHO EXPERIMENTAL CODE ----------------


	- ParseExt = lphp LPHP

	- a hack in nanoweb.php is required,
	you find a few lines in it that must be uncommented if you
	search for "libphpHACK" in nanoweb.php

	- currently only allows very simple scripts (without
	function and object definitions), see this exaaaample.lphp:
		<html>
		<body>(?php
			echo "something " . (1+2);
		?)</body>
		</html>

	- scripts which try to define functions which were already
	defined in nanoweb won't work of course

	- header() and cookies() and much of the funny stuff
	is very unsupported;
	=> use nwheader() instead

	- currently (and in future) GET-requests only; POST ones
	will be passed to mod_cgi instead

	- this module will _NEVER_ be able to create a fully PHP
	compliant environment to run your scripts in


*/

class mod_libphp {


	function mod_libphp() {

		$this->modtype = "parser_LPHP";
		$this->modname = "EXPERIMENTAL inline PHP support for nanoweb";

		if (!$GLOBALS["TEST_FUNCS"]["socket_create"] || !function_exists("posix_mkfifo")) {
			$this->modtype = "disabled"; 
			techo("mod_libphp: cannot run under Windows or NT, need Linux/MacOS", NW_EL_ERROR);
		}

	}


	function parser_open($args, $filename, &$rq_err, &$cgi_headers) {

		global $conf, $http_action;
		global $__nw_libphp_script, $__nw_libphp_pipe, $__nw_libphp_headers, $NANOWEB;

		if (true||   ($http_action != "GET") && ($http_action != "HEAD")) {
			error_reporting(E_ALL);
			$this = $GLOBALS["modules"]["parser_CGI"][0];
			$this->parser_open($args, $filename, $rq_err, $cgi_headers);
			return;
		}

		// create fifo
		unlink($this->pipe_file = tempnam($conf["global"]["tmpdir"][0], "nwlibphp"));
		posix_mkfifo($this->pipe_file, 0700);
		chmod($this->pipe_file, 0700);

		// create cgi process
		$pid = pcntl_fork();

		if ($pid < 0) {

			techo("mod_libphp: error forking CGI subprocess", NW_EL_ERROR);

		}
		elseif ($pid) {

			$this->pipe = fopen($this->pipe_file, NW_BSAFE_READ_OPEN);
			$headers = unserialize(base64_decode(fgets($this->pipe, 32768)));

			foreach (explode("\n", implode("\n", $headers)) as $h) {
				list($hd_key, $hd_val) = explode(":", rtrim($h, "\r"), 2);
				if (strlen($hd_val)) {
					$hd_val = ltrim($hd_val);
					$cgi_headers[$hd_key] = $hd_val;
				}
			}

		}
		else {

			// fake PHP4.2 environment
			$_SERVER = nw_server_vars();
			$_SERVER["GATEWAY_VERSION"] = "CGI/1.1";
			parse_str($_SERVER["QUERY_STRING"], $_GET);
			$_POST = $_FILES = $_COOKIE = $_SESSION = array();
			$_REQUEST = array_merge($_GET, $_POST, $_COOKIE);
			foreach($_SERVER as $en => $ev) {
				putenv("$en=$ev");
				$_ENV[$en] = $ev;
			}
			foreach(array_keys($GLOBALS) as $varname) {
				if ($varname[0] != "_") {
					unset($GLOBALS[$varname]);
				}
			}
			$GLOBALS["PHP_SELF"] = $_SERVER["PHP_SELF"] = $_SERVER["SCRIPT_NAME"];
			foreach (ini_get_all as $ini_setting => $ini_value) {
				ini_set($ini_setting, $ini_value);
			}

			// other preparations
			$out_contenttype = ($uu = get_cfg_var("default_mimetype")) ? $uu : "text/html";
			if ($out_contenttype == "text/html") {
				$add_contenttype .= '; charset="' . (($uu = get_cfg_var("default_charset")) ? $uu : "iso8859-1") . '"';
			}
			$__nw_libphp_headers = array(
				"Status: 200",
				"X-Powered-By: nanowebs mod_libphp",
				"Content-Type: " . $add_contenttype
			);
			$__nw_libphp_script = $filename;
			$NANOWEB = 1;

			// output fifo
			$fp = $__nw_libphp_pipe = fopen($this->pipe_file, NW_BSAFE_WRITE_OPEN);

			// fifo output handler
			ob_start("__nw_libphp_ob_pipe");
			register_shutdown_function("__nw_libphp_shutdown");


			#---------------- into nanoweb core --------------
			// if (isset($__nw_libphp_script)) { unset($lf); include($__nw_libphp_script); exit; }
			#-------------------------------------------------

		}

	}


	function parser_get_output() {
		if ($this->pipe) {
			return(fread($this->pipe, 32768));
		}
	}


	function parser_eof() {
		return(empty($this->pipe) || @feof($this->pipe));
	}


	function parser_close() {
		if ($this->pipe) fclose($this->pipe);
		if ($this->pipe_file) unlink($this->pipe_file);
	}


}




#== esoteric helper functions ===============================================


function __nw_libphp_ob_pipe($string) {
	if ($fp = $GLOBALS["__nw_libphp_pipe"]) { 
		if (!nwheaders_sent()) {
			$h = base64_encode(serialize($GLOBALS["__nw_libphp_headers"]));
			fputs($fp, ($h."\n"));
			$GLOBALS["__nw_libphp_headers_sent"] = true;
		}
		fputs($fp, $string);
	}
}


function __nw_libphp_shutdown() {
	ob_end_flush();
	fclose($GLOBALS["__nw_libphp_pipe"]);
}


function nwheader($header) {
	$GLOBALS["__nw_libphp_headers"][] = $header;
}


function nwheaders_sent() {
	return(isset($GLOBALS["__nw_libphp_headers_sent"]));
}


?>