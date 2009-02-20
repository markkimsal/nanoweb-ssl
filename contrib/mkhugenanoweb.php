#!/usr/local/bin/php -q
<?php

error_reporting(0);

$config = regex_getopts(
array(
     "help" => "/^-+h/i",
     "nwbin" => "/^-+n|-+b/",
     "config" => "/^-+c/",
     "modules" => "/^-+m/",
     "output" => "/^-+o/i",
     "auto" => "/^-+a/"
));

if (($argc < 2) || (@$config["help"])) {
	echo <<< HELP_END

This utility merges the nanoweb.php 2.1 »binary« with the pre-parsed
configuration data and all the activated modules into one big standalone
»binary« suitable (a bit faster) for inetd-mode operation.

mkhugenanoweb.php [--options...]

--nanoweb -n  /usr/sbin/nanoweb.php
--config  -c  /etc/nanoweb/nanoweb.conf
--auto    -a  will search for all the required files (nanoweb.php, config
              files and modules)
--output  -o  this parameter says where to write the resulting »large
              nanoweb.php binary« to, otherwise you should redirect stdout
--modules -m  is optional and gives the directory where all the modules
              are located
--help    -h  this help

In a hurry, just invoke mkhugenanoweb.php with the -a switch.


HELP_END
	;
}
else {
	#-- opts
	if (@$config["auto"]) {
		if (empty($config["nwbin"])) $config["nwbin"] = exec("which nanoweb.php");
		if (empty($config["config"])) $config["config"] = "/etc/nanoweb/nanoweb.conf";
		if (empty($config["output"])) $config["output"] = "large-nanoweb.php";
	}
	if (!is_dir(@$config["modules"])) $config["modules"]="";

	#-- read nanoweb binary
	if (!($f_nwbin = fopen($fn=$config["nwbin"], "r"))) die("could not open binary '$fn'!\n");
	$nwbin = fread($f_nwbin, 524288);
	fclose($f_nwbin);

 	#-- activate parts of the nanoweb binary
	$reduced = $nwbin;
	$p0 = strpos($nwbin, "<?");
	$p0 = strpos($nwbin, "\n", $p0) + 1;
	$p1 = strpos($nwbin, "set_time_limit(0);");
	$reduced = substr($nwbin, $p0, $p1 - $p0);
	eval($reduced);

	#-- let nanoparts parse the conffile
	$conffile = $config["config"];
	nanoweb_init($conffile);

	#-- build inline configuration code
	$php_conf = "";
	foreach ($conf as $cfsect=>$uu) {
		foreach ($conf[$cfsect] as $cfname=>$uu) {
			foreach($conf[$cfsect][$cfname] as $cfindex=>$cfvalue) {
				if (is_scalar($cfvalue)) {
					$cfvalue=addslashes($cfvalue);
					$php_conf .= "\$conf[\"$cfsect\"][\"$cfname\"][\"$cfindex\"]='$cfvalue';\n";
				}
			}
		}
	}
	foreach ($mime as $mi=>$mv) {
		$php_conf .= "\$mime[\"$mi\"]=\"$mv\";\n";
	}
	$php_conf .=
		'$themes=' . array_export($themes) . ";\n";
	$php_conf .=
		'$access_policy=' . array_export($access_policy) . ";\n";
	$php_conf .=
		'$posix_av=is_callable("posix_setuid");' . "\n".
		'$pcntl_av=is_callable("pcntl_fork");' . "\n".
		'$gz_av=is_callable("gzencode");' . "\n";
	$php_conf .= '$conf=cmdline_conf_upd($conf, $cmdline_conf_overrides, $cmdline_conf_adds);'."\n";
	$php_conf .= "\$conf[\"_complete\"]=true;\n";

	#-- copy modules` code
	$php_modules = "";
	if (! ($md = $config["modules"])) $md = $conf["global"]["modulesdir"][0];
	foreach ($conf["global"]["loadmodule"] as $mod) {
		$f = fopen($md."/".$mod, "r");
		$modc = fread($f, 262144);
		fclose($f);
		$modc = preg_replace('/^<[?].*?\n/', '', $modc);
		$modc = preg_replace('/[?]>.*?$/', '', $modc);
		$php_modules .= $modc;
	}
	$php_modules .= '$modules=load_modules($conf);' . "\n" .
			'modules_init();' . "\n";
	strip_comments($php_modules);

	#-- patch binary
	strip_comments($nwbin);
	$nwbin = str_replace('@include_once(', '#<uu># @include_once(', $nwbin);
	$nwbin = str_replace('$nload=(!class_exists(', '$nload=true; #<uu>#(!class_exists(', $nwbin);
	$nwbin = preg_replace('/\n(if [(]!is_readable[(][$]conf.{10,200}nanoweb_init[^\n]+\n)/ims', "\n/*  #<off>#\n\$1\n*/\n#<INSERT>#", $nwbin);
	$nwbin = str_replace("#<INSERT>#", "\n\n" .
		"#<nanoweb.conf>#\n" .
		$php_conf .
		"#</nanoweb.conf>#\n" .
		"\n" .
		"#<modules>#\n" .
		$php_modules .
		"#</modules>#\n" .
		"\n\n",
		$nwbin
	);


	#-- finally: new "huge" nanoweb.php binary to stdout
	if ($fn=$config["output"]) {
		$f=fopen($fn, "w");
		fwrite($f,$nwbin);
		fclose($f);
		echo "large nanoweb binary written to »{$fn}«\n";
	}
	else {
		echo $nwbin;
	}

}

  #-- we'll accept these commandline arguments: -----------------------
  $regex_options = array(
  );
  function regex_getopts($regexopts) {
     if (empty($_SERVER)) {
	$_SERVER = $GLOBALS["HTTP_SERVER_VARS"];
     }
     if (!empty($GLOBALS["argc"])) {
	$_SERVER["argc"] = $GLOBALS["argc"];
	$_SERVER["argv"] = $GLOBALS["argv"];
     }
     $opts = array();
     for ($n = 1; $n < $_SERVER["argc"]; $n++) {
        foreach ($regexopts as $opts_id => $optsregex) {
           if (preg_match($optsregex, $_SERVER["argv"][$n])) {
              $value = 1;
              if (($next = @$_SERVER['argv'][$n+1]) && ($next[0] != "-")) {
                 $value = $next;
                 $n++;
              }
              $opts[$opts_id] = $value;
              break;
           }
        }
     }
     return($opts);
  }




function strip_comments(&$s) {
	$s = preg_replace('#\n\s*[/][*]\s.+?[*][/]#ms', "\n", $s);
	$s = preg_replace('#\n\s*[/][/]\s[^\n]+?\n#ms', "\n", $s);
	$s = preg_replace('/\n\s*[#]\s[^\n]+?\n/ms', "\n", $s);
}

   function array_export($var, $indent = "", $output = "") {
      if (is_array($var)) {
         foreach ($var as $id => $next) {
            if ($output) $output .= ", ";
            else $output = "array(";
            $output .= "\"$id\"=>" .
               array_export($next, "");
         }
         if (empty($output)) $output = "array(";
         $output .= ")";
      }
      else {
         $output = "'" . preg_replace("/([\\\\\'])/", '\\\\$1', $var) . "'";
      }
      if ($indent == " ") $output .= ";";
      return($output);
   }


?>
