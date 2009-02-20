<?php

$NANOWEB_BINARY = "/usr/sbin/nanoweb.php";
$FAST_INLINE_NANOWEB = 1;
$PHP_BINARY = "/usr/local/bin/php";

$TEMP_DIR = "/tmp";
$nww_apache_headers = 1;

/*

  this is a wrapper script to run Nanoweb below Apache

  - it is recommended to use a "big" nanoweb binary, with the configuration
    and modules already inside (use the "mkhugenanoweb.php" utility)
  - the PHP binary is only required when invoking nanoweb.php via the slower
    (but more stable) exec() syscall, when FAST_INLINE_NANOWEB is set to 0

  #-- For apache activate this wrapper in .htaccess as follows:
    RewriteEngine On
    RewriteRule .* /nanoweb/cgi-nanoweb-wrapper.php [L]

  #-- good alternative (if your evil provider disabled Apache's mod_rewrite)
    ErrorDocument 404 /nanoweb/cgi-nanoweb-wrapper.php

*/



#-- init
error_reporting(0x0000);

#-- save http environment
$nww_server_docroot = $_SERVER["DOCUMENT_ROOT"];
$nww_server_hostname = $_SERVER["SERVER_NAME"];
$nww_server_addr = $_SERVER["SERVER_ADDR"];
$nww_server_admin = $_SERVER["SERVER_ADMIN"];
$nww_request_method = $_SERVER["REQUEST_METHOD"];
$qs = ($qs = $_SERVER["QUERY_STRING"]) ? '?'.$qs : '';
($nww_request_string = $_SERVER["REDIRECT_REQUEST_URI"].$qs) or
($nww_request_string = $_SERVER["REQUEST_URI"].$qs);
$nww_request_protocol = $_SERVER["SERVER_PROTOCOL"];
$nww_request_body = $_SERVER["CONTENT_LENGTH"];
if ($_ENV["CGI_NANOWEB_WRAPPER"]) { ex("HTTP/1.1 500 Subserver loop detected"); } else { putenv("CGI_NANOWEB_WRAPPER=1"); }
$nww_nph = strpos($_SERVER["SCRIPT_NAME"], "/nph-")!==false;

#-- reconstructing HTTP request
$nww_request_head = $nww_request_method." ".$nww_request_string." ".$nww_request_protocol."\n";
if (function_exists("getallheaders")) {
	foreach (getallheaders() as $name=>$value) {
		$nww_request_head .= $name.": ".$value."\n";
	}
}
else {
	if (empty($_SERVER["HTTP_HOST"])) {
		 $nww_request_head .= "Host: ".$nww_server_hostname."\n";
	}
	foreach ($_SERVER as $id=>$value) {
		if ((strpos($id,"HTTP_") === 0) && (strpos($value,"\n")===false)) {
			$name = substr($id, 5);
			$name = str_replace("_", " ", $name);
			$name = strtolower($name);
			$name = ucwords($name);
			$name = str_replace(" ", "-", $name);
			$nww_request_head .= $name.": ".$value."\n";
		}
	}
	if (!empty($nww_request_body)) {
		$nww_request_head .= "Content-Length: ".$_SERVER["CONTENT_LENGTH"]."\n";
		$nww_request_head .= "Content-Type: ".$_SERVER["CONTENT_TYPE"]."\n";
	}
}
$nww_request_head .= "\n";

#-- fetch POST/PUT requests` body
$nww_request_body_data = "";
if (strlen($nww_request_body)) {
	if (!empty($nww_request_body_data)) {
		#-- any other ideas?
	}
	#-- requires 4.3.0
	elseif ($fp = fopen("php://input", "r")) {
		$nww_request_body_data = fread($fp, 0x0100000);
		fclose($fp);
	}
	#-- probably guessworking
	elseif ((1+php_ini_set("always_populate_raw_post_data", "1")) && isset($HTTP_RAW_POST_DATA)) {
		$nww_request_body_data = $HTTP_RAW_POST_DATA;
	}
	#-- outch
	elseif (strpos($_SERVER["CONTENT_TYPE"],"form-data")) {
		#-- ok, let's reconstruct it
		$boundary = $_SERVER["CONTENT_TYPE"];
		$boundary = substr($boundary, strpos("boundary=",$boundary)+9);
		$boundary = trim($boundary, '"');
		$nww_request_body_data .= "--$boundary";
		foreach ($_POST as $name=>$value) {
			$nww_request_body_data .= "\n";
			$nww_request_body_data .= "Content-Disposition: form-data; name=\"$name\"\n";
			$nww_request_body_data .= "\n$value\n";
			$nww_request_body_data .= "--$boundary";
		}
		foreach ($_FILES as $name => $fa) {
			$nww_request_body_data .= "\n";
			$nww_request_body_data .= "Content-Disposition: form-data; name=\"$name\" filename=\"".$fa["name"]."\"\n";
			$nww_request_body_data .= "Content-Type: ".$fa["type"]."\n";
			$value = "" . @implode("", @file($fa["tmp_name"]));
			$nww_request_body_data .= "\n$value\n";
			$nww_request_body_data .= "--$boundary";
		}
		$nww_request_body_data .= "--\n";
	}
	elseif (strpos($_SERVER["CONTENT_TYPE"],"www-url-encoded")) {
		foreach ($_POST as $name=>$value) {
			$nww_request_body_data .= (strlen($nww_request_body_data)?"&":"") .
				$name . "=" . url_encode($value);
		}
	}
	else {
		ex("HTTP/1.1 500 Request Body Unavailable For Subserver");
	}
}


