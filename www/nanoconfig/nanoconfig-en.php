<?php

   # help text for all configuration directives in English
   # this file is the fallback and thus always required!

   $directive_descriptions["NW"] = array(
	"@nanoweb" => '<img src="../nanoweblogo.gif" width="200" height="60" align="right" alt="nanoweb logo" valign="top" border="1"> '
                    ."This tool is intended for quick initial setup of nanoweb.
                      It is often much easier and faster to edit the
                      well documented configuration files and it is in
                      fact impossible to configure each and every option
                      through this interface, so an editor is necessary for
                      fine-tuning.
                      <br><br> In order to be able to use this tool you must
                      make the config files writeable on Linux/UNIX
                      machines (not Windows):<br><TT>chmod a+rw
                      {$T[$which]['CONFIG_FILE']}</TT><br><br>This can be revoked by
                      pushing 'Lock Config' on this page after you have
                      finished setup.<br><br>You need to select [Save] on
                      every page if you did changes to it.<br><br><input
                      type=\"submit\" name=\"lock\" value=\"Lock Config\">
                      <input type=\"submit\" name=\"apply\"
                      value=\"Apply Config\">\n",
	"@-General" => "The main server needs a default DNS name which gets
                      send back if a client doesn't know it yet. An alias
                      name may contain asterisks. You cannot just invent
                      names here without telling your operating system (see
                      /etc/hosts or C:\winnt\hosts).",
        "@-.nwaccess" => "The name of the per-directory
                      configuration files is not hardcoded, it's up to your
                      choice how they are called.",
        "@-Logging" => "nanoweb supports various ways to log nearly all
                      activities. If you want to enable logging to a MySQL
                      database, please refer to the manual on how to setup
                      the according extension module.",
        "@Modules" => "Following extension modules are currently set to be
                      loaded on nanoweb startup. Note that entries, which
                      are commented out, don't get shown here (yet).
                      Have a look in the manual for an overview of all
                      <a href=\"{$T["NW"]["DOCDIR"]}/modules.html\">available
                      modules</a>.",
        "@-Authentication" => 'There are currently 6 different authentication
                      modules available for nanoweb, which can be activated
                      with the <A HREF="'.$T["NW"]["DOCREF"].'#authrequire">AuthRequire</A>
                      directive.',
        "@-Multiviews" => "The multiviews module extends nanoweb with
                      transparent content negotiation (this means serving
                      a file out of a bunch of variants that best matches
                      the clients preferences).<br>It partially helps with
                      RVSA/1.0.",
        "@-Status" => "mod_status allows you to view some <a
                 href=\"/server-status?detailed\">informations about
                 the running server</a> online.<br>
                 Please refer to the <a href=\"{$T["NW"]["DOC"]}\">manual</a> for
                 the available info pages.",
        "@-StdoutLog" => "The module mod_stdlog prints the log to the console
                      nanoweb.php was run from. This only works if you
                      didn't used nanoctl to start the server.",
        "@-MySQL Logging" => "mod_mysqllog writes the server log into a database
                 (instead of to some file, which the standard logging
                 functions do).<br>
                 The according table is automagically created if everything
                 is set up correctly:",
        "@-Proxy" => "mod_proxy is an internal proxy for nanoweb. Proxy
                 servers are often used to be able to overcross a firewall,
                 the nanoweb proxy is primarily a caching one.",
        "@-LoadLimit" => "mod_load_limit stops nanoweb from further serving pages
                      if the server gets too slow.",
        "@-Brainfuck" => "Brainfuck server pages (.bsp) module.<br>
                          Please refer to the manual, READMEs and Google for
                          further informations.",
        "@-DoS Evasive" => "This module prevents your server from going down
                      due to »Denial of Service« attacks.",
        "@Virtual Hosts" => "A »virtual host« always has a server name and a docroot
                      different from the main server. Many of the directives
                      that can be used within the main server configuration
                      section are allowed for virtual hosts too.<br><br>" .
                      'DNS name for the new virtual host:<br><input size="42" name="add_vhost"> <input type="submit" value="Add" name="save"> ',
	"DocumentRoot" => "The document root is the base directory of
                      all files which are available (via http://) through
                      the nanoweb server:",
        "DirectoryIndex" => "The file that gets delivered in favour
                 of a directory listing can be set using the DirectoryIndex
                 directive. Multiple file names may be given here separated
                 by spaces:",
        "DefaultContentType" => "When nanoweb cannot determine which
                 type a file is of, it will use following value to tell
                 the client (browser):",
        "SingleProcessMode" => "Because Windows&trade; doesn't
                 support process forking like UNIX, nanoweb must run on this
                 platform in the somewhat slower ",
        "ServerMode" => "You're strongly encouraged to have a look into
                 the READMEs before changing the ",
        "User" => "If you don't want nanoweb to run with superuser
                        privileges keep the default »www-data« as ",
        "ListenInterface" => "nanoweb needs to bind to at least one IP address
                      and a TCP port (80 is the default for webservers) to
                      listen for incoming connections.",
        "ListenQueue" => "A number of incoming requests can be
                 delayed (until they are processed) depending on the size
                 of the ",
        "KeepAlive" => "Modern HTTP clients can request different
                 files without opening that much TCP/IP connections. This
                 feature can be disabled by setting this directive to 0:",
        "RequestTimeout" => "Some browsers crash from time to time and
                 thus cannot complete their requests after they already
                 established and prepared a connection. To prevent
                 nanoweb from waiting neverendless for unfinished requests
                 this directive sets a timeout for these cases:",
        "ChildLifeTime" => "From time to time child processes need to
                 be restarted because they may already be trapped in some
                 neverending loop. Give an amount of seconds after which
                 every child shall be recreated.",
        "MaxServers" => "Limits the number of child servers Nanoweb may create.",
        "TempDir" => "Set this to where nanoweb is allowed to saves some files temporarily:",
        "StaticBufferSize" => "mod_static handles all plain files, but loads
                 big files into memory for faster output until following limit:",
        "ACPolicy" => "Nanoweb per default accepts requests from any
                 host, but it can also be configured to allow only a few
                 ones to access the server content.",
        "ACAllowIP" => "Hosts that should be granted access regardless
                 of the default setting above may be given with names or IP numbers
                 containing wildcards.",
        "ACDenyIP" => "If ACPolicy defaults to 'allow', you can
                 still prevent some hosts from accessing your server:",
        "ACBlockError" => "You can customize the error response for blocked hosts.",
        "AccessFile" => "If you want to reuse the per-directory
                      configuration files of apache for example you may
                      want to change this to »<b>.htaccess</b>«:",
        "AccessPolicy" => "Per default config settings of per-directory
                      configuration files override the values from the main server configuration;
                      but this behaviour can be changed:",
        "AccessOverride" => "You can set the desired behaviour for
                      individual directives independently from the default
                      configuration settings overriding policy.",
        "MimeTypes" => "nanoweb initially fetches it's mime
             types from the system wide mime registry file found on all
             recent Linux systems, so there's actually no need to care about
             file extension mappings.",
        "DisableMimeMagic" => "If a files mime type cannot be determined by
             its extension nanoweb will let PHP (CVS or version 4.3 or later)
             guess the mime type by using the 'magic data' file. If you fear
             this is too slow (usually not the case) you could disable this.",
        "ServerLog" => "Nanoweb can create multiple ServerLogs. The second
                 argument to the filename tells which message class should
                 be written to the specified log. See the manual for a list
                 of server message types.",
        "Log" => "Every virtual host (and so the main server) can log the
                 access hits to a separate file.",
        "LogDir" => "Set a default directory for log files, so you can
                 shorten the file path within the Log and ServerLog directives.",
        "HostnameLookups" => "You may want to keep the DNS lookups
                 (for hostnames instead of IP addresses in server logs)
                 disabled, as this slows down nanoweb a bit:",
        "HostnameLookupsBy" => "The actual lookup can be delayed, as
                 the DNS query is only required for the server logs; select
                 »logger« to speed up the main server / delivery:",
        "PidFile" => "The PidFile holds the »process id« on Linux/UNIX
                 machines, which eases killing the server for nanoctl.",
        "LoggerProcess" => "LoggerProcess, LoggerUser/Group can only be set
                        in the config file.",
        "LogHitsToConsole" => "If you want the log to be printed to the
                 console (window) the module
                <A HREF=\"".$T["NW"]["DOC"]."/mod_stdlog.html\">mod_stdlog</A>
                must be loaded.",
        "ParseExt" => "The ParseExt directive
                      defines which CGI interpreter shall be invoked for
                      which file extension.",
        "AllowPathInfo" => "The pathinfo is an additional information holder
                  besides the query string (GET) or the POST variables.
                  It is often used in favour of GET variables
                  because urls like \"script.php?a=x&b=1&cd=234\" frighten nearly
                  all search engines, while the pathinfo is harmless.",
        "PathInfoTryExt" => "You probably want to note every CGI
                      extension in here too, as this enables you to leave
                      out extensions when referring to a CGI with an incomplete
                      URI and without the need for mod_multiviews:",
        "CGIScriptsDir" => "Files in a (/cgi-bin/) directory specified here
                   are treaten as CGIs if they have the executeable flag
                   set (without dependency on file extension). If you just
                   set this to <b>/</b> your exec-flag CGIs may be located
                   anywhere.",
        "CGIScriptNoExec" => "If one of the scripts in your /cgi-bin/
                   has the executable flag not set, the server should
                   normally send an error response to the requesing client
                   (error), but may otherwise deliver this one as ordinary
                   file (raw).",
        "CGIFilterPathInfo" => "The PHP interpreter still has a bug, which
                     makes it trash the \$PHP_SELF variable if one
                     passes a \$PATH_INFO. If this directive is enabled
                     you won't be able to use PATH_INFO, if disabled you
                     still can use SCRIPT_NAME in favour of PHP_SELF:",
        "ConfigDir" => "Specify the directory where all the configuration
                  files of nanoweb and the theme files are kept:",
        "AllowSymlinkTo" => "Webservers only should allow access to files
                  below the docroot (or the docroot of a virtual hosts). You
                  may however allow nanoweb to serve files if you list the
                  symlinks destination directory here:",
        "IgnoreDotFiles" => "Files whose name starts with a dot are
                  treaten invisible by most UNIX applications; nanoweb for
                  example keeps per-directory configuration and password data
                  in files of this kind. You probably don't want anybody to
                  be able to retrieve such files:",
        "Alias" => "Use the Alias directive to assign virtual
                  directory names to absolute paths. These aliased directories
                  can be accessed from any virtual host independent from
                  any following configuration directives:",
        "ErrorDocument" => "A custom info page may be shown
                  instead of nanoweb's built-in error messages:",
        "AddHeader" => "This directive allows you to send arbitrary
                  HTTP headers to the client:",
        "UserDir" => "User directories are accessible via
                  <b>http://server/~user</b> if the user created following
                  subdirectory in his homedir.",
        "ServerSignature" => "You can control how much information
                  about your server Nanoweb reports to clients, you could even report your
                  server software with a faked free form string (useful for security reasons).",
        "Include" => "The Include directive allows to split the configuration
                      into different files; <tt>nanoweb.conf</tt>,
                      <tt>modules.conf</tt> and <tt>vhosts.conf</tt> are the
                      default ones, and you probably don't want much more
                      of these!",
        "ModulesDir" => "You don't need of course to give full path
                      names for every module, if you specified a default
                         directory for them:",
        "GzipEnable" => "Nearly all HTTP transfers can be accelerated if the
                      requested content is encoded using the internet
                      standard compression method »gzip« (also known as
                      »zlib format«) as all recent browsers support
                      transparent compression. This speeds up the transfer
                      as there is usually less packet loss due to usually
                      less tcp/ip packages, and most important: many users
                      still use slow modem dialup connections.",
        "GzipLevel" => "You can force the compression library to spend
                      more time on getting better results with a value of 9,
                      but of course as speed is often more important, you
                      should set this to some smaller value (5 is default):",
        "GzipMaxRatio" => "Only transfer compressed if it doesn't seems to be
                     already compressed:",
        "FileBrowser" => "The filebrowser extension module generates directory
                      listings where no default file (see DirectoryIndex)
                      is available. The output may be tweaked by various
                      options:",
        "FBIconDirectory" => "The /icons/ directory is an aliased one which can be reached from every vhost,
                  that way it is much easier for mod_filebrowser to map icons to file types:",
        "AuthSimpleUserPass" => "mod_auth_<b>simple</b> allows
                      you to specify all usernames and passwords within one
                      of the configuration files with just this
                      directive (enter login and password seperated with a
                      space here):<br>",
        "AuthNwauthFilename" => "mod_auth_<b>nwauth</b> keeps
                      the login/password pairs in the nanoweb authentication
                      files:<br>",
        "AuthHtpasswdFilename" => "mod_auth_<b>htpasswd</b> allows
                      you to use apache style authentication files:<br>",
        "AuthMysqlHost" => "with mod_auth_<b>mysql</b>
                      you can use login/password pairs from an already
                      existing MySQL user database:<br>",
        "AuthMysqlTable" => "Give here the table and column names that
                      contain the user authentication records; and specify
                      how the password field was encoded:<br>",
        "AuthPgsqlHost" => "with mod_auth_<b>pgsql</b>
                      you can use login/password pairs from an already
                      existing PostgreSQL database:<br>",
        "AuthLDAPServer" => "mod_auth_<b>ldap</b> fetches authentication
                      data via LDAP:<br>",
        "MispellAction" => "Correct misspelled URLs automagically (internal
                 redirect) or present an error page with correct URL:",
        "LanguagePriority" => "You may want to set the language that is the primary
                        one on your web page:",
        "OtherPriority" => "Additional server-side preferences come into use,
                        when the client is unwillingly to state which 
                        file types it supports:",
        "ReflectRewriting" => "Following directive is also used by mod_rewrite:",
        "StatusAllowHost" => "For security reasons you only should allow
                 local users access to these status pages. An incomplete
                 IP address is treaten as match pattern:",
        "ProxyCacheDir" => "Cache directory which will be filled with
                 all requested files:",
        "ProxyAllowIP" => "Only specified hosts (or ranges when
                 incomplete IP given here) are allowed to access other
                 servers using the proxy:",
        "ProxyAccesLog" => "A seperate server log,",
        "ProxyDenySite" => "Sites which shall not be available
                 through the server must be listed in this text file (one
                 server name per line):",
        "LoadLimitErrorMessage" => "You can define a custom error message for
                      blocked requests,",
        "LoadLimitAction" => "Nanoweb can block requests or redirect clients
                      to 'fallback server' when the load limit is reached.",
        "LoadLimitRedirect" => "fallback server "
   );




   $directive_descriptions["PHP"] = array(
        "@PHP" => "The <nobr><b>P</b>HP</nobr> <nobr><b>H</b>ypertext</nobr>
                      <nobr><b>P</b>rocessor</nobr>.<img src=\"{$PHP_SELF}?=" .
                      php_logo_guid() . "\" align=\"right\" alt=\"PHP Logo\"><br>  It wasn't originally
                      planned to make PHP configurable with nanoconfig, so
                      don't expect any miracles!<br><br>You have to make the
                      php.ini writeable as you did with the nanoweb
                      configuration files (<tt>chmod a+rw php.ini</tt>) to
                      be able to adjust the directives.",
        "engine" => "PHP can be enabled or disabled on a per
                      directory basis, that's what this flag is for:",
        "expose_php" => "For security concerns some people don't
                      want to tell the world that they're actually using PHP.
                      Setting this flag only makes sense if none of the
                      scripts is referred with the default trailing .php (add
                      a ParseExt or use mod_rewrite to hide it effectively):",
        "short_open_tag" => "PHP code is enclosed in the XML-style
                      <nobr><tt><b>&lt;?php ... ?&gt;</b></tt></nobr> tag, but most
                      people want to leave out the »php« after the question
                      mark<br><tt>&lt;?...?&gt;</tt> ", 
        "asp_tags" => " ASP tags are rather unusual<br><tt>&lt;%...%&gt;</tt> ",
        "include_path" => "Give a list of colon separated directories
                  to search for included files:",
        "auto_prepend_file" => "Following files are automagically included
                  into every executed script (useful for setup of site
                  specific runtime defaults):",
        "allow_url_fopen" => "PHP allows you to use the standard file access
                   function calls to retrieve http:// and ftp:// resources
                   (reading only). This is a very powerful feature, but can
                   lead easily to security holes.",
        "doc_root" => "The following directives are compatibility ones.
                  Just don't care if you use nanoweb.",
        "@-Variables" => "PHP once was known to be an ease to web development.
                  For security reasons some of its behaviour has changed
                  through the last versions. You can however gain
                  compatibility with older versions with some of these
                  options:",
        "register_globals" => "If you want all GET, POST and COOKIE variables
                  to be automagically accessible in global name space
                  (else these variables are accessible through
                  \$_REQUEST[] or \$_GET[]), you probably want to enable ",
        "variables_order" => "Precedence order for Get, Post, Cookie,
                  Session and Environment variables:",
        "register_argc_argv" => "\$argc and \$argv are only useful to
                  standalone non-www scripts (CLI):",
        "precision" => "length of floating point variables",
        "magic_quotes_gpc" => "Enabling »magic quotes« will lead to
                  escaped meta characters in variables that come from
                  outside or are to be send out:",
        "@-Output" => "Every piece of output that comes from non-script
                  areas or echo() may be buffered or feed through output
                  handlers.",
        "output_buffering" => "You may set a buffer size (default for »on« is
                  4096 bytes) which allows you to send headers() even if
                  something already has been printed out.",
        "zlib.output_compression" => "Instead of specifying one of the generic
                  output handlers above you may want to auto compress your
                  pages with the »zlib« output handler (this is what
                  mod_gzip does for static pages):",
        "implicit_flush" => "If a output handler is active, flush the buffer after every
                  echo() and non-php-script page part?",
        "safe_mode" => "The PHP »Safe Mode« allows to restrict availability
                  of functions or access to not owned files and directories.",
        "safe_mode_gid" => "Check script's GID (»group id« on Linux/UNIX) also
                  against accessed file, additionally to the standard UID
                  (»user id«) test.",
        "safe_mode_include_dir" => "The uid/gid doesn't need to match for following
                  two directories:",
        "disable_functions" => "This directive is also in effect, when safe
                  mode is not enabled:",
        "enable_dl" => "The dl() function allows to load binary extension modules
                  at runtime:",
        "@-Errors/Log" => "Errors may be suppressed or redirected determined by
                 following config directives,",
        "error_reporting" => "Following value may be changed in each script by
                 using the error_reporting() function call; however some
                 errors are produced in load stage (parsing errors for
                 example) and thus cannot be affected later.
                 E_ALL, E_ERROR, E_WARNING, E_PARSE, E_NOTICE,
                 E_COMPILE_ERROR, E_CORE_ERROR are only some of the available
                 bits which may be combined with the »binary OR«: |",
        "display_errors" => "Mixing error messages into standard output
                 (web page) is very handy for development but discouraged for
                 production web sites:",
        "html_errors" => "Display errors using HTML markup (red color for example).
                 You may want to set the error output enhancements in the php.ini
                 after enabling this:",
        "error_log" => "Log errors to »<b>syslog</b>« (also available for
                 NT but not in Windows 4.x) or to the file specified here:",
	"@-CGI" => "These are some legacy options to tweak behaviour of the
                 CGI version of the PHP interpreter.",
        "cgi.force_redirect" => "Should really be disabled for Nanoweb:",
        "cgi.fix_pathinfo" => "If enabled PHP garbages the
                 \$_SERVER[\"PATH_INFO\"] and some other CGI vars. Used for
                 some stupid security reasons with Apache, and should be
                 disabled here.",
	"cgi.rfc2616_headers" => "Apache does not use the CGI/1.1
                 specification headers corrrectly, while Nanoweb does and
                 therefor the RFC2616 (HTTP/1.1) compliant headers
                 should be used:",
        "memory_limit" => "Execution of scripts can be restricted in time
                 and memory usage.",
        "post_max_size" => "Too huge POST requests may trash the server or slow
                  down your scripts considerably, and thus are often used to
                  fault webservers.",
        "file_uploads" => "HTML Forms allow to upload files with the HTTP POST method.",
        "from" => "Anonymous FTP connections require an email
                 address to be send instead of a password:",
        "y2k_compliance" => "not really useful ",
        "extension" => "Many of the PHP extensions are now built-in; however
                  some of them must be explicitly loaded and registered within
                  the PHP core."
   );






   $configuration_pages_add_section["NW"] = array(
               "html" => "Directives in a virtual host section may override
                    values from the main server configuration.<br>",
               "html_2" => "Without a separate directory tree a virtual
                    host section would be completely senseless:<br><br>",
               "DocumentRoot" => "string",
               "html_3" => "Match pattern for additional server names,<br>",
               "ServerAlias" => "multiple",
               "html_4" => "If you would like to have a separate server log for this
                    virtual host, just give a different filename here.<br>",
               "Log" => "string"
   );



   $configuration_pages_add_section["PHP"] = array(
               "html_seedoc" => "Please have a look at the
               <a href=\"{$T[$which]["DOC"]}\">online manual</a> for
               informations on these configuration directives.<br><br>"
   );



?>