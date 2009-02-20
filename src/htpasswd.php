#!/usr/local/bin/php -q
<?php

/*

u can use this software instead of the apache htpasswd for create your .nwauth
code by myrdin
part of the nanoweb project (http://nanoweb.si.kz)

*/

define(VERSION, "0.2");
define(DEFAULT_AUTH_FILE, ".nwauth");

$display = false;

$cmdline_help[]="usage: ./htpasswd.php [options]";
$cmdline_help[]="htpasswd supported command line options :";
$cmdline_help[]="--help : this help screen";
$cmdline_help[]="--version : show version info";
$cmdline_help[]="--file=/path/to/the/file : path to the file to create or upgrade";
$cmdline_help[]="--create-file=\"box title\" : create a new file and give the name to the box";
$cmdline_help[]="--add-user=\"login passwd\" : add a new user to the file";
$cmdline_help[]="--display-result : don't put the result in a file, just display it to the screen";
$cmdline_help[]="--modify-user=\"login passwd\" : modify an existing user";
$cmdline_help[]="can be abreviated as -h, -v, -f, -c, -a -d and -m";

if (!$_SERVER["argc"]<1){
	foreach ($cmdline_help as $cs) echo($cs."\n");
}

if ($_SERVER["argc"]>1) for($a=1;$a<$_SERVER["argc"];$a++) {

	if (($a==1) && (substr($_SERVER["argv"][$a], 0, 1)!="-")) {

		$cmdline_cf=$_SERVER["argv"][1];
	
	} else {

		$ca=explode("=", $_SERVER["argv"][$a]);
		$ck=array_shift($ca);
		$cv=implode("=", $ca);

		switch($ck) {

			case "-h":
			case "--help":
			foreach ($cmdline_help as $cs) echo($cs."\n");
			exit;
			break;

			case "-v":
			case "--version":
			die(VERSION."\n");
			break;
			
			case "-d":
			case "--display-result":
			$display = true;
			break;

			case "-f":
			case "--file":
			$file = $cv;			
			break;

			case "-c":
			case "--create-file":
			$title=$cv."\n";
			create_file($title);
			break;
			
			case "-a":
			case "--add-user":
			$params=explode(" ",$cv);
			$login = $params[0];
			$passwd = $params[1];
			add_user($login,$passwd);
			break;
			
			case "-m":
			case "--modify-user":
			$params=explode(" ",$cv);
			$login = $params[0];
			$passwd = $params[1];
			modify_user($login,$passwd);
			break;
			
			default:
			echo "unknown argument : ".$_SERVER["argv"][$a].", try --help\n";
			break;

		}
	
	}
	
}

function gen_salt(){

	$random = 0;
	$rand64 = "";
	$salt = "";

	$random=rand();	

	$rand64= "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
	$salt=substr($rand64,$random  %  64,1).substr($rand64,($random/64) % 64,1);
	$salt=substr($salt,0,2); 

	return $salt;
}

function crypt_pass($pass,$salt){
	
	return crypt($pass,$salt);

}

function open_file($file,$arg){
	
	if (!$file){
		if (is_file(DEFAULT_AUTH_FILE)) $fp=fopen(DEFAULT_AUTH_FILE,'a'); 
		else $fp=fopen(DEFAULT_AUTH_FILE,'w');
	} else $fp=fopen($file,$arg);

	return $fp;

}

function create_file($str){
	
	if(!$str){
		echo "please, you have to set a realm name !\n";
		exit();
	}

	write_file($str,"w");

}

function add_user($login,$passwd){
	
	global $display;
	
	if(!$passwd){
		echo "please, you have to set a password !\n";
		exit();
	}

	$check = read_file("check");

	if ($check == true){
		echo "user already exist, use --modify-user instead\n";
		exit();
	}else{
		$salt = gen_salt();
		$crypt_pwd = crypt_pass($passwd, $salt);
		$str = $login.":".$crypt_pwd."\r\n";
		if($display == true) display_result($str); else write_file($str,"a");
	}
	
}

function write_file($str,$arg){

	global $file;

	$fp = open_file($file,$arg);			
	fputs($fp,$str);
	close_file($fp);

}

function close_file($fp){

	fclose($fp);

}

function display_result($str){

	echo $str;

}

function modify_user($login,$passwd){

	global $display;
	
	if(!$passwd){
		echo "please, you have to set a password !\n";
		exit();
	}

	$check = read_file("mod");

}

function read_file($action){

	global $file, $login, $passwd;
	
	$found = false;

	if (!$file) $file = DEFAULT_AUTH_FILE;
	$fp = fopen($file,"r");
	$contents = fread($fp, filesize($file));
	$tmp = explode("\n",$contents);
	
	for ($i=1;$i<count($tmp);$i++){
		
		$xp = explode(":",$tmp[$i]);
		
		if ($action == "check"){
			if (strcmp($xp[0],$login)==0){
				$found = true;
			}
		}else if ($action == "mod"){
			if (strcmp($xp[0],$login)==0){
				$salt = gen_salt();
				$crypt_pwd = crypt_pass($passwd, $salt);
				$tmp[$i] = $login.":".$crypt_pwd."\n";
			}
		}
	
		$str .= $tmp[$i];

	}
	
	if ($action == "mod") replace_pwd($str);
	close_file($fp);

	return $found;

}

function replace_pwd($str){

	global $file;

	$fp = open_file($file,"w");
	fputs ($fp,$str);
	close_file($fp);

}

?>
