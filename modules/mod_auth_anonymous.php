<?php

/*

Nanoweb Anonymous Auth Module
=============================

Allows users to access an restricted area, if they give there email
address.

Mario Salzer <nanoweb.20.mario17@spamgourmet.com>


Usage
=====

AuthRealm      = Please log in as %25anonymous%25 using your email address as password
AuthRequire    = ANONYMOUS
#AuthAnonymousNames = anonymous anonym guest
#AuthAnonymousNames = nobody
#AuthAnonymousSMTPcheck = 0

*/

class mod_auth_anonymous {

	function mod_auth_anonymous() {

		$this->modtype="auth_anonymous";
		$this->modname="anonymous authentication";

	}

	function auth($user, $pass, $args) {

		$allowed = array_merge(
			array(
				"anonymous"
			),
			explode(" ", implode(" ", access_query("authanonymousnames")))
		);

		$r = false;

		if (in_array($user, $allowed) && $this->preg_email($pass)) {

			if (access_query("authanonymoussmtpcheck", 0)) {

				$r = $this->check_email($pass);

			}
			else {

				$r = true;

			}

		}

		return($r);

	}



      function preg_email($string) {
         $chars = '[-_%+&#*äöüÄÖÜß\w\d]+';
         if (preg_match("/^({$chars}[.]*)+[@]({$chars}[.]?)+$/", $string)) {
            return(true);
         }
      }


      # checks via SMTP (using the VRFY command, or deceives
      # a mail delivery), if that address is valid for the
      # given server
      #

      function check_email($email) {

         $result = false;

         #-- email-Adresse in $user und $domain aufsplitten
         list($user, $domain) = explode('@', $email);

         #-- die MX-Server für $domain bestimmen
         getmxrr($domain, $mx_servers);
            $mx_servers[] = $domain;
            foreach ($mx_servers as $smtp_server) {

               #-- mit SMTP-Server verbinden
               if (! $result)
               if ($socket = fsockopen($smtp_server, 25)) {

                  #-- warten auf SMTP ready
                  socket_set_blocking($socket, false);
                  $loop = 0;
                  while (! preg_match('/^220[ ]/', fgets($socket, 2048)))
                  {  #-- Schleife, weil anfangs SMTP-Datemmüll kommen kann
                     if ($loop++ > 19999) { fclose($socket); break 2; }
                  }

                  #-- Proto
                  socket_set_blocking($socket, true);
                  $this->socket_command($socket, "HELO www.erphesfurt.de\r\n");
                  if (! ($result = preg_match('/^25/',
                      $this->socket_command($socket, "VRFY {$user}\r\n"))))
                  {
                     $this->socket_command($socket, "MAIL FROM:<trash@erphesfurt.de>\r\n");
                     $result = preg_match('/^25/',
                        $this->socket_command($socket, "RCPT TO:<{$email}>\r\n"));
                  }

                  #-- SMTP-Verbindung beenden
                  fputs($socket, "QUIT\r\n");
                  fclose($socket);
               }#if($socket)
            }#foreach($mx_servers)
         ##if(getmxrr)

         return($result);
      }

      function socket_command($socket, $cmd) {
         fputs($socket, $cmd);
         $result = fgets($socket, 2048);
         return($result);
      }


}

?>
