<?php

class mod_access_rbl {

    function mod_access_rbl(){

        $this->modtype="core_after_decode";
        $this->modname="Deny IP listed in the RBL";

    }

    function main(){

        global $conf, $pri_err, $add_errmsg;

        switch (strtolower($conf["global"]["access_rbl"][0])) {

         case "web":

            $host = "www.mail-abuse.org";
            $fp = fsockopen($host,80);

            fputs($fp, "POST /cgi-bin/lookup?".$GLOBALS["remote_ip"]." HTTP/1.1\n");
            fputs($fp, "Host: $host\n");
            fputs($fp, "Content-type: application/x-www-form-urlencoded\n");
            fputs($fp, "User-Agent: MSIE\n");
            fputs($fp, "Connection: close\n\n");

            while (!feof($fp))
             $buf .= fgets($fp,128);
            fclose($fp);

            if (!strstr($buf,"does not appear on the MAPS RBL.")) $rbled=true;

            break;

         case "dns":

            $ip_xp = explode(".",$GLOBALS["remote_ip"]);

            $rbl_name = $ip_xp[3].".".$ip_xp[2].".".$ip_xp[1].".".$ip_xp[0].".blackholes.mail-abuse.org";
            if (gethostbyname($rbl_name)==="127.0.0.2") $rbled=true;

            break;

        }

		if ($rbled) {

			$pri_err=403;
			$add_errmsg="Your IP address (".$GLOBALS["remote_ip"].") is listed in the RBL.<br><br>See <a href=\"http://mail-abuse.org/rbl\"><b>http://mail-abuse.org/rbl</b></a> for more informations about why you are banned from this site, and many others.<br><br>";

		}

    }

}

?>
