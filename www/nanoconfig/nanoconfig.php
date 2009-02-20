<?php

   ###################################################################
   #
   #   nanoweb online configuration script R1.02b-2.1.2
   #   <mario&#40;erphesfurt·de>

   #   directives which are commented out in the distributed config
   #   files shouldn't be listed here, as they are set to 0 if one
   #   pushes [Save] without noticing this

   #<off>#   $FORCE_LANG = "en";

   #-- init vars
   $PHP_SELF = $HTTP_SERVER_VARS["SCRIPT_NAME"];
   $_REQUEST = array_merge($_REQUEST, $HTTP_GET_VARS, $HTTP_POST_VARS);
   $DOS_HOST = stristr(php_uname(), "Windows");
   #-- error_reporting(E_ALL);
   error_reporting(0);
   set_magic_quotes_runtime(0);

   #-- no running nanoweb -> php (only?)
   $page = @$_REQUEST["page"];
   if (empty($page)) {
      if (strstr(@$HTTP_SERVER_VARS["SERVER_SOFTWARE"], "nanoweb") === false) {
         $page = "PHP/PHP";
      }
      else {
         $page = "NW/nanoweb";
      }
   }
   @list($which, $config_page) = explode("/", $page, 2);

   $T = array(
      "NW" => array(
         "CONFIG_DIR" => search_dir(array("/etc/nanoweb", "/usr/etc/nanoweb", "/usr/local/etc/nanoweb", "C:/nanoweb", "C:/Program Files/nanoweb")),
         "CONFIG_FILE" => "nanoweb.conf",
         "DEFAULT_SECTION" => "global",
         "DOCDIR" => ($uu="/doc/nanoweb/html"),
         "DOC" => "$uu/index.html",
         "DOCREF" => "$uu/core.html#"
      ),
      "PHP" => array(
         "CONFIG_DIR" => search_dir(array(dirname(get_cfg_var("cfg_file_path")), "/etc/php4/cli", "/etc/php4/cgi", "/etc/php4/nanoweb", "/etc/php4/apache", "/etc/php/cgi", "/etc/php4", "/etc/php", "/usr/local/etc/php4", "C:/PHP", "C:/PHP4", "C:/Program Files/PHP")),
         "CONFIG_FILE" => "php.ini",
         "DEFAULT_SECTION" => "PHP",
         "DOC" => "http://www.php.net/manual/en/",
         "DOCREF" => "http://www.php.net/manual/en/configuration.php#ini."
      )
   );
   $DEFAULT_SECTION = $T[$which]["DEFAULT_SECTION"];

   # lang-specific output
   $D_BOOLEAN["boolean"] = array("No", "Yes");
   $D_BOOLEAN["boolvalue"] = array("False", "True");
   $D_BOOLEAN["boolpower"] = array("Off", "On");
   $D_BOOLEAN["boolstate"] = array("Disabled", "Enabled");



   #
   #  following arrays hold the structure of the configuration pages
   #

   $configuration_pages["NW"] = array(
	"nanoweb" => array(
            "html_empty" => ""
        ),
        "-General" => array(
            "ServerName" => "string",
            "ServerAlias" => "multiple",
            "ServerAdmin" => "string",
            "DocumentRoot",
            "DirectoryIndex",
            "DefaultContentType",
            "SingleProcessMode" => "boolean",
            "ServerMode" => "standalone|inetd|",
            "User" => "string",
            "Group" => "string",
        ),
        "-Technical" => array(
            "ListenInterface" => "string",
            "ListenPort" => "string",
            "ListenQueue" => "string",
            "KeepAlive" => "string",
            "RequestTimeout" => "string",
            "ChildLifeTime" => "string",
            "MaxServers" => "string",
            "TempDir" => "string",
            "StaticBufferSize" => "string"
        ),
        "-Access Control" => array(
            "ACPolicy" => "|allow|deny",
            "ACAllowIP" => "multiple",
            "ACAllowHost" => "multiple",
            "ACDenyIP" => "multiple",
            "ACDenyHost" => "multiple",
            "ACBlockError" => "string",
            "ACBlockMessage" => "string",
        ),
        "-.nwaccess" => array(
            "AccessFile" => "string",
            "AccessPolicy" => "override|merge|block",
            "AccessOverride" => "multiple",
            "AccessMerge" => "multiple",
            "AccessBlock" => "multiple"
        ),
        "-Mime Types" => array(
            "MimeTypes" => "string",
            "AddType" => "multiple",
            "DisableMimeMagic" => "boolvalue"
        ),
        "-Logging" => array(
            "Log" => "string",
            "LogDir" => "string",
            "ServerLog" => "multiple",
            "HostnameLookups" => "boolstate",
            "HostnameLookupsBy" => "server|logger|",
            "PidFile" => "string",
            "LogHitsToConsole" => "boolean",
            "LogHitsToServerLog" => "boolean"
        ),
	"-CGI Setup" => array(
            "ParseExt" => "multiple",
            "AllowPathInfo" => "boolean",
            "PathInfoTryExt" => "multiple",
            "CGIScriptsDir" => "multiple",
            "CGIScriptNoExec" => "|error|raw",
            "CGIFilterPathInfo" => "boolean",
            "FCGIFilterPathInfo" => "boolean"
        ),
        "-Security" => array(
            "AllowSymlinkTo" => "multiple",
            "IgnoreDotFiles" => "boolean"
        ),
        "-Miscellaneous" => array(
            "ConfigDir" => "string",
            "Alias" => "multiple",
            "UserDir" => "string",
            "ErrorDocument" => "multiple",
            "AddHeader" => "multiple",
            "AddServerVar" => "multiple",
            "ServerSignature" => "full|os|php|prod|min|off|fake",
            "ServerFakeSignature" => "string",
	/*
            "Include" => "multiple"
         */
        ),
	"-Themes" => array(
            "ServerTheme" => "string",
            "LoadTheme" => "multiple"
	),
        "Modules" => array(
            "LoadModule" => "multiple",
            "ModulesDir" => "string",
        ),
        "-FileBrowser" => array(
            "FileBrowser" => "boolstate",
            "FBSortOrder" => "name|size|date|name desc|size desc|date desc",
            "FBWelcomeFile" => "string",
            "FBDescFile" => "string",
            "FBIconDirectory" => "string",
            "FBIconByType" => "multiple",
            "FBIconDefault" => "string"
        ),
        "-Gzip Encoding" => array(
            "GzipEnable" => "boolean",
            "GzipLevel" => "string",
            "GzipMaxRatio" => "string"
        ),
        "-Authentication" => array(
            "html_hr1" => "<hr><br>",
            "AuthSimpleUserPass" => "multiple",
            "html_hr2" => "<br><hr><br>",
            "AuthNwauthFilename" => "string",
            "html_hr3" => "<br><hr><br>",
            "AuthHtpasswdFilename" => "string",
            "html_hr4" => "<br><hr><br>",
            "AuthMysqlHost" => "string",
            "AuthMysqlUser" => "string",
            "AuthMysqlPass" => "string",
            "AuthMysqlDB" => "string",
            "AuthMysqlTable" => "string",
            "AuthMysqlLoginColumn" => "string",
            "AuthMysqlPassColumn" => "string",
            "AuthMysqlPassType" => "plain|crypt|md5|mysql",
            "html_hr5" => "<br><hr><br>",
            "AuthPgsqlHost" => "string",
            "AuthPgsqlUser" => "string",
            "AuthPgsqlPass" => "string",
            "AuthPgsqlDB" => "string",
            "AuthPgsqlTable" => "string",
            "AuthPgsqlLoginColumn" => "string",
            "AuthPgsqlPassColumn" => "string",
            "AuthPgsqlPassType" => "plain|md5|",
            "html_hr6" => "<br><hr><br>",
            "AuthLDAPServer" => "string",
            "AuthLDAPBindDN" => "string",
            "AuthLDAPMatchfilter" => "string",
            "html_last_hr" => "<br><hr><br>"
        ),
        "-Mispell" => array(
            "MispellAction" => "|advice|redirect"
        ),
        "-MultiViews" => array(
            "LanguagePriority" => "string",
            "OtherPriority" => "multiple",
            "ReflectRewriting" => "boolean"
        ),
        "-Status" => array(
            "StatusAllowHost" => "multiple"
        ),
        "-StdoutLog" => array(
            "LogHitsToConsole" => "boolean",
            "LogHitsToServerLog" => "boolean"
        ),
        "-MySQL Logging" => array(
            "MySQLLogHost" => "string",
            "MySQLLogDatabase" => "string",
            "MySQLLogUser" => "string",
            "MySQLLogPassword" => "string"
        ),
        "-Proxy" => array(
            "ProxyAllowIP" => "multiple",
            "ProxyAccessLog" => "string",
            "ProxyCacheDir" => "string",
            "ProxyCacheMaxAge" => "string",
            "ProxyDenySite" => "string",
            "ProxyDenyPopup" => "string",
            "ProxyDenyPup" => "string"
        ),
        "-LoadLimit" => array(
            "LoadLimit" => "string",
            "LoadLimitError" => "string",
            "LoadLimitErrorMessage" => "string",
            "LoadLimitAction" => "|error|redir",
            "LoadLimitRedirect" => "string"
        ),
        "-Brainfuck" => array(
            "BSPAllowSource" => "boolean",
        ),
        "-DoS Evasive" => array(
            "DosEvasiveTimer" => "string",
            "DosEvasiveMaxReqs" => "string",
            "DosEvasiveError" => "string"
        ),
	"Virtual Hosts" => array(
            "html_empty" => ""
        )
   );


   $configuration_pages["PHP"] = array(
        "PHP" => array(
            "engine" => "magic",
            "expose_php" => "magic",
            "short_open_tag" => "auto",
            "asp_tags" => "auto",
            "default_mimetype" => "string",
            "default_charset" => "string"
        ),
        "-Files/Paths" => array(
            "include_path" => "string",
            "auto_prepend_file" => "string",
            "auto_append_file" => "string",
            "allow_url_fopen",
            "doc_root" => "string",
            "user_dir" => "string",
            "extension_dir" => "string"
        ),
        "-Variables" => array(
            "register_globals" => "auto",
            "variables_order" => "string",
            "register_argc_argv" => "auto",
            "precision" => "string",
            "magic_quotes_gpc" => "magic",
            "magic_quotes_runtime" => "magic",
            "magic_quotes_sybase" => "magic"
        ),
        "-Output" => array(
            "output_buffering" => "Off|On|1024|2048|4096|8192|16384|32768",
            "output_handler" => "string",
            "zlib.output_compression" => "boolpower",
            "implicit_flush" => "auto"
        ),
        "-Safe Mode" => array(
            "safe_mode" => "auto",
            "safe_mode_gid" => "auto",
            "safe_mode_include_dir" => "string",
            "safe_mode_exec_dir" => "string",
            "safe_mode_allowed_env_vars" => "string",
            "safe_mode_protected_env_vars" => "string",
            "disable_functions" => "string",
            "enable_dl" => "auto"
        ),
        "-Errors/Log" => array(
            "error_reporting" => "string",
            "display_errors" => "auto",
            "display_startup_errors" => "auto",
            "html_errors" => "auto",
            "log_errors" => "auto",
            "track_errors" => "auto",
            "error_log" => "string",
            "warn_plus_overloading" => "auto",
            "allow_call_time_pass_reference" => "auto"
        ),
        "-CGI" => array(
            "cgi.force_redirect" => "boolean",
            "cgi.fix_pathinfo" => "boolean",
            "cgi.rfc2616_headers" => "boolean"
        ),
        "-Misc" => array(
            "memory_limit" => "string",
            "max_execution_time" => "string",
            "post_max_size" => "string",
            "file_uploads",
            "upload_max_filesize",
            "from",
            "y2k_compliance"
        ),
        "Extensions" => array(
            "extension" => "multiple",
            "extension_dir" => "string"
        )
   );