#-- setup nanoweb.php inetd environment
putenv("INETD_REMOTE_IP=".$_SERVER["REMOTE_ADDR"]);
putenv("INETD_REMOTE_PORT=".$_SERVER["REMOTE_PORT"]);
foreach ($_SERVER as $var=>$uu) {
	putenv($var);
}

#-- prepare nanoweb.php cmdline args:
$_SERVER["argv"] = array(
	$NANOWEB_BINARY,
	"--quiet",
	"--set-option=SERVERMODE=inetd",
	"--set-option=SINGLEPROCESSMODE=1",
	"--set-option=MAXSERVERS=1",
	"--set-option=LOGGERPROCESS=0",
	"--set-option=KEEPALIVE=",
	"--set-option=LOGTOCONSOLE=0",
	"--set-option=PIDFILE=",
	"--set-option='SERVERNAME=$nww_server_hostname'",
	"--set-option='DOCUMENTROOT=$nww_server_docroot'",
	"--set-option='SERVERADMIN=$nww_server_admin'",
	"--set-option='LISTENINTERFACE=$nww_server_addr'",
   //	"--add-option='ADDHEADER=X-Subserver-Wrapper: cgi-nanoweb/2.0.1-dev'",
   //	"--set-option=ACCESSFILE=.nwaccess",
);
$_SERVER["argc"] = count($_SERVER["argv"]);

#-- "start" nanoweb.php, using eval()
if ($FAST_INLINE_NANOWEB) {

	#-- define dummy socket ext functions 
	if (!function_exists("socket_create")) {
		function socket_create($d,$t,$p) { return(false); }
		function socket_shutdown($s,$h=0) { return(false); }
		function socket_close($s) { return(false); }
	}

	#-- read nanoweb.php »binary«
	if ($fp = fopen($NANOWEB_BINARY, "r")) {
		$nwbin = fread($fp, 0x0100000);
		fclose($fp);
	}
	if (empty($nwbin)) {
		ex("HTTP/1.1 500 Subserver Misconfiguration");
	}

	#-- patches:
	$REPLACEMENTS = array(
		# - - - - - - - -
		'$buf=read_request('
		=> '$buf=$nww_request_head;
			$dp=strpos($buf,"\n\n");
			$pn=2;
			#<off># $buf=read_request(',
		# - - - - - - - -
		'$buf.=read_request('
		=> '$bug.=$nww_request_body_data;   #<off># $buf.=read_request(',
		# - - - - - - - -
		'$hbuf=build_response_headers()'
		=> '$hbuf="";
							foreach (explode("\n",build_response_headers()) as $hline) { 
								if (!$nww_nph) if (strpos($hline,"Server:")===0) { $hline = "X-".$hline; }
								header($hline);
								if ($nww_apache_headers) if (strpos($line, "HTTP/")===0) { header("Status:".substr($line,strpos($line," "))); }
							}
							#<off># $hbuf=build_response_headers()'
		# - - - - - - - -
	);
	#-- apply
	foreach ($REPLACEMENTS as $orig => $new) {
		$nwbin = str_replace($orig, $new, $nwbin);
	}
	#-- strip first two lines, and the '?''>' at the bottom:
	list($uu, $uu, $nwbin) = explode("\n", $nwbin, 3);
	$nwbin = substr($nwbin, 0, strlen($nwbin) - 2);

	#-- remove ' from $argv
	foreach ($_SERVER["argv"] as $i=>$v) {
		$_SERVER["argv"][$i] = str_replace("'", "", $v);
	}

	#-- "execute" patched nanoweb.php
	eval($nwbin);

}

#-- this is the "SLOW" innovocation part, using exec()
else {

	if ($fp=fopen($tmp_request_file=$TEMP_DIR.DIRECTORY_SEPARATOR."cgi-nanoweb-wrapper-http-request.".base_convert(time(),10,36).".".getmypid(), "w")) {
		fwrite($fp, $nww_request_head.$nww_request_body_data);
		fclose($fp);
	}
	else {
		ex("HTTP/1.1 500 Subserver Wrapping File I/O Error");
	}

	$nww_pipe = popen(
		$PHP_BINARY .
//		" -c /home/www/www.example.com/nanoweb/my-php.ini " .
		" -f " .
		implode(" ", $_SERVER["argv"]) .
		" " .
		" < $tmp_request_file",
		"r"
	);

	if ($nww_pipe) {
		if (PHP_SAPI != "CLI") {
			while (!feof($nww_pipe) && ($line=trim(fgets($nww_pipe)))) {
				if (!$nww_nph) if (strpos($line, "Server: ")===0) { $line = "X-".$line; }
				header($line);
				if ($nww_apache_headers) if (strpos($line, "HTTP/")===0) { header("Status:".substr($line, strpos($line, " "))); }
				if (empty($line)) break;
			}
		}
		while (!feof($nww_pipe)) {
			echo(fread($nww_pipe, 1024));
		}
		pclose($nww_pipe);
	}

	unlink($tmp_request_file);

}

function ex($msg) {
	@header($msg);
	die($msg);
}

?>