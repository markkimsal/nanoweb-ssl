<?php

   # de_DE �bersetzung der nanoconfig-Seiten
   # <mario@erphesfurt�de>

   # translations for "Yes" / "No" variables
   $D_BOOLEAN["boolean"] = array("Nein", "Ja");
   $D_BOOLEAN["boolvalue"] = array("Falsch", "Richtig");
   $D_BOOLEAN["boolpower"] = array("Aus", "Ein");
   $D_BOOLEAN["boolstate"] = array("Deaktiviert", "Aktiviert");


   #  the percent sign can now be used to
   #  translate the menu entries
   #
   #  the @�says that description entry is the
   #  very first thing to get printed on the
   #  according page
   #

   #-- translated descriptions
   $directive_descriptions["NW"] = array(
        "%-General" => "-Allgemein",
        "%-Technical" => "-Technisches",
        "%-Access Control" => "-Zugriffskontrolle",
        "%-Mime Types" => "-MIME Typen",
        "%-Logging" => "-Logbuch",
        "%-CGI Setup" => "-CGI Einstellungen",
        "%-Security" => "-Sicherheit",
        "%-Miscellaneous" => "-Verschiedens",
        "%Modules" => "Module",
        "%-FileBrowser" => "-Datei-Browser",
        "%-Gzip Encoding" => "-Komprimierung",
        "%-Authentication" => "-Authentifizierung",
        "%Virtual Hosts" => "Virtuelle Server",
        "@nanoweb" => '<img src="../nanoweblogo.gif" width="200" height="60" align="right" alt="nanoweb logo" valign="top" border="1">'."
                      Dieses Programm dient einer schnellen Erstkonfiguration
                      von nanoweb. In aller Regel ist es jedoch einfacher
                      die Konfigurationsdateien von Hand zu editieren (sehr
                      gut dokumentiert und einfache Struktur).
                      �brigens sind nicht alle Einstellm�glichkeiten in
                      diese Oberfl�che integriert, so da� sp�testens f�rs
                      Fein-tuning ein Editor n�tig ist.
                      <br><br> Damit dieses Werkzeug die Konfigurationsdateien
                      auch ge�ndert abspeichern kann m�ssen unter
                      Linux/UNIX zun�chst die Schreibrechte gelockert werden
                      (unter Windows nicht notwendig):<br> <TT>chmod a+rw
                      {$T[$which]['CONFIG_FILE']}</TT><br><br>
                      Diese Schreibrechte k�nnen nach Abschlu� aller Einstellungen
                      mit [Lock&nbsp;Config] wieder korrigiert werden.<br><br>
                      [Save] mu� auf <b>jeder</b> Seite angeklickt werden
                      wenn Du etwas ge�ndert hast.<br><br><input
                      type=\"submit\" name=\"lock\" value=\"Lock Config\">
                      <input type=\"submit\" name=\"apply\"
                      value=\"Apply Config\">\n",
        "@-General" => "Der Server ben�tigt einen Standard-Domainnamen, den er an
                      alle Browser zur�cksenden kann, die diesen noch nicht
                      kennen. Es reicht nicht aus sich hier einfach einen
                      Namen auszudenken, dem Betriebssystem mu� dieser auch
                      bekannt gemacht werden, damit er tats�chlich verwendet
                      werden kann (siehe hierzu /etc/hosts oder C:\\winnt\\hosts).",
        "DocumentRoot" => "Das Dokument-Wurzelverzeichnis beherbergt
                      alle Dateien und Ordner die durch nanoweb (also als
                      WWW-Seiten �ber http://) bereitgestellt werden sollen:",
        "DirectoryIndex" => "Sobald eine der im folgenden
                 aufgelisteten Dateien in einem Ordner vorhanden ist, wird
                 diese statt einer automatisch generierten Verzeichnisliste
                 ausgeliefert (auch bekannt als �Startdatei�):",
        "DefaultContentType" => "Wenn nanoweb den Typ einer Datei nicht
                      feststellen kann, soll von diesem Standard-Dateityp
                      ausgegangen werden:",
        "SingleProcessMode" => "Windows&trade; unterst�tzt kein
                 Prozess-�forking� (wie Linux/UNIX), daher startet nanoweb dort in dem
                 etwas langsameren ",
        "ServerMode" => "Bitte unbedingt einen Blick in die READMEs
                 werfen, vor �nderung des ",
        "User" => "Da nanoweb nicht mit den Privilegien des SuperUsers
                 laufen sollte, bitte das voreingestellte �www-data� belassen.",
        "ListenInterface" => "nanoweb mu� zumindest eine IP Schnittstelle und einen
                      TCP Port (80 ist Standard f�r Webserver) �berwachen,
                      um Verbindungen annehmen zu k�nnen.",
        "ListenQueue" => "Eine Zahl von ankommenden Anfragen kann
                 auf eine Abarbeitungsliste gesetzt werden (wenn der Server
                 gerade besch�ftigt ist), ",
        "KeepAlive" => "Moderne Browser k�nnen mehrere Dateien mit
                 nur einem ge�ffneten TCP/IP Kanal abrufen. Wenn Du keine
                 Unterst�tzung daf�r m�chtest, setzt Du einfach den folgenden Wert auf 0:",
        "RequestTimeout" => "Einige Anfragen von Browsern werden nicht
                 ordnungsgem�� beendet (Netzst�rungen, Absturz), so da�
                 Verbindungen offen bleiben. Damit ordnungsgem��e Anfragen
                 durch diese F�lle nicht behindert werden sollte eine
                 maximale Wartezeit zur Vervollst�ndigung von Anfragen
                 festgelegt werden:",
        "ChildLifeTime" => "Von Zeit zu Zeit k�nnen Kind-Prozesse
                 h�ngen bleiben (Endlosschleifen, etc.), so da� alle
                 Serverprozesse nach einer bestimmten Zeitspanne
                 neugestartet werden sollten.",
        "MaxServers" => "Begrenzt die Anzahl maximal gestarteter Server (Kindprozesse).",
        "StaticBufferSize" => "mod_static l�dt gew�hnliche Dateien bis zu einer
                 maximalen Gr��e in den Speicher, um die �bertragung zu beschleunigen:",
        "TempDir" => "Ordner f�r tempor�re Dateien:",
        "@-.nwaccess" => "Die Dateinamen f�r die Verzeichnis-spezifischen
                      Konfigurationsdateien sind frei einstellbar:",
        "AuthFile" => "Diese Dateien enthalten die Authentifizierungs-Daten
                      (Pa�w�rter) f�r den HTTP Auth. Mechanismus:",
        "ACPolicy" => "Nanoweb gew�hrt standardm��ig allen anfragenden
                      Rechnern Zugriff; f�r Intranet-webserver bietet es sich hingegen an zun�chst
                      alle eingehenden Anfragen abzuw�rgen.",
        "ACAllowIP" => "Rechner die Zugriff auf den Datenbestand erhalten
                      sollen k�nnen mit Hostnamen oder IP Adresse angegeben werdeb, wobei
                      Platzhalter-zeichen erlaubt sind.",
        "ACDenyIP" => "Wenn ACPolicy mit 'allow' allen Rechnern Zugriff
                      erlaubt, k�nnen hier einige angegeben werden, f�r
                      die diese allgemeine Erlaubnis nicht zutrifft.",
        "ACBlockError" => "F�r abgeblockte Rechner kann hier eine nette
                      Fehlermeldung angegeben werden:",
        "AccessFile" => "Um die Verzeichnis-config-dateien
                      vom �apache� weiter zu verwenden, k�nntest Du den
                      Namen f�r eben diese Dateien einfach in �<b>.htaccess</b>� �ndern:",
        "AccessPolicy" => "Standardm��ig �berschreiben Einstellungen in
                      .nwaccess Dateien die urspr�nglichen Konfigurationswerte
                      des Servers. Dieses Verhalten l��t sich aber anpassen:",
        "AccessOverride" => "F�r einzelne Direktiven kann 
                      eine abweichende Vorgehensweise konfiguriert werden.",
        "MimeTypes" => "nanoweb holt sich die MIME-Typ
             Zuordnungen (Dateiendung =&gt; Typ) aus der entsprechenden
             Konfigurationsdatei deines Systems (in jeder aktuellen
             Linux Distribution enthalten), so da� Du dich darum eigentlich
             nicht sorgen solltest.  
             (MIME steht �brigens f�r �Mehrzweck Internet Mail Erweiterungen�)",
        "@-Logging" => "nanoweb unterst�tzt verschiedene Methoden alle Aktivit�ten
                      zu vermerken (engl. logging). Ein Erweiterungsmodul
                      erm�glicht es z.B. die Vermerke in eine MySQL Datenbank
                      zu schreiben.",
        "Log" => "Jeder virtuelle Server (und damit auch der Hauptserver)
                 kann ein eigenes Logbuch mit allen Zugriffen speichern.",
        "ServerLog" => "Nanowebs Logbuch Meldungen sind in Klassen
                 unterteilt. Der zweite Parameter zu dem Dateinamen eines
                 ServerLogbuches bestimmt diese und filtert damit die
                 Nachrichten. Eine Liste der m�glichen Fehlertypen findet
                 sich im Handbuch.",
        "HostnameLookups" => "Die DNS-Namensaufl�sung (Domainnamen statt
                 IP-Nummern) verlangsamt den Server:",
        "HostnameLookupsBy" => "Der Host-Name kann auch erst w�hrend der
                 Erstellung des Logbuchs herausgesucht werden, so da� sich
                 der Server nicht damit aufhalten mu�; hierzu �logger�
                 w�hlen:",
        "PidFile" => "Die Pid-Datei enth�lt die �Proze� id� auf Linux/UNIX
                 Maschienen, was es nanoctl erleichtert den Server zu beenden.",
        "LoggerProcess" => "LoggerProcess, LoggerUser/Group k�nnen nur in der
                 Konfigurationsdatei gesetzt werden.",
        "LogHitsToConsole" => "Wenn Du das Log auf der Standard-Ausgabe
                  - also der Konsole (oder Fenster) sehen m�chtest, mu� das
                Modul
                <A HREF=\"".$T["NW"]["DOC"]."/mod_stdlog.html\">mod_stdlog</A>.
                geladen werden.",
        "ParseExt" => "Die ParseExt Direktive definiert welcher CGI
                      Interpreter bei welcher Dateinamenserweiterung
                      verwendet werden soll.",
        "AllowPathInfo" => "�pathinfo� ist ein zus�tzlicher Informationstr�ger
                  neben dem �query string� (GET) oder den POST Variablen.
                  Er wird oft gegen�ber den GET Variablen bevorzugt, weil
                  URLs wie \"script.php?a=x&b=1&cd=234\" nahezu alle
                  Suchmaschinen verschrecken.",
        "PathInfoTryExt" => "Sehr warscheinlich m�chtest Du jede
                      CGI Erweiterung hier auch auflisten, um die
                      Erweiterung sp�ter auslassen zu k�nnen wenn Du ein
                      CGI in einer HTML-Datei referenzierst (also /script/
                      statt /script.php/):",
        "CGIScriptsDir" => "Dateien die in einem dieser Ordner (/cgi-bin/)
                   das Ausf�hrbar-Flag gesetzt haben werden unabh�ngig von
                   ihrer Dateiendung als CGIs behandelt. Wenn hier aber
                   schlicht <b>/</b> eingetragen wird, d�rfen diese CGIs
                   �berall vorkommen.",
        "CGIScriptNoExec" => "Falls eines der Scripte aus /cgi-bin/
                   das �Ausf�hrbar�-Flag nicht gesetzt hat, kann nanoweb eine
                   Fehlermeldung an den Client zur�cksenden (error), oder das
                   Script wie eine gew�hnliche Datei ausliefern (raw).",
        "CGIFilterPathInfo" => "Der PHP Interpreter hat immer noch einen
                     Fehler, der die \$PHP_SELF Variable bei einer vorhanden
                     \$PATH_INFO Variable unbrauchbar macht.
                     Wenn diese Direktiven aktiviert werden, wird also kein
                     PATH_INFO �bertragen, bleiben sie unaktiviert kann
                     immernoch SCRIPT_NAME an stelle von PHP_SELF verwendet
                     werden:",
        "ConfigDir" => "Das Verzeichnis, da� alle Konfigurations- und
                  Themendateien von Nanoweb enth�lt:",
        "AllowSymlinkTo" => "Webserver sollten nur Zugang zu Dateien erlauben
                  die innerhalb des Dokument-Wurzelverzeichnisses
                  liegen. Falls jedoch Dateien au�erhalb dieses
                  Bereiches verlinkt werden (nur �symlinks� unter Linux/UNIX,
                  Windows-Verweise sind Bastelkram), sind diese nur
                  zug�nglich wenn das Zielverzeichnis mit folgender
                  Direktive freigeschaltet wird:",
        "IgnoreDotFiles" => "Dateien deren Namen mit einem Punkt
                  beginnen werden von vielen UNIX-Programmen als
                  unsichtbar behandlet; nanoweb's Verzeichnis-config-Dateien
                  fallen z.B. in diese Kategorie. Daher m�chtest Du
                  normalerweise nicht, da� diese Dateien �bertragen
                  werden k�nnen:",
        "Alias" => "Mit der Alias Direktive k�nnen beliebigen
                  Ordnern von der Festplatte virtuelle Verzeichnisnamen
                  innerhalb von nanoweb zugeordnet werden.
                  Diese virtuellen Verzeichnisse k�nnen auch in jedem der
                  virtuellen Server verwendet werden, unabh�ngig von
                  sonstigen Einstellungen:",
        "ServerSignature" => "Nanoweb gibt anfragenden Browsern
                  normalerweise einige Details �ber sich im 'Server:'-Feld
                  einer jeden HTTP-Antwort preis. Der Umfang der Infos
                  kann jedoch begrenzt werden; aus Sicherheitsgr�nden k�nnte
                  sogar eine v�llig falsche Angabe (fake) gemacht werden.",
        "ErrorDocument" => "F�r jeden auftretenden Fehler kann
                  eine individuelle Fehlerseite angezeigt werden (an Stelle
                  der Standard-Meldungen von nanoweb):",
        "AddHeader" => "Diese Direktive erlaubt das Mitsenden von
                  beliebigen HTTP Kopfzeilen:",
        "UserDir" => "Private Webseiten eines System-Benutzers werden via
                  <b>http://server/~user</b> zug�nglich, wenn der
                  entsprechende Benutzer folgendes Unterverzeichnis in seinem
                  Heimatverzeichnis anlegt:",
        "@Modules" => "Folgende Erweiterungs-module werden momentan beim
                 Starten in den Server geladen. Hinweis: Eintr�ge die
                 in der Konfigurationsdatei auskommentiert sind k�nnen momentan noch nicht von
                 nanoconfig angezeigt werden.
                 Ein Blick in das Handbuch verr�t
                 <a href=\"{$T["NW"]["DOCDIR"]}/modules.html\">welche Module</a>
                 derzeit f�r nanoweb verf�gbar sind.",
        "GzipEnable" => "Nahezu alle modernen Browser unterst�tzen das
                      Standard-Komprimierungsverfahren �gzip� (auch
                      bekannt als �zlib Format�). Die Verwendung von gzip
                      beschleunigt die �bertragung (der Zeitaufwand die
                      Seiten zu Komprimieren ist sehr gering) weil zum einen
                      weniger TCP/IP-Pakete verloren gehen k�nnen weil
                      weniger davon �bertragen werden m�ssen, und zum
                      anderen bechleunigt diese komprimierte �bertragung das
                      Herunterladen �ber die immernoch h�ufig verwendeten
                      Modems.",
        "GzipMaxRatio" => "Die Dateien sollten nur komprimiert �bertragen
                     werden, falls diese nicht schon komprimiert sein sollten:",
        "FileBrowser" => "Das Erweiterungsmodul �FileBrowser� generiert die
                      Verzeichnislistings f�r nanoweb, wenn keine
                      �Start-Datei� (index.html) in einem Ordner vorgefunden
                      wird. Die Ausgabe kann vielf�ltig versch�nert werden:",
        "FBIconDirectory" => "Das /icons/ Verzeichnis ist eines der
                  voreingestellten Alias-Verzeichnisse, und erm�glicht damit
                  einen einfachen Zugriff auf die Bildchen die
                  jedem Dateityp zugeordnet werden k�nnen:",
        "MispellAction" => "Dieses Modul korrigiert falsch
                 eingegebene URLs; wahlweise kann ein Hinweis (<b>advice</b>) auf die 
                 richtige Addresse ausgegeben werden, oder eine automatische
                 Weiterleitung (<b>redirect</b>) erfolgen:",
        "@-MultiViews" => "Das Multiviews-Modul (TCN) erweitert nanoweb um automatische
                      Inhalts-selektion (d.h. die richtige Datei aus einer
                      Menge von vorhandenen Varianten an Hand der vom
                      Browser unterst�tzten oder gew�nschten Dateitypen
                      und Lieblingssprachen des Benutzers auszuw�hlen).
                       Unterst�tzt teilweise RVSA/1.0",
            "LanguagePriority" => "Die prim�r verwendeten Sprachen auf ihrem Server:",
            "OtherPriority" => "Zus�tzliche Server-seitige Pr�ferenzen, die verwendet
                        werden, wenn der Browser keine Angaben �ber
                        unterst�tzte Dateitypen macht.",
            "ReflectRewriting" => "Diese Direktive beeinflu�t auch mod_rewrite:",
        "@-Status" => "mod_status erlaubt es einige <a
                 href=\"/server-status?detailed\">Informationen �ber
                 den laufenden Server</a> online einzusehen.
                 Im <a href=\"{$T["NW"]["DOC"]}\">Handbuch</a> findet sich
                 eine Beschreibung �ber die vorhanden Info-Seiten.",
        "StatusAllowHost" => "Aus Sicherheitsgr�nden solltest Du nur lokalen
                 Rechnern Zugang zu den Status-Seiten gew�hren.
                 Unvollst�ndige IP-Angaben werden als Vergleichsmuster
                 ausgewertet:",
        "@-StdoutLog" => "Das Modul mod_stdlog gibt das Logbuch auf die
                      Konsole aus. Das funktioniert nat�rlich nur, wenn
                      nanoweb.php direkt (ohne nanoctl) von dort gestartet
                      wurde.",
        "@-MySQL Logging" => "mod_mysqllog schreibt das Server-Logbuch in eine
                 Datenbank (nanoweb benutzt ansonsten eine ganz gew�hnliche
                 Datei daf�r).
                 Die entsprechende Tabelle wird beim ersten Start
                 automatisch erstellt, wenn alle Einstellungen
                 richtig sind:",
	"@Virtual Hosts" => "Ein �virtual host� (oder �virtueller Server�)
                      besitzt einen Domain-namen und ein Wurzelverzeichnis,
                      die sich von dem des Hauptservers unterscheiden.
                      Viele der Direktiven die in der Hauptkonfiguration
                      verwendet werden k�nnen, sind auch hier erlaubt.<br><br>"
                     .'DNS-Name f�r den neuen virtuellen Server:<input size="42" name="add_vhost"> <input type="submit" value="Add" name="save"><br> '
   );

   #-- PHP Dokumentation
   $T["PHP"]["DOC"] = "http://www.php.net/manual/de/";
   $T["PHP"]["DOCREF"] = "http://www.php.net/manual/de/configuration.php#ini.";

   #-- added [sections]
   $configuration_pages_add_section["NW"] = array(
               "html" => "Direktiven f�r einen �virtuellen Server� d�rfen
                    von den Einstellungen des Hauptservers abweichen. ",
               "html_2" => "Ohne einen seperaten Verzeichnisbaum f�r dessen
                    Dateien, w�re ein virtueller Server absolut bl�dsinnig:<br><br>",
               "DocumentRoot" => "string",
               "html_3" => "Vergleichsmuster f�r zus�tzliche Domain-Namen,<br>",
               "ServerAlias" => "multiple",
               "html_4" => "Wenn Du ein eigenst�ndiges Logbuch f�r diesen
                    virtuellen Server m�chtest, dann trage bitte hier den
                    entsprechenden Dateinamen ein.<br>",
               "Log" => "string"
   );
   $configuration_pages_add_section["PHP"] = array(
               "html_seedoc" => "Bitte wirf' einen Blick in das
               <a href=\"{$T[$which]["DOC"]}\">Handbuch</a> f�r
               Informationen zu folgenden Konfigurations&shy;einstellungen.<br><br>"
   );


   #-- PHP-Seiten
   $directive_descriptions["PHP"] = array(
        "@PHP" => "<nobr><b>P</b>HP</nobr> <nobr><b>H</b>ypertext</nobr>
                      <nobr><b>P</b>rocessor</nobr> <img src=\"{$PHP_SELF}?=" .
                      php_logo_guid() . "\" align=\"right\" alt=\"PHP Logo\">
                      Es war urspr�nglich nicht geplant PHP auch mit
                      nanoconfig einstellen zu k�nnen (wenngleich beide
                      Konfigurationsdateien einen �hnlichen Aufbau haben);
                      also bitte keine Wunder erwarten!
                      <br><br>
                      Nat�rlich mu� auch die <b>php.ini</b> wieder f�r dieses
                      Tool beschreibbar gemacht werden (nicht n�tig f�r Windows,
                      weil es dort ja keinen Schreibschutz gibt):<br>
                      <tt>chmod a+rw php.ini</tt>",
        "engine" => "PHP kann in den einzelnen Unterverzeichnissen
                      aktiviert und deaktiviert werden;",
        "expose_php" => "Wegen Sicherheitsbedenken m�chten einige
                      Leute nicht verraten, da� PHP benutzt wird...
                      Diese Option zu setzen, macht nat�rlich nur Sinn,
                      wenn man nicht anhand der Dateinamen ohnehin ablesen kann,
                      da� PHP verwendet wird (mit ParseExt oder mod_rewrite,
                      mod_multiviews k�nnte man dies jedoch verschleiern).",
        "short_open_tag" => "PHP-code wird in das XML-Tag
                      <nobr><tt><b>&lt;?php ... ?&gt;</b></tt></nobr>
                      eingeschlossen, normalerweise m�chte man aber auch
                      die Kurzform (ohne das �php� nach dem Fragezeichen) verwenden
                      <tt>&lt;?...?&gt;</tt> ", 
        "asp_tags" => "ASP-�hnliche Tags werden eher selten verwendet <tt>&lt;%...%&gt;</tt> ",
        "include_path" => "Eine Liste mit Ordnern in denen nach
                  include() Dateien gesucht werden soll (Ordner mit
                  Doppelpunkten trennen; bzw. Semikolon unter Windows)",
        "auto_prepend_file" => "Die folgenden Dateien sollen automatisch am
                  Anfang und am Ende jedes PHP-Scriptes eingef�gt werden
                  (n�tzlich um allgemeine Server-spezifische Laufzeit-Einstellungen
                  vorzunehmen).",
        "allow_url_fopen" => "PHP erlaubt es mit den normalen Datei-
                  Ein-/Ausgabefunktionen auch auf entfernte Dateien 
                  mit http:// and ftp:// (lesend) zuzugreifen. Sehr n�tzlich,
                  aber u.U. ein Sicherheitsloch bei schlecht durchdachten
                  Scripten.",
        "doc_root" => "Die folgenden Einstellungen dienen haupts�chlich
                  der Kompatibilit�t. Wer nanoweb verwendet, sollte sich
                  hierum nicht k�mmern m�ssen.",
        "@-Variables" => "PHP war urspr�nglich als die einfachste und f�r die
                  Webentwicklung schnellste Scriptsprache ersonnen worden,
                  allerdings wurden aus Sicherheitsgr�nden einige �nderungen
                  im Verhalten der Sprache eingef�hrt. Mit folgenden Optionen
                  kann jedoch die Kompatibilit�t mit fr�heren Versionen
                  wieder hergestellt werden:",
        "register_globals" => "Wenn Du alle GET, POST und COOKIE Variablen
                  automatisch im globalen Namensraum verf�gbar haben m�chtest
                  (andernfalls sind die Variablen �ber \$_REQUEST[] oder \$_GET[]
                  erreichbar), solltest Du folgende Option aktivieren,",
        "varialbes_order" => "Vorzugsreihenfolge f�r Get-, Post-, Cookie-,
                  Session- und Umgebungs-variablen:",
        "register_argc_argv" => "\$argc und \$argv sind eigenlich nur in
                  eigenst�ndigen (nicht-www) Scripten n�tzlich (CLI):",
        "precision" => "Gleitkommagenauigkeit",
        "magic_quotes_gpc" => "Atkivierte �magic quotes� f�hren dazu,
                  da� Meta-Zeichen (Anf�hrungszeichen, Backslash) in allen
                  Variablen, die von \"au�en\" kommen oder die nach au�en wandern,
                  automatisch gesch�tzt werden (durch vorangestellten Backslash):",
        "@-Output" => "Alle Ausgaben (also alle nicht-PHP-Bereiche oder
                  echo()-Aufrufe) k�nnen zwischengespeichert (gepuffert)
                  werden und/oder durch eine spezielle Bearbeitungsfkt.
                  gesendet werden.",
        "output_buffering" => "Du kannst die Puffergr��e zur
                  Ausgabeverz�gerung selber festlegen (�On� steht f�r 4096 Byte).
                  Ein solcher Zwischenspeicher erlaubt es u.a. weitere
                  header() zu senden obwohl bereits Ausgaben gemacht wurden.",
        "zlib.output_compression" => "An Stelle einer der allgemeinen
                  Ausgaberoutinen (das Feld hier�ber) kann auch die oft
                  bevorzugte komprimierte �bertragung der HTML-Seite
                  aktiviert werden:",
        "implicit_flush" => "Wenn ein Ausgabebehandler aktiviert ist,
                  soll der Puffer automatisch nach jeden echo()
                  geleert werden?",
        "safe_mode" => "Der �Sichere Modus� von PHP erlaubt es die
                  Verf�gbarkeit von bestimmten Systemfunktionen zu beschr�nken
                  und auch den Zugriff auf nicht-eigene Dateien zu verhindern.",
        "safe_mode_gid" => "Neben dem Eigent�mer soll beim Zugriff auf eine
                  Datei auch die Gruppenzugeh�rigkeit �berpr�ft werden
                  (GID - �group id� unter Linux/UNIX).",
        "safe_mode_include_dir" => "Eigent�mer/Gruppe wird f�r folgende
                  Ordner ignoriert:",
        "disable_functions" => "Die folgende Einstellung funktioniert
                  unabh�ngig von �Safe Mode�:",
        "enable_dl" => "Die dl() Funktion erlaubt es Erweiterungsmodule
                  noch w�hrend der Laufzeit nachzuladen:",
        "@-Errors/Log" => "Fehlermeldungen werden unterdr�ckt oder umgeleitet
                 abh�ngig von folgenden Einstellungen:",
        "error_reporting" => "Dieser Wert kann sp�ter auch in jedem Script
                 mit error_reporting() �bergangen werden. Einige Fehler
                 treten jedoch bereits beim Laden auf (z.B. Parser-Fehler),
                 und werden daher immer angezeigt, wenn sie hier nicht
                 deaktiviert sind.
                 E_ALL, E_ERROR, E_WARNING, E_PARSE, E_NOTICE,
                 E_COMPILE_ERROR, E_CORE_ERROR sind nur einige der Optionen
                 die hier mit | oder + kombiniert werden k�nnen:",
        "display_errors" => "Die Fehlermeldungen gleich in die
                 erstellten Seiten mitauszugeben ist sehr hilfreich bei der Entwicklung,
                 aber nicht empfohlen auf fertigen Internetpr�senzen:",
        "html_errors" => "Fehler mit HTML Auszeichnungen hervorheben
                 (in aller Regel roter Text, was aber in der php.ini
                 eingestellt werden kann).",
        "error_log" => "Fehler-Logbuch soll ins �<b>syslog</b>�
                 (gibt es auch unter NT, nicht jedoch im Windows) oder in
                 die hier angegebene Datei geschrieben werden:",
        "memory_limit" => "Der Ausf�hrung von Scripten k�nnen im Speicherverbrauch
                 und der ben�tigten Ausf�hrungszeit Grenzen gesetzt werden.",
        "post_max_size" => "Zu gro�e POST Anfragen k�nnen den Server zum
                  Absturz bringen, und f�hren relativ h�ufig zu einer
                  erheblichen Verlangsamung der Ausf�hrung.",
        "from" => "Anonyme FTP �bertragungen erfordern die Angabe
                 einer EMail-Adresse an Stelle eines Pa�wortes:",
        "y2k_compliance" => "(nicht wirklich n�tzlich) ",
        "extension" => "Viele der PHP Erweiterungen sind inzwischen direkt in den
                 Interpreter einkompiliert, so da� nur noch einige wenige expilizit geladen
                 werden m�ssen."
   );


?>