####    ####   ###########   ####         ############
####    ####   ###########   ####         #############
####    ####   ####          ####         ####     ####
####    ####   ####          ####         ####     ####
############   ########      ####         #############
############   ########      ####         ############
####    ####   ####          ####         ####
####    ####   ####          ####         ####
####    ####   ###########   ##########   ####
####    ####   ###########   ##########   ####


   #-- load directive descriptions (in other languages)
   include("nanoconfig-en.php");
   if (empty($directive_descriptions)) {
      echo "ERROR: 'nanoconfig-en.php' not found!<br>\n";
   }

   if (($langs = @$FORCE_LANG) || ($langs = @$HTTP_SERVER_VARS["HTTP_ACCEPT_LANGUAGE"]) || ($langs = getenv("LANG"))) {

      $lpref = array();
      $qadj = 0.001;   #==> arsort() orders langs randomly if we don't help it
      foreach (explode(",", $langs) as $langq) {
         preg_match('/^\s*(\w\w).*?[\s;]+qs?=([.\d]+)/', $langq . "; q=1", $uu);
         $lpref[strtolower($uu[1])] = $uu[2] - ($qadj *= 1.17);
      }
      arsort($lpref);

      foreach ($lpref as $LANG => $uu) {

         if ($LANG == "en") { continue; }

         if (file_exists($lfile = "nanoconfig-$LANG.php")) {

            $old_desc = $directive_descriptions;

            include($lfile);

            foreach ($directive_descriptions as $where=>$uu) {
               $directive_descriptions[$where] = array_merge(
                  $old_desc[$where],
                  $directive_descriptions[$where]
               );
            }

            break;

         }
      }
   }





   # == ini files - read and write ========
   $directive = @$_REQUEST['directive'];  # new directive values
