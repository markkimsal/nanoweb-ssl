<?php

   # de_DE Übersetzung der nanoconfig-Seiten
   # <mario@erphesfurt·de>

   # translations for "Yes" / "No" variables
   $D_BOOLEAN["boolean"] = array("Nein", "Ja");
   $D_BOOLEAN["boolvalue"] = array("Falsch", "Richtig");
   $D_BOOLEAN["boolpower"] = array("Aus", "Ein");
   $D_BOOLEAN["boolstate"] = array("Deaktiviert", "Aktiviert");


   #  the percent sign can now be used to
   #  translate the menu entries
   #
   #  the @ says that description entry is the
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
                      Übrigens sind nicht alle Einstellmöglichkeiten in
                      diese Oberfläche integriert, so daß spätestens fürs
                      Fein-tuning ein Editor nötig ist.
                      <br><br> Damit dieses Werkzeug die Konfigurationsdateien
                      auch geändert abspeichern kann müssen unter
                      Linux/UNIX zunächst die Schreibrechte gelockert werden
                      (unter Windows nicht notwendig):<br> <TT>chmod a+rw
                      {$T[$which]['CONFIG_FILE']}</TT><br><br>
                      Diese Schreibrechte können nach Abschluß aller Einstellungen
                      mit [Lock&nbsp;Config] wieder korrigiert werden.<br><br>
                      [Save] muß auf <b>jeder</b> Seite angeklickt werden
                      wenn Du etwas geändert hast.<br><br><input
                      type=\"submit\" name=\"lock\" value=\"Lock Config\">
                      <input type=\"submit\" name=\"apply\"
                      value=\"Apply Config\">\n",
        "@-General" => "Der Server benötigt einen Standard-Domainnamen, den er an
                      alle Browser zurücksenden kann, die diesen noch nicht
                      kennen. Es reicht nicht aus sich hier einfach einen
                      Namen auszudenken, dem Betriebssystem muß dieser auch
                      bekannt gemacht werden, damit er tatsächlich verwendet
                      werden kann (siehe hierzu /etc/hosts oder C:\\winnt\\hosts).",
        "DocumentRoot" => "Das Dokument-Wurzelverzeichnis beherbergt
                      alle Dateien und Ordner die durch nanoweb (also als
                      WWW-Seiten über http://) bereitgestellt werden sollen:",
        "DirectoryIndex" => "Sobald eine der im folgenden
                 aufgelisteten Dateien in einem Ordner vorhanden ist, wird
                 diese statt einer automatisch generierten Verzeichnisliste
                 ausgeliefert (auch bekannt als »Startdatei«):",
        "DefaultContentType" => "Wenn nanoweb den Typ einer Datei nicht
                      feststellen kann, soll von diesem Standard-Dateityp
                      ausgegangen werden:",
        "SingleProcessMode" => "Windows&trade; unterstützt kein
                 Prozess-»forking« (wie Linux/UNIX), daher startet nanoweb dort in dem
                 etwas langsameren ",
        "ServerMode" => "Bitte unbedingt einen Blick in die READMEs
                 werfen, vor Änderung des ",
        "User" => "Da nanoweb nicht mit den Privilegien des SuperUsers
                 laufen sollte, bitte das voreingestellte »www-data« belassen.",
        "ListenInterface" => "nanoweb muß zumindest eine IP Schnittstelle und einen
                      TCP Port (80 ist Standard für Webserver) überwachen,
                      um Verbindungen annehmen zu können.",
        "ListenQueue" => "Eine Zahl von ankommenden Anfragen kann
                 auf eine Abarbeitungsliste gesetzt werden (wenn der Server
                 gerade beschäftigt ist), ",
        "KeepAlive" => "Moderne Browser können mehrere Dateien mit
                 nur einem geöffneten TCP/IP Kanal abrufen. Wenn Du keine
                 Unterstützung dafür möchtest, setzt Du einfach den folgenden Wert auf 0:",
        "RequestTimeout" => "Einige Anfragen von Browsern werden nicht
                 ordnungsgemäß beendet (Netzstörungen, Absturz), so daß
                 Verbindungen offen bleiben. Damit ordnungsgemäße Anfragen
                 durch diese Fälle nicht behindert werden sollte eine
                 maximale Wartezeit zur Vervollständigung von Anfragen
                 festgelegt werden:",
        "ChildLifeTime" => "Von Zeit zu Zeit können Kind-Prozesse
                 hängen bleiben (Endlosschleifen, etc.), so daß alle
                 Serverprozesse nach einer bestimmten Zeitspanne
                 neugestartet werden sollten.",
        "MaxServers" => "Begrenzt die Anzahl maximal gestarteter Server (Kindprozesse).",
        "StaticBufferSize" => "mod_static lädt gewöhnliche Dateien bis zu einer
                 maximalen Größe in den Speicher, um die Übertragung zu beschleunigen:",
        "TempDir" => "Ordner für temporäre Dateien:",
        "@-.nwaccess" => "Die Dateinamen für die Verzeichnis-spezifischen
                      Konfigurationsdateien sind frei einstellbar:",
        "AuthFile" => "Diese Dateien enthalten die Authentifizierungs-Daten
                      (Paßwörter) für den HTTP Auth. Mechanismus:",
        "ACPolicy" => "Nanoweb gewährt standardmäßig allen anfragenden
                      Rechnern Zugriff; für Intranet-webserver bietet es sich hingegen an zunächst
                      alle eingehenden Anfragen abzuwürgen.",
        "ACAllowIP" => "Rechner die Zugriff auf den Datenbestand erhalten
                      sollen können mit Hostnamen oder IP Adresse angegeben werdeb, wobei
                      Platzhalter-zeichen erlaubt sind.",
        "ACDenyIP" => "Wenn ACPolicy mit 'allow' allen Rechnern Zugriff
                      erlaubt, können hier einige angegeben werden, für
                      die diese allgemeine Erlaubnis nicht zutrifft.",
        "ACBlockError" => "Für abgeblockte Rechner kann hier eine nette
                      Fehlermeldung angegeben werden:",
        "AccessFile" => "Um die Verzeichnis-config-dateien
                      vom »apache« weiter zu verwenden, könntest Du den
                      Namen für eben diese Dateien einfach in »<b>.htaccess</b>« ändern:",
        "AccessPolicy" => "Standardmäßig überschreiben Einstellungen in
                      .nwaccess Dateien die ursprünglichen Konfigurationswerte
                      des Servers. Dieses Verhalten läßt sich aber anpassen:",
        "AccessOverride" => "Für einzelne Direktiven kann 
                      eine abweichende Vorgehensweise konfiguriert werden.",
        "MimeTypes" => "nanoweb holt sich die MIME-Typ
             Zuordnungen (Dateiendung =&gt; Typ) aus der entsprechenden
             Konfigurationsdatei deines Systems (in jeder aktuellen
             Linux Distribution enthalten), so daß Du dich darum eigentlich
             nicht sorgen solltest.  
             (MIME steht übrigens für »Mehrzweck Internet Mail Erweiterungen«)",
        "@-Logging" => "nanoweb unterstützt verschiedene Methoden alle Aktivitäten
                      zu vermerken (engl. logging). Ein Erweiterungsmodul
                      ermöglicht es z.B. die Vermerke in eine MySQL Datenbank
                      zu schreiben.",
        "Log" => "Jeder virtuelle Server (und damit auch der Hauptserver)
                 kann ein eigenes Logbuch mit allen Zugriffen speichern.",
        "ServerLog" => "Nanowebs Logbuch Meldungen sind in Klassen
                 unterteilt. Der zweite Parameter zu dem Dateinamen eines
                 ServerLogbuches bestimmt diese und filtert damit die
                 Nachrichten. Eine Liste der möglichen Fehlertypen findet
                 sich im Handbuch.",
        "HostnameLookups" => "Die DNS-Namensauflösung (Domainnamen statt
                 IP-Nummern) verlangsamt den Server:",
        "HostnameLookupsBy" => "Der Host-Name kann auch erst während der
                 Erstellung des Logbuchs herausgesucht werden, so daß sich
                 der Server nicht damit aufhalten muß; hierzu »logger«
                 wählen:",
        "PidFile" => "Die Pid-Datei enthält die »Prozeß id« auf Linux/UNIX
                 Maschienen, was es nanoctl erleichtert den Server zu beenden.",
        "LoggerProcess" => "LoggerProcess, LoggerUser/Group können nur in der
                 Konfigurationsdatei gesetzt werden.",
        "LogHitsToConsole" => "Wenn Du das Log auf der Standard-Ausgabe
                  - also der Konsole (oder Fenster) sehen möchtest, muß das
                Modul
                <A HREF=\"".$T["NW"]["DOC"]."/mod_stdlog.html\">mod_stdlog</A>.
                geladen werden.",
        "ParseExt" => "Die ParseExt Direktive definiert welcher CGI
                      Interpreter bei welcher Dateinamenserweiterung
                      verwendet werden soll.",
        "AllowPathInfo" => "»pathinfo« ist ein zusätzlicher Informationsträger
                  neben dem »query string« (GET) oder den POST Variablen.
                  Er wird oft gegenüber den GET Variablen bevorzugt, weil
                  URLs wie \"script.php?a=x&b=1&cd=234\" nahezu alle
                  Suchmaschinen verschrecken.",
        "PathInfoTryExt" => "Sehr warscheinlich möchtest Du jede
                      CGI Erweiterung hier auch auflisten, um die
                      Erweiterung später auslassen zu können wenn Du ein
                      CGI in einer HTML-Datei referenzierst (also /script/
                      statt /script.php/):",
        "CGIScriptsDir" => "Dateien die in einem dieser Ordner (/cgi-bin/)
                   das Ausführbar-Flag gesetzt haben werden unabhängig von
                   ihrer Dateiendung als CGIs behandelt. Wenn hier aber
                   schlicht <b>/</b> eingetragen wird, dürfen diese CGIs
                   überall vorkommen.",
        "CGIScriptNoExec" => "Falls eines der Scripte aus /cgi-bin/
                   das »Ausführbar«-Flag nicht gesetzt hat, kann nanoweb eine
                   Fehlermeldung an den Client zurücksenden (error), oder das
                   Script wie eine gewöhnliche Datei ausliefern (raw).",
        "CGIFilterPathInfo" => "Der PHP Interpreter hat immer noch einen
                     Fehler, der die \$PHP_SELF Variable bei einer vorhanden
                     \$PATH_INFO Variable unbrauchbar macht.
                     Wenn diese Direktiven aktiviert werden, wird also kein
                     PATH_INFO übertragen, bleiben sie unaktiviert kann
                     immernoch SCRIPT_NAME an stelle von PHP_SELF verwendet
                     werden:",
        "ConfigDir" => "Das Verzeichnis, daß alle Konfigurations- und
                  Themendateien von Nanoweb enthält:",
        "AllowSymlinkTo" => "Webserver sollten nur Zugang zu Dateien erlauben
                  die innerhalb des Dokument-Wurzelverzeichnisses
                  liegen. Falls jedoch Dateien außerhalb dieses
                  Bereiches verlinkt werden (nur »symlinks« unter Linux/UNIX,
                  Windows-Verweise sind Bastelkram), sind diese nur
                  zugänglich wenn das Zielverzeichnis mit folgender
                  Direktive freigeschaltet wird:",
        "IgnoreDotFiles" => "Dateien deren Namen mit einem Punkt
                  beginnen werden von vielen UNIX-Programmen als
                  unsichtbar behandlet; nanoweb's Verzeichnis-config-Dateien
                  fallen z.B. in diese Kategorie. Daher möchtest Du
                  normalerweise nicht, daß diese Dateien übertragen
                  werden können:",
        "Alias" => "Mit der Alias Direktive können beliebigen
                  Ordnern von der Festplatte virtuelle Verzeichnisnamen
                  innerhalb von nanoweb zugeordnet werden.
                  Diese virtuellen Verzeichnisse können auch in jedem der
                  virtuellen Server verwendet werden, unabhängig von
                  sonstigen Einstellungen:",
        "ServerSignature" => "Nanoweb gibt anfragenden Browsern
                  normalerweise einige Details über sich im 'Server:'-Feld
                  einer jeden HTTP-Antwort preis. Der Umfang der Infos
                  kann jedoch begrenzt werden; aus Sicherheitsgründen könnte
                  sogar eine völlig falsche Angabe (fake) gemacht werden.",
        "ErrorDocument" => "Für jeden auftretenden Fehler kann
                  eine individuelle Fehlerseite angezeigt werden (an Stelle
                  der Standard-Meldungen von nanoweb):",
        "AddHeader" => "Diese Direktive erlaubt das Mitsenden von
                  beliebigen HTTP Kopfzeilen:",
        "UserDir" => "Private Webseiten eines System-Benutzers werden via
                  <b>http://server/~user</b> zugänglich, wenn der
                  entsprechende Benutzer folgendes Unterverzeichnis in seinem
                  Heimatverzeichnis anlegt:",
        "@Modules" => "Folgende Erweiterungs-module werden momentan beim
                 Starten in den Server geladen. Hinweis: Einträge die
                 in der Konfigurationsdatei auskommentiert sind können momentan noch nicht von
                 nanoconfig angezeigt werden.
                 Ein Blick in das Handbuch verrät
                 <a href=\"{$T["NW"]["DOCDIR"]}/modules.html\">welche Module</a>
                 derzeit für nanoweb verfügbar sind.",
        "GzipEnable" => "Nahezu alle modernen Browser unterstützen das
                      Standard-Komprimierungsverfahren »gzip« (auch
                      bekannt als »zlib Format«). Die Verwendung von gzip
                      beschleunigt die Übertragung (der Zeitaufwand die
                      Seiten zu Komprimieren ist sehr gering) weil zum einen
                      weniger TCP/IP-Pakete verloren gehen können weil
                      weniger davon übertragen werden müssen, und zum
                      anderen bechleunigt diese komprimierte Übertragung das
                      Herunterladen über die immernoch häufig verwendeten
                      Modems.",
        "GzipMaxRatio" => "Die Dateien sollten nur komprimiert übertragen
                     werden, falls diese nicht schon komprimiert sein sollten:",
        "FileBrowser" => "Das Erweiterungsmodul »FileBrowser« generiert die
                      Verzeichnislistings für nanoweb, wenn keine
                      »Start-Datei« (index.html) in einem Ordner vorgefunden
                      wird. Die Ausgabe kann vielfältig verschönert werden:",
        "FBIconDirectory" => "Das /icons/ Verzeichnis ist eines der
                  voreingestellten Alias-Verzeichnisse, und ermöglicht damit
                  einen einfachen Zugriff auf die Bildchen die
                  jedem Dateityp zugeordnet werden können:",
        "MispellAction" => "Dieses Modul korrigiert falsch
                 eingegebene URLs; wahlweise kann ein Hinweis (<b>advice</b>) auf die 
                 richtige Addresse ausgegeben werden, oder eine automatische
                 Weiterleitung (<b>redirect</b>) erfolgen:",
        "@-MultiViews" => "Das Multiviews-Modul (TCN) erweitert nanoweb um automatische
                      Inhalts-selektion (d.h. die richtige Datei aus einer
                      Menge von vorhandenen Varianten an Hand der vom
                      Browser unterstützten oder gewünschten Dateitypen
                      und Lieblingssprachen des Benutzers auszuwählen).
                       Unterstützt teilweise RVSA/1.0",
            "LanguagePriority" => "Die primär verwendeten Sprachen auf ihrem Server:",
            "OtherPriority" => "Zusätzliche Server-seitige Präferenzen, die verwendet
                        werden, wenn der Browser keine Angaben über
                        unterstützte Dateitypen macht.",
            "ReflectRewriting" => "Diese Direktive beeinflußt auch mod_rewrite:",
        "@-Status" => "mod_status erlaubt es einige <a
                 href=\"/server-status?detailed\">Informationen über
                 den laufenden Server</a> online einzusehen.
                 Im <a href=\"{$T["NW"]["DOC"]}\">Handbuch</a> findet sich
                 eine Beschreibung über die vorhanden Info-Seiten.",
        "StatusAllowHost" => "Aus Sicherheitsgründen solltest Du nur lokalen
                 Rechnern Zugang zu den Status-Seiten gewähren.
                 Unvollständige IP-Angaben werden als Vergleichsmuster
                 ausgewertet:",
        "@-StdoutLog" => "Das Modul mod_stdlog gibt das Logbuch auf die
                      Konsole aus. Das funktioniert natürlich nur, wenn
                      nanoweb.php direkt (ohne nanoctl) von dort gestartet
                      wurde.",
        "@-MySQL Logging" => "mod_mysqllog schreibt das Server-Logbuch in eine
                 Datenbank (nanoweb benutzt ansonsten eine ganz gewöhnliche
                 Datei dafür).
                 Die entsprechende Tabelle wird beim ersten Start
                 automatisch erstellt, wenn alle Einstellungen
                 richtig sind:",
	"@Virtual Hosts" => "Ein »virtual host« (oder »virtueller Server«)
                      besitzt einen Domain-namen und ein Wurzelverzeichnis,
                      die sich von dem des Hauptservers unterscheiden.
                      Viele der Direktiven die in der Hauptkonfiguration
                      verwendet werden können, sind auch hier erlaubt.<br><br>"
                     .'DNS-Name für den neuen virtuellen Server:<input size="42" name="add_vhost"> <input type="submit" value="Add" name="save"><br> '
   );

   #-- PHP Dokumentation
   $T["PHP"]["DOC"] = "http://www.php.net/manual/de/";
   $T["PHP"]["DOCREF"] = "http://www.php.net/manual/de/configuration.php#ini.";

   #-- added [sections]
   $configuration_pages_add_section["NW"] = array(
               "html" => "Direktiven für einen »virtuellen Server« dürfen
                    von den Einstellungen des Hauptservers abweichen. ",
               "html_2" => "Ohne einen seperaten Verzeichnisbaum für dessen
                    Dateien, wäre ein virtueller Server absolut blödsinnig:<br><br>",
               "DocumentRoot" => "string",
               "html_3" => "Vergleichsmuster für zusätzliche Domain-Namen,<br>",
               "ServerAlias" => "multiple",
               "html_4" => "Wenn Du ein eigenständiges Logbuch für diesen
                    virtuellen Server möchtest, dann trage bitte hier den
                    entsprechenden Dateinamen ein.<br>",
               "Log" => "string"
   );
   $configuration_pages_add_section["PHP"] = array(
               "html_seedoc" => "Bitte wirf' einen Blick in das
               <a href=\"{$T[$which]["DOC"]}\">Handbuch</a> für
               Informationen zu folgenden Konfigurations&shy;einstellungen.<br><br>"
   );


   #-- PHP-Seiten
   $directive_descriptions["PHP"] = array(
        "@PHP" => "<nobr><b>P</b>HP</nobr> <nobr><b>H</b>ypertext</nobr>
                      <nobr><b>P</b>rocessor</nobr> <img src=\"{$PHP_SELF}?=" .
                      php_logo_guid() . "\" align=\"right\" alt=\"PHP Logo\">
                      Es war ursprünglich nicht geplant PHP auch mit
                      nanoconfig einstellen zu können (wenngleich beide
                      Konfigurationsdateien einen ähnlichen Aufbau haben);
                      also bitte keine Wunder erwarten!
                      <br><br>
                      Natürlich muß auch die <b>php.ini</b> wieder für dieses
                      Tool beschreibbar gemacht werden (nicht nötig für Windows,
                      weil es dort ja keinen Schreibschutz gibt):<br>
                      <tt>chmod a+rw php.ini</tt>",
        "engine" => "PHP kann in den einzelnen Unterverzeichnissen
                      aktiviert und deaktiviert werden;",
        "expose_php" => "Wegen Sicherheitsbedenken möchten einige
                      Leute nicht verraten, daß PHP benutzt wird...
                      Diese Option zu setzen, macht natürlich nur Sinn,
                      wenn man nicht anhand der Dateinamen ohnehin ablesen kann,
                      daß PHP verwendet wird (mit ParseExt oder mod_rewrite,
                      mod_multiviews könnte man dies jedoch verschleiern).",
        "short_open_tag" => "PHP-code wird in das XML-Tag
                      <nobr><tt><b>&lt;?php ... ?&gt;</b></tt></nobr>
                      eingeschlossen, normalerweise möchte man aber auch
                      die Kurzform (ohne das »php« nach dem Fragezeichen) verwenden
                      <tt>&lt;?...?&gt;</tt> ", 
        "asp_tags" => "ASP-ähnliche Tags werden eher selten verwendet <tt>&lt;%...%&gt;</tt> ",
        "include_path" => "Eine Liste mit Ordnern in denen nach
                  include() Dateien gesucht werden soll (Ordner mit
                  Doppelpunkten trennen; bzw. Semikolon unter Windows)",
        "auto_prepend_file" => "Die folgenden Dateien sollen automatisch am
                  Anfang und am Ende jedes PHP-Scriptes eingefügt werden
                  (nützlich um allgemeine Server-spezifische Laufzeit-Einstellungen
                  vorzunehmen).",
        "allow_url_fopen" => "PHP erlaubt es mit den normalen Datei-
                  Ein-/Ausgabefunktionen auch auf entfernte Dateien 
                  mit http:// and ftp:// (lesend) zuzugreifen. Sehr nützlich,
                  aber u.U. ein Sicherheitsloch bei schlecht durchdachten
                  Scripten.",
        "doc_root" => "Die folgenden Einstellungen dienen hauptsächlich
                  der Kompatibilität. Wer nanoweb verwendet, sollte sich
                  hierum nicht kümmern müssen.",
        "@-Variables" => "PHP war ursprünglich als die einfachste und für die
                  Webentwicklung schnellste Scriptsprache ersonnen worden,
                  allerdings wurden aus Sicherheitsgründen einige Änderungen
                  im Verhalten der Sprache eingeführt. Mit folgenden Optionen
                  kann jedoch die Kompatibilität mit früheren Versionen
                  wieder hergestellt werden:",
        "register_globals" => "Wenn Du alle GET, POST und COOKIE Variablen
                  automatisch im globalen Namensraum verfügbar haben möchtest
                  (andernfalls sind die Variablen über \$_REQUEST[] oder \$_GET[]
                  erreichbar), solltest Du folgende Option aktivieren,",
        "varialbes_order" => "Vorzugsreihenfolge für Get-, Post-, Cookie-,
                  Session- und Umgebungs-variablen:",
        "register_argc_argv" => "\$argc und \$argv sind eigenlich nur in
                  eigenständigen (nicht-www) Scripten nützlich (CLI):",
        "precision" => "Gleitkommagenauigkeit",
        "magic_quotes_gpc" => "Atkivierte »magic quotes« führen dazu,
                  daß Meta-Zeichen (Anführungszeichen, Backslash) in allen
                  Variablen, die von \"außen\" kommen oder die nach außen wandern,
                  automatisch geschützt werden (durch vorangestellten Backslash):",
        "@-Output" => "Alle Ausgaben (also alle nicht-PHP-Bereiche oder
                  echo()-Aufrufe) können zwischengespeichert (gepuffert)
                  werden und/oder durch eine spezielle Bearbeitungsfkt.
                  gesendet werden.",
        "output_buffering" => "Du kannst die Puffergröße zur
                  Ausgabeverzögerung selber festlegen (»On« steht für 4096 Byte).
                  Ein solcher Zwischenspeicher erlaubt es u.a. weitere
                  header() zu senden obwohl bereits Ausgaben gemacht wurden.",
        "zlib.output_compression" => "An Stelle einer der allgemeinen
                  Ausgaberoutinen (das Feld hierüber) kann auch die oft
                  bevorzugte komprimierte Übertragung der HTML-Seite
                  aktiviert werden:",
        "implicit_flush" => "Wenn ein Ausgabebehandler aktiviert ist,
                  soll der Puffer automatisch nach jeden echo()
                  geleert werden?",
        "safe_mode" => "Der »Sichere Modus« von PHP erlaubt es die
                  Verfügbarkeit von bestimmten Systemfunktionen zu beschränken
                  und auch den Zugriff auf nicht-eigene Dateien zu verhindern.",
        "safe_mode_gid" => "Neben dem Eigentümer soll beim Zugriff auf eine
                  Datei auch die Gruppenzugehörigkeit überprüft werden
                  (GID - »group id« unter Linux/UNIX).",
        "safe_mode_include_dir" => "Eigentümer/Gruppe wird für folgende
                  Ordner ignoriert:",
        "disable_functions" => "Die folgende Einstellung funktioniert
                  unabhängig von »Safe Mode«:",
        "enable_dl" => "Die dl() Funktion erlaubt es Erweiterungsmodule
                  noch während der Laufzeit nachzuladen:",
        "@-Errors/Log" => "Fehlermeldungen werden unterdrückt oder umgeleitet
                 abhängig von folgenden Einstellungen:",
        "error_reporting" => "Dieser Wert kann später auch in jedem Script
                 mit error_reporting() übergangen werden. Einige Fehler
                 treten jedoch bereits beim Laden auf (z.B. Parser-Fehler),
                 und werden daher immer angezeigt, wenn sie hier nicht
                 deaktiviert sind.
                 E_ALL, E_ERROR, E_WARNING, E_PARSE, E_NOTICE,
                 E_COMPILE_ERROR, E_CORE_ERROR sind nur einige der Optionen
                 die hier mit | oder + kombiniert werden können:",
        "display_errors" => "Die Fehlermeldungen gleich in die
                 erstellten Seiten mitauszugeben ist sehr hilfreich bei der Entwicklung,
                 aber nicht empfohlen auf fertigen Internetpräsenzen:",
        "html_errors" => "Fehler mit HTML Auszeichnungen hervorheben
                 (in aller Regel roter Text, was aber in der php.ini
                 eingestellt werden kann).",
        "error_log" => "Fehler-Logbuch soll ins »<b>syslog</b>«
                 (gibt es auch unter NT, nicht jedoch im Windows) oder in
                 die hier angegebene Datei geschrieben werden:",
        "memory_limit" => "Der Ausführung von Scripten können im Speicherverbrauch
                 und der benötigten Ausführungszeit Grenzen gesetzt werden.",
        "post_max_size" => "Zu große POST Anfragen können den Server zum
                  Absturz bringen, und führen relativ häufig zu einer
                  erheblichen Verlangsamung der Ausführung.",
        "from" => "Anonyme FTP Übertragungen erfordern die Angabe
                 einer EMail-Adresse an Stelle eines Paßwortes:",
        "y2k_compliance" => "(nicht wirklich nützlich) ",
        "extension" => "Viele der PHP Erweiterungen sind inzwischen direkt in den
                 Interpreter einkompiliert, so daß nur noch einige wenige expilizit geladen
                 werden müssen."
   );


?>