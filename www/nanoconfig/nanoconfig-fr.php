<?php

   #-- boolean drop-downs
   $D_BOOLEAN["boolean"] = array("Non", "Oui");
   $D_BOOLEAN["boolvalue"] = array("False", "True");
   $D_BOOLEAN["boolpower"] = array("Off", "On");
   $D_BOOLEAN["boolstate"] = array("D�sactiv�", "Activ�");

   #-- translated directives help
   $directive_descriptions["NW"] = array(
        "@nanoweb" => '<img src="../nanoweblogo.gif" width="200" height="60" align="right" alt="nanoweb logo" valign="top" border="1">'."
					  Cet outil permet une configuration rapide de nanoweb.
					  Il est souvent plus facile et plus rapide d'�diter les
					  fichiers de configuration, et il est en fait impossible
					  d'acceder � toutes les options � partir de cette interface.
					  Un �diteur texte est donc necessaire pour les configurations
					  plus subtiles.
                      <br><br> 
					  Pour que cet outil fonctionne correctement, vous devez
					  rendre les fichiers de configuration accessibles en �criture.
					  Pour cel� sous Linux/UNIX : <br><TT>chmod a+rw
                      {$T[$which]['CONFIG_FILE']}</TT><br><br>
					  Ce droit peut etre r�voqu� par un clic sur le bouton 
					  'Lock Config' sur cette page une fois votre configuration
					  termin�e.<br><br>
					  Vous devez selectionner 'Save' sur chaque page sur laquelle
					  vous avez effectu� des changements.<br><br><input
                      type=\"submit\" name=\"lock\" value=\"Lock Config\">
                      <input type=\"submit\" name=\"apply\"
                      value=\"Apply Config\">",
        "@-General" => "Le serveur a besoin d'un nom de host pour reconstruire des URL
			          absolues. Vous devez utiliser ici un nom existant (voir /etc/hosts ou C:\winnt\hosts).",
        "DocumentRoot" => "La racine des documents est le repertoire de base de tous les fichiers
						qui sont accessibles � partir de nanoweb:",
        "DirectoryIndex" => "Ce fichier, si il existe, est envoy� � la place de la
								liste des fichiers du repertoire. Plusieurs noms de fichiers
								peuvent etre donn�s si ils sont s�par�s par des espaces:",
        "DefaultContentType" => "Le header Content-type renvoy� par nanoweb quand celui
								ci n'a pas pu reconnaitre le type du contenu:",
        "SingleProcessMode" => "Le mode single process est un mode de fonctionnement d�grad�
								dans lequel un seul processus prend en charge toutes les requetes HTTP.
								Ceci est necessaire sous windows par exemple, qui ne supporte pas la
								fonction fork():",
        "ServerMode" => "Une bonne connaissance des fichiers README est necessaire avant
								de changer cette option:",
        "User" => "Si vous ne voulez pas faire tourner nanoweb en tant que super utilisateur,
							laissez cette option sur sa valeur par d�faut : www-data",
        "ListenInterface" => "nanoweb a besoin de se lier � une adresse IP et � un port
					pour pouvoir accepter des connexions entrantes.",
        "ListenQueue" => "D�finit le nombre de connexions que nanoweb pourra
								garder en file d'attente.",
        "KeepAlive" => "Support des connexions HTTP persistantes. Mettez cette valeur
								� zero pour d�sactiver les connexions persistantes, ou un autre
								nombre pour d�finir le nombre maximum de requetes servies par
								session keep-alive :",
        "RequestTimeout" => "Certains navigateurs peuvent de temps en temps se d�connecter
							du serveur sans avoir envoy� de requete ou en ayant envoy� une requete
							incomplete. Cette option permet de limiter le temps d'attente maximum
							du serveur:",
        "ChildLifeTime" => "Cette option permet de donner un temps de vie maximum aux processus
								enfants (serveurs).",
        "TempDir" => "Repertoire temporaire, nanoweb doit avoir le droit d'�crire dans ce repertoire.:",
        "@-.nwaccess" => "Les noms des fichiers de controle d'access (access files) peut
						etre d�fini dans cette section:",
        "AccessFile" => "si vous voulez r�utiliser les fichiers de controle d'acces 
								de Apache par exemple, vous pouvez changer la valeur de cette
								option en �<b>.htaccess</b>� :",
        "MimeTypes" => "Nanoweb se base sur un fichier de types MIME pour d�terminer
									le type de contenu qu'il doit annoncer au navigateur", 
        "@-Logging" => "nanoweb propose plusieurs fa�ons de journaliser tous les types
						d'activit�. Si vous d�sirez utiliser pour cela une base de donn�es
						MySQL, veuillez consulter le manuel.",
        "ServerLog" => "Le ServerLog recoit toutes les informations 
								(y compris les hits):",
        "HostnameLookups" => "D�sactivez cette fonction pour gagner un peu de performances,
								ou si votre analyseur de logs permet la r�solution inverse des
								noms d'hotes:",
        "PidFile" => "Le fichier PID contient sous UNIX l'identifiant du processus.",
        "LoggerProcess" => "LoggerProcess, LoggerUser/Group ne peuvent etre d�clar�s
						que dans le fichier de configuration.",
        "LogHitsToConsole" => "Si vous d�sirez activer l'envoi des informations de log
								vers la console, <A HREF=\"".$T["NW"]["DOC"]."/mod_stdlog.html\">mod_stdlog</A> 
								doit etre charg�.",
        "ParseExt" => "La directive ParseExt d�finit quel type de parser sera
			utiliser pour des certaines extensions.",
        "AllowPathInfo" => "�pathinfo� Activation du support path_info. Ceci vous 
						permet d'utiliser des URL de type \"http://www.example.com/script/arg1/arg2\"
						au lieu de \"script.php?arg1=x&arg2=y\".",
        "PathInfoTryExt" => "Liste des extensions que nanoweb essayera en cas
			de demande d'un script avec path_ingo (permet d'utiliser /script/ au lieu de /script.php/):",
        "CGIFilterPathInfo" => "Un bug dans PHP-CGI empeche le fonctionnement optimal de scripts
			PHP sous nanoweb utilisant la variable path_info, cette directive permet de la filtrer:",
        "AllowExtSymlinks" => "Nanoweb ne permet pas l'access � des fichiers situ�s en
							dehors de la racine du site (voir DocumentRoot). Cette directive
							permet de passer outre cette limite pour les liens symboliques:",
        "IgnoreDotFiles" => "Les fichiers dont le nom commence par un point sont 
			trait�s comme invisibles par la plupart des applications Unix.:",
        "Alias" => "Utilisez la directive Alias pour assigner des noms de repertoires
						virtuels � des repertoires existants.:",
        "ErrorDocument" => "Une page d'erreur personalis�e peut etre renvoy�e � la place
								du message d'erreur standard de nanoweb:",
        "AddHeader" => "Cette directive permet d'envoyer des entetes HTTP 
								personalis�es au client:",
        "UserDir" => "Les repertoires utilisateurs sont accessibles via 
                  <b>http://www.example.com/~user/</b> si l'utilisateur en question
					a cr�� le repertoire suivant dans son home:",
        "@Modules" => "Les modules d'extensions suivants sont charg�s au d�marrage de nanoweb.
                 Reportez vous au manuel pour une vue d'ensemble des 
                 <a href=\"{$T["NW"]["DOCDIR"]}/modules.html\">modules</a>
                  disponibles pour nanoweb.",
        "GzipEnable" => "Les transferts HTTP peuvent etre acc�l�r�s par l'utilisation
						de la m�thode standard de compression �gzip� (aussi appel�e �format zlib�).
						Cette compression est support�e par tous les navigateurs recents.",
        "GzipMaxRatio" => "Ne compresser les donn�es que si le ratio de compression est assez avantageux:",
        "FileBrowser" => "Le module file browser genere un listing du repertoire quand
					aucun fichier par defaut (voir DirectoryIndex) n'a �t� trouv�. La liste
					peut etre personalis�e par plusieurs options:",
        "FBIconDirectory" => "Les repertoire /icons/ est un repertoire virtuel (voir Alias):",
        "MispellAction" => "Corrige les fautes de frappes dans les URL demand�es. Peut effectuer une
						redirection vers la page correcte, ou simplement donner un lien vers celle-ci
						dans la page d'erreur:",
        "@-MultiViews" => "Le module multiviews ajoute � nanoweb le support transparent de la n�gociation
						de contenu.",
            "LanguagePriority" => "Le langage primaire de votre site web:",
            "OtherPriority" => "Liste des priorit�s serveur, celles ci sont utilis�es quand le client n'envoie
						pas de demandes de priorit�s particulieres.",
            "ReflectRewriting" => "Egalement utilis� par mod_rewrite:",
        "@-Status" => "mod_status permet de consulter quelques <a
                 href=\"/server-status?detailed\"> informations sur l'�tat du serveur</a>
				 en temps reel.
                 Consultez le <a href=\"{$T["NW"]["DOC"]}\">manuel</a> pour une liste
				 des options support�es par mod_status.",
        "StatusAllowHost" => "Liste des addresses IP autoris�es � consulter mod_status:",
        "@-StdoutLog" => "Cette option permet d'envoyer les lignes de log de chaque hit vers
					la sortie standard (console).",
        "@-MySQL Logging" => "mod_mysqllog �crit le journal d'activit� du serveur dans une base de
			donn�es MySQL. La table correspondate est automatiquement cr��e si elle n'existe pas:",
	"@Virtual Hosts" => "Un �virtual host� (ou serveur virtuel) a un nom de serveur et un repertoire
						racine different du serveur principal (ou serveur par defaut).<br>La plupart
						des directives utilis�es plus haut pour le serveur principal sont aussi utilisables
						pour les virtual hosts.<br><br>".
                                                'Nom DNS du nouveau virtual host:<br><input size="42" name="add_vhost"> <input type="submit" value="Add" name="save"><br> '
   );

?>