#-- echo "<pre>";print_r($directive);echo "</pre>";



   # =====================================================================
   # == lock action ============
   # =====================================================================

   if (@$_REQUEST["lock"]) {
      if (! $DOS_HOST) {
         $cdir = opendir($T[$which]["CONFIG_DIR"]);
         while ($cfile = readdir($cdir)) {
            if ($cfile[0] != ".") {
               chmod($T[$which]["CONFIG_DIR"] . "/" . $cfile, 0644);
               @$_MESSAGE .= "$cfile ";
            }
         }
         closedir($cdir);
         $_MESSAGE = "Set $_MESSAGE to not-writable.<br><br>";
      }
      else {
         $_MESSAGE = "Your operating system supports no file permissions, so the config files cannot be locked.<br><br>";
      }
   }



   # =====================================================================
   # == apply action =============
   # =====================================================================

   elseif (@$_REQUEST["apply"]) {
      if (! $DOS_HOST) {
         @exec("/usr/bin/killall -HUP nanoweb.php");
         $_MESSAGE = "You need to restart nanoweb for the changed configuration
                     to take effect.<br><br>";
      }
      else {
         #-- @exec("C:/nanoweb/nanoctl restart"); #-???-
      }
   }



     ##########      ###########   ####     ####   ##########
   ##############   ############   ####     ####   ##########
  #####      ####   #####   ####   ####     ####   ####
  #####             ####    ####   ####     ####   ####
   ######           ####    ####    ####   ####    ####
    #########       ####    ####    ####   ####    ########
      #########     ############    ####   ####    ########
          ######    ############     #### ####     ####
            #####   ####    ####     #### ####     ####
  ####      #####   ####    ####      #######      ####
  ##############    ####    ####       #####       ##########
    ##########      ####    ####       #####       ##########



   # =====================================================================
   # ==  read and update config files ====================================
   # =====================================================================

   # == strip empty "new" directives
   remove_empty_nodes($directive);

   $cf[$DEFAULT_SECTION]["include"] = array($T[$which]["CONFIG_DIR"] . "/" . $T[$which]["CONFIG_FILE"]);
   $cf_section = $DEFAULT_SECTION;
   $cf_already = array();

   function next_config_file() {
      global $cf_already, $cf, $cf_section, $DEFAULT_SECTION, $T, $which;
      foreach ($cf[$DEFAULT_SECTION]["include"] as $cf_file) {
         if (!strstr($cf_file,"/") && !strstr($cf_file,DIRECTORY_SEPARATOR)) {
            $cf_file = $T[$which]["CONFIG_DIR"] . "/" . $cf_file;
         }
         if (empty($cf_already[$cf_file])) {
            $cf_already[$cf_file] = 1;
            return($cf_file);
         }
      }
   }

   function search_dir($dirs) {
      foreach ($dirs as $d) {
         if (file_exists($d) && is_dir($d)) {
            return($d);
         }
      }
      return($dirs[0]);
   }

   function add_missing_directives($last_cf_directive = "[]") {
      global $cf_section, $directive, $new, $cf;
      if (isset($directive[$cf_section]))
      foreach ($directive[$cf_section] as $dname => $darray) {
         if (($last_cf_directive === "[]") ||
             (strtolower($dname) == $last_cf_directive)) {
            #--echo "adding lost directive [$cf_section] $dname / '$last_cf_directive'<br>";

            foreach($darray as $di => $dval) {
               $new[] = "{$dname} = {$dval}\n";
               @$cf[$cf_section][strtolower($dname)][] = $dval;
            }
            unset($directive[$cf_section][$dname]);
         }
      }
   }

   function remove_empty_nodes(& $array) {
      if (! empty($array))
      foreach ($array as $id => $sub) {
         if (is_string($sub) && (empty($sub)) && ($sub !== "0")) {
            unset($array[$id]);
         }
         elseif (is_array($sub)) {
            remove_empty_nodes($array[$id]);
         }
      }
   }


   /* do once */ { 
      $cf_already = array();  # reread from first configuration file
      $cf_section = "global";

      while ($cf_file = next_config_file())

      if ($old_configuration_file = @file($cf_file)) {

         $new = array();

         $last_cf_directive = "#";

         foreach ($old_configuration_file as $line) {

            if (preg_match('/^\s*(\w[-_.:\w\d]*)\s*=?(.*?)$/', $line, $uu)) {
               $cf_directive = strtolower(trim($uu[1]));
               $cf_args = trim($uu[2]);

               #-- append new directive not found in current config file
               if ($cf_directive != $last_cf_directive) {
                  add_missing_directives($last_cf_directive);
                  $last_cf_directive = $cf_directive;
               }
   
               #-- replace config files` lines with submitted values
               if (isset($directive[$cf_section]))
               foreach ($directive[$cf_section] as $dname => $darray) {

                  if (strtolower($dname) == $cf_directive) {

                     $act_divs = count(@$cf[$cf_section][$cf_directive]);

                     $dval = @$directive[$cf_section][$dname][$act_divs];
                     $line = ($dname . " = " . $dval . "\n");
                     $cf_args = $dval;

                     unset($directive[$cf_section][$dname][$act_divs]);
                     remove_empty_nodes($directive[$cf_section]);

                     #-- echo "last] $cf_section :: $cf_directive [$act_divs] ?= $dval<br>";
                  }
               }

               #-- save values in config array
               $cf[$cf_section][$cf_directive] = @array_merge(
                  $cf[$cf_section][$cf_directive],
                  array( $cf_args )
               );

            }
            else {
               if (preg_match('/^\s*\[([-_. \w\d]+)\]\s*$/', $line, $uu)) {
                  if ($cf_section != $uu[1]) {
#--echo "add missing directives of section $cf_section<br>";
                     add_missing_directives();
                  }
                  $cf_section = $uu[1];
               }
               else {
                  if (trim($line) == "") {
#--echo "add missing directives before blank line<br>";
                     add_missing_directives($last_cf_directive);
                  }
               }
            }
   
            $new[] = $line;

         }

         #-- new vhost section
         if (($add_vhost = @$_REQUEST["add_vhost"]) && ($which == "NW")) {
            if (strpos($cf_file, "vhosts") >= 1) {

               $docroot = @$HTTP_SERVER_VARS["DOCUMENT_ROOT"];
               $docroot = getcwd();
               
               $new[] = "\n[" . $add_vhost . "]\n" .
                 "DocumentRoot = " . $docroot .
		 "\n[/" . $add_vhost . "]\n\n";

               $cf[$add_vhost]["documentroot"][0] = $docroot;

               $config_page = "-" . $add_vhost;

            }
         }

         #-- save
         if ((@$_REQUEST["save"])) {
            if (is_writeable($cf_file) && ($f_cf = fopen($cf_file, "w"))) {
echo "writing $cf_file...";
               fwrite($f_cf, implode("", $new));
               fclose($f_cf);
#-$o .= implode("", $new);
            }
            else {
               @$_MESSAGE .= "<font color=\"#771122\">'$cf_file' is not writeable!</font><br><br>";
            }
         }
      }

      add_missing_directives();
   }




############   ####    ####   ############
####    ####   ####    ####   ############
####    ####   ####    ####       ####
####    ####   ####    ####       ####
####    ####   ####    ####       ####
####    ####   ####    ####       ####
####    ####   ####    ####       ####
####    ####   ####    ####       ####
############   ############       ####


 
   # -- add [vhosts] (temporarily) as configuration pages ---------------
   $configuration_pages["ADD"] = array();
   foreach ($cf as $section => $divs) {
      if ($section != $DEFAULT_SECTION) {
         $configuration_pages["ADD"]["-$section"] = $configuration_pages_add_section[$which];
         foreach ($divs as $dname => $dcont) {
            if (! preg_match("/^log|servername|serveralias|documentroot$/i", $dname)) {
               $configuration_pages["ADD"]["-$section"][$dname] = "magic";
            }
         }
      }
   }

   #-- which pages to list
   if (file_exists($T["NW"]["CONFIG_DIR"])) {
      $page_order = array("NW");
   }
   switch ($which) {
      case "PHP":
          $page_order[] = "PHP";
          $page_order[] = "ADD";
          break;
      case "NW":
          $page_order[] = "ADD";
          $page_order[] = "PHP";
          break;
      default:     
          $page_order[] = "PHP";
          $page_order[] = "$which";
          break;
   }



#-- echo "<pre>"; print_r($directive); #print_r($cf); print_r($configuration_pages); echo "</pre>";



?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title>nanoweb online configuration</title>
  <style type="text/css"><!--
     body { font-family:Verdana,Times,"Times New","Times New Roman"; }
     .box { border:1px solid #777777; }
     a { text-decoration:none;color:#3333cc; }
     a:hover { color:#cc3333; }
     input.save { background-color:#aaaaee; }
  //--></style>
</head>
<body bgcolor="#ffffff">
  <table width="680" bgcolor="#dddddd" border="0" cellpadding="5" cellspacing="10" class="box" style="border-style:outset" summary="list of pages on the left side, actual configuration directives on the right"><colgroup width="680"><col width="160"><col width="520"></colgroup>
  <tr><td colspan="2" bgcolor="#6666bb" class="box" valign="middle" align="center"><b><big>nanoweb online configuration</big></b></td></tr>
  <tr>
  <td bgcolor="#eeeeee" class="box" style="border-style:inset" valign="top" align="left" width="160">
     <?php



      #-- list config pages
      $combined_pages = array();
      foreach ($page_order as $sub) {

         foreach ($configuration_pages[$sub] as $id => $uu) {

            if ($sub == "ADD") { $sub = $which; }

            $title = $directive_descriptions[$sub]["%$id"]
            or
            $title = $id;

            if ($title[0] == "-") {
               $title = '&middot;&nbsp;' . ltrim($title, "-");
            } else {
               $title = "<b>$title</b>";
            }

            echo '<div' . ($id == $config_page ? ' style="background-color:#ccccee" bgcolor="#ccccee"' : '') . '>';
            echo '<a href="' . $PHP_SELF . '?page=' . $sub . "/" .
                 urlencode($id) . '">' . $title . "</a>";
            echo "</div>\n";
         }

      }



     ?>
  </td>
  <td bgcolor="#eeeeee" class="box" style="border-style:inset" valign="top" align="left" width="520">
     <?php

         # -- [global] or [vhost]
         $cf_section = $DEFAULT_SECTION;

         if (@$configuration_pages["ADD"][$config_page]) {
            # -- not neccessary anymore because of "ADD":  &&is_array($cf[ltrim($config_page, "-")])) {
            $cf_section = ltrim($config_page, "-");
         }


         #-- info msg from init
         echo @$_MESSAGE;
         #-- echo "<pre>";print_r($cf);echo "</pre>";


         #-- output config page
         if (!($def = @$configuration_pages[$which][$config_page])
            && !($def = @$configuration_pages["ADD"][$config_page])) {
            echo "internal script error<br>";
         }
         else {

            #-- submit form
            echo "<form action=\"$PHP_SELF\" method=\"POST\">\n";
            echo "   <input type=\"hidden\" name=\"page\" value=\"$which/$config_page\">\n";

            #-- page help
            $help = @$directive_descriptions[$which];
            if ($page_intro = @$help["@" . $config_page]) {
               echo $page_intro . "<br><br>\n";
            }

            #-- go thru directive names on this config page
            foreach ($def as $directive => $ddesc) {

               #-- super-simple auto directive names
               if (strlen($directive) == 1) {
                  $directive = $ddesc;
                  $ddesc = "magic";
               }

               #-- print help
               if ($dir_help = @$help[$directive]) {
                  echo $dir_help . "<br>\n";
               }

               #-- print html help text or directive name, link
               if ((strpos($directive, "html") === 0) && ($directive !== "html_errors")) {
                  echo $ddesc;
                  continue;
               }
               else {
                  $link = $T[$which]["DOCREF"] . $directive;
                  switch ($which) {
                     case "NW": break;
                     case "PHP": $link = $T[$which]["DOCREF"] . strtr($directive, "_", "-"); break;
                     default: $link = "http://www.google.com/search?q={$which}+{$directive}";
                  }
                  echo "<a href=\"$link\">$directive</a>:<br>";
               }


               #-- values array
               $value = @$cf[$cf_section][strtolower($directive)];

               #-- determine type for "auto"
               if (($ddesc == "auto") || ($ddesc == "magic") || ($ddesc == "*") || ($ddesc === "")) {
                  if (is_array($value) && (count($value) >= 2)) {
                     $ddesc == "multiple";
                  }
                  else {
                     foreach (array("On|Off", "true|false", "Yes|No", "1|0", "enabled|disabled") as $ddesc) {
                        if (preg_match("/^{$ddesc}$/i", @$value[0])) {
                           $ddesc .= "|";
                           break;
                        }
                        $ddesc = "string";
                     }
                     if ($ddesc == "1|0|") { $ddesc = "boolean"; }
                  }
               }


               #-- first value (from array)
               $value = htmlentities(@$value[0]);

               #-- print input boxes depending on type
               if (($D_BOOLEAN[$ddesc]) || (strpos($ddesc, "|") !== false)) {

                  #-- yes/no selection
                  echo "   <select name=\"directive[$cf_section][$directive][0]\">";
                  $options = explode("|", $ddesc);
                  if (count($options) >= 3) {
                     foreach ($options as $opt) {
                        if ($opt !== "") {
                           echo '<option value="' . $opt . '"' .
                              ((strtolower($value) == strtolower($opt)) ? " selected" : "") .
                              '>' . $opt . '</option>';
                        }
                     }
                  }
                  else {
                     list($no, $yes) = ($uu = $D_BOOLEAN[$ddesc]) ? $uu : array("Yes", "No");

                     if (count($options) >= 2) {
                        list($no, $yes) = $options;
                     }
                     echo '<option value="0">' . $no . '</option>';
                     echo '<option value="1"' . ($value ? " selected" : "") . '>' . $yes . '</option>';
                  }
                  echo "</select>\n";

               }

               #-- input list
               elseif ($ddesc == "multiple") {

                  $index = -1;
                  if (count(@$cf[$cf_section][strtolower($directive)]))
                  foreach (@$cf[$cf_section][strtolower($directive)] as $index => $dval) {
                     $dval = htmlentities($dval);
                     echo "   <input size=\"42\" name=\"directive[$cf_section][$directive][$index]\" value=\"" . $dval . "\">";
                     echo "<input type=\"submit\" name=\"remove_directive[$cf_section][$directive][$index]\" value=\"Remove\"><br>\n";
                  }
                  $index++;
                  echo "   <input size=\"42\" name=\"directive[$cf_section][$directive][$index]\" value=\"\">";
                  echo "<input type=\"submit\" name=\"save\" value=\"Add\">\n";
               }

               #-- single field
               else {

                  #-- just a string input field
                  echo "   <input size=\"42\" name=\"directive[$cf_section][$directive][0]\" value=\"$value\">\n";
               }


               echo "   <br><br>\n";
            }

            #-- [save] button
            echo "   <input class=\"save\" type=\"submit\" name=\"save\" value=\"Save Changes\">\n";
            echo "   </form>\n";

         }

     ?>
  </tr></table>
</body>
</html>
