<?php

/*

  mod_emailprotect
  ================
  Hides and encodes email addresses found in served "text/html" content
  from spambots by enforcing a <form>/POST request, which automated
  spiders currently cannot perform - therefor it is believed to be secure.
  It however requires a bit server side work, and may slow down (regexs)
  your site. Beware that it only searches and encodes email addresses of
  <a href=mailto:> tags, and leaves others alone!

  Additionally it annoys the marketing mafia with the well known faked
  destination addresses.

  It is an extended (GPL) version of the email_protect plugin from
  http://erfurtwiki.sourceforge.net/.

  Mario Salzer <nanoweb.20.mario17@spamgourmet.org>

*/


class mod_emailprotect extends pfilter {

	var $modtype = "core_before_response";
	var $sig_token = "emailProtect";

	var $urls = array("/ProtectedEmail/");
	var $urlparam_encemail = "encoded_email";
	var $urlparam_nospambot = "i_am_no_spambot";
	var $urlparam_requestlv = "rl";
	var $fake_email_loop = 5;


	function mod_emailprotect() {
		$this->modname = "Email address protection (spambot hiding)";
	}



	function init() {
		register_filter("emailprotect", $this, NW_PFILTER_ALL);
	}



	function main() {

		global $lf, $out_contenttype, $real_uri;

		if (($out_contenttype == "text/html") && ("/$real_uri" != $this->urls[0]) ) {

			$this->pp = $lf;
			$this->eof = false;

			if (isset($this->pp->content_length)) $this->content_length=$this->pp->content_length;

			$lf = $this;

		}

	}



	function parser_get_output() {

		$content = "";
		while ((strlen($content) < 1<<20) && (!$this->pp->parser_eof())) {
			$content .= $this->pp->parser_get_output();
		}

		if ($this->eof = $this->pp->parser_eof()) {
			
			$this->pp->parser_close();

			if (strpos($content, "@")) {
				$this->email_protect($content);
			}

			$this->content_length = strlen($content);

		}

		return($content);
	}



	function parser_eof() {
		return($this->eof);
	}



	function email_protect(&$content) {

		$enc = $this->urls[0] . '?' . $this->urlparam_encemail . '=';
		$content = preg_replace('/(<a[^>]+href=[\'"]?)(mailto:)([^\'">]+)([^>]*>)([^<]*)/imse',
			'stripslashes("$1") . $enc . mod_emailprotect::encode("$3", 1) .
			stripslashes("$4") . mod_emailprotect::encode("$5", 0)', $content);

	}



	function _REQUEST() {
		global $htreq_headers, $htreq_content, $query_string;
		$r = array();
		if (strlen($query_string)) {
			parse_str($query_string, $r);
		}
		elseif (strstr($htreq_headers["CONTENT-TYPE"], "application/x-www-form-urlencoded")) {
			parse_str($htreq_content, $r);
		}
		return($r);
	}



	function url(&$rq_err, &$out_contenttype, &$out_add_headers) {

		$tmpl = array(
			'error_label' => "Protected Email Address",
			'error_resource' => "",
			'error_add_message' => "",
			'error_admin' => '<br><br>the <a href="' . $this->urls[0] . "?" . $this->urlparam_encemail . "=" . mod_emailprotect::encode($GLOBALS["conf"][$GLOBALS["vhost"]]["serveradmin"][0], 1) . '">adminstrator</a> of this server<br>'
		);
		$html = &$tmpl['error_resource'];

		$REQU = $this->_REQUEST();

		if ($email = @$REQU[$this->urlparam_encemail]) {

			if (empty($REQU[$this->urlparam_nospambot])) {

				$html .= "The email address you've clicked on is protected by this
form, so it won't get found by <a href=\"http://google.com/search?q=spambots\">spambots</a>
(automated search engines, which crawl the net for addresses just for the
entertainment of the marketing mafia).
<br><br><br>";
				$html .= '<form action="' . $this->urls[0] .
					'" method="POST" enctype="application/x-www-form-urlencoded" encoding="iso-8859-1">';
				$html .= '<input type="hidden" name="'.$this->urlparam_encemail.'" value="'.$email.'">';
				$html .= '<input type="checkbox" name="'.$this->urlparam_nospambot.'" value="true"> I\'m no spambot, really!<br><br>';
				$html .= '<input type="submit" name="go"></form><br><br>';

				$html .= "\n<b>spammers, please eat these:</b><br>\n";
				$html .= $this->feedbots($REQU);

			}
			else {

				$email = mod_emailprotect::encode($email, -1);

				$html .= "the email address you've clicked on is:<br>";
				$html .= '<a href="mailto:' . $email . '">' . $email . '</a>';

			}

		}

		$rq_err = 200;
		$out_contenttype = "text/html";
		($r = nw_apply_template(NW_TMPL_ERROR_PAGE, $tmpl)) or
		($r = "<HTML><BODY>$html</BODY></HTML>");

		return($r);
	}



	function encode($string, $func) {

		switch ($func) {

			case 0:  // garbage shown email address
				if (strpos($string, "mailto:") === 0) {
					$string = substr($string, 7);
				}
				while (($rd = strrpos($string, ".")) > strpos($string, "@")) {
					$string = substr($string, 0, $rd);
				}
				$string = strtr($string, "@.-_", "»·±¯");
				break;

			case 1:  // encode
				$string = mod_emailprotect::str_rot17($string);
				$string = base64_encode($string);
				$string = urlencode($string);
				break;

			case -1:  // decode
				$string = base64_decode($string);
				$string = mod_emailprotect::str_rot17($string);
				break;		 

		}

		return($string);
	}



	/* this is a non-portable string encoding fucntion which ensures, that
	 * encoded strings can only be decoded when requested by the same client
	 * or user in the same dialup session (IP address must match)
	 * feel free to exchange the random garbage string with anything else
	 */
	function str_rot17($string) {
		if (!defined("STR_ROT17")) {
			global $htreq_headers;
			$i = SERVER_STRING_V .
				  @$htreq_headers["HTTP-USER-AGENT"] .
				  @$htreq_headers["REMOTE-ADDR"];
			$i .= 'MxQXF^e-0OKC1\\s{\"?i!8PRoNnljHf65`Eb&A(\':g[D}_|S#~3hG>*9yvdI%<=.urcp/@$ZkqL,TWBw]a;72UzYJ)4mt+ V';
			$f = "";
			while (strlen($i)) {
				if (strpos($f, $i[0]) === false) {
					$f .= $i[0];
				}
				$i = substr($i, 1);
			}
			define("STR_ROT17", $f);
		}
		return(strtr($string, STR_ROT17, strrev(STR_ROT17)));
	}



	function feedbots($REQU=0) {
		$html = "";
		srand(time()/17-1000*microtime());

		#-- spamtraps, and companys/orgs fighting for spammers rights
		$domains = array("@spamassassin.taint.org", "@123webhosting.org",
			"@e.mailsiphon.com", "@heypete.com", "@ncifcrf.gov",
			"@riaa.com", "@whitehouse.gov", "@aol.com", "@microsoft.com");
		$traps = explode(" ", "blockme@relays.osirusoft.com simon.templar@rfc1149.net james.bond@ada-france.org anton.dvorak@ada.eu.org amandahannah44@hotmail.com usenet@fsck.me.uk meatcan2@beatrice.rutgers.edu heystupid@artsackett.com listme@dsbl.org bill@caradoc.org spamtrap@spambouncer.org spamtrap@woozle.org gfy@spamblocked.com listme@blacklist.woody.ch tarpit@lathi.net");
		$word_parts = explode(" ", "er an Ma ar on in el en le ll Ca ne ri De Mar Ha Br La Co St Ro ie Sh Mc re or Be li ra Al la al Da Ja il es te Le ha na Ka Ch is Ba nn ey nd He tt ch Ho Ke Ga Pa Wi Do st ma Mi Sa Me he to Car ro et ol ck ic Lo Mo ni ell Gr Bu Bo Ra ia de Jo El am An Re rt at Pe Li Je She Sch ea Sc it se Cha Har Sha Tr as ng rd rr Wa so Ki Ar Bra th Ta ta Wil be Cl ur ee ge ac ay au Fr ns son Ge us nt lo ti ss Cr os Hu We Cor Di ton Ri ke Ste Du No me Go Va Si man Bri ce Lu rn ad da ill Gi Th and rl ry Ros Sta sh To Se ett ley ou Ne ld Bar Ber lin ai Mac Dar Na ve no ul Fa ann Bur ow Ko rs ing Fe Ru Te Ni hi ki yn ly lle Ju Del Su mi Bl di lli Gu ine do Ve Gar ei Hi vi Gra Sto Ti Hol Vi ed ir oo em Bre Man ter Bi Van Bro Col id Fo Po Kr ard ber sa Con ick Cla Mu Bla Pr Ad So om io ho ris un her Wo Chr Her Kat Mil Tre Fra ig Mel od nc yl Ale Jer Mcc Lan lan si Dan Kar Mat Gre ue rg Fi Sp ari Str Mer San Cu rm Mon Win Bel Nor ut ah Pi gh av ci Don ot dr lt ger co Ben Lor Fl Jac Wal Ger tte mo Er ga ert tr ian Cro ff Ver Lin Gil Ken Che Jan nne arr va ers all Cal Cas Hil Han Dor Gl ag we Ed Em ran han Cle im arl wa ug ls ca Ric Par Kel Hen Nic len sk uc ina ste ab err Or Am Mor Fer Rob Luc ob Lar Bea ner pe lm ba ren lla der ec ric Ash Ant Fre rri Den Ham Mic Dem Is As Au che Leo nna rin enn Mal Jam Mad Mcg Wh Ab War Ol ler Whi Es All For ud ord Dea eb nk Woo tin ore art Dr tz Ly Pat Per Kri Min Bet rie Flo rne Joh nni Ce Ty Za ins eli ye rc eo ene ist ev Der Des Val And Can Shi ak Gal Cat Eli May Ea rk nge Fu Qu nie oc um ath oll bi ew Far ich Cra The Ran ani Dav Tra Sal Gri Mos Ang Ter mb Jay les Kir Tu hr oe Tri lia Fin mm aw dy cke itt ale wi eg est ier ze ru sc My lb har ka mer sti br ya Gen Hay a b c d e f g h i j k l m n o p q r s t u v w x y z");
		$word_delims = explode(" ", "0 1 2 3 3 3 4 5 5 6 7 8 9 - - - - - - - _ _ _ _ _ _ _ . . . . . . .");
		$n_dom = count($domains)-1;
		$n_trp = count($traps)-1;
		$n_wpt = count($word_parts)-1;
		$n_wdl = count($word_delims)-1;

		for ($n = 1; $n < $this->fake_email_loop; $n++) {
			$m = "";
			while (strlen($m) < rand(3,17)) {
				$a = $word_parts[nat_rand($n_wpt)];
				if (!empty($m)) {
					$a = strtolower($a);
					if (rand(1,9)==5) {
						$m .= $word_delims[rand(0,$n_wdl)];
					}
				}
				$m .= $a;
			}
			
			$dom = $domains[rand(0, $n_dom)];
			
			if ($dom=="@123webhosting.org") {
			
				// Where 123webhosting.org spamtrap gets special treatment ;)

				$m = str_replace(".", "-", $GLOBALS["remote_ip"])."-".$GLOBALS["host"]."-".date("U");

			}
			
			$m .= $dom;
			$html .= '<a href="mailto:'.$m.'">'.$m.'</a>'.",\n";
		}

		$html .= '<a href="mailto:'.$traps[rand(0, $n_trp)].'">'.$traps[rand(0, $n_trp)].'</a>';

		if (($rl = 1 + @$REQU[$this->urlparam_requestlv]) < $this->fake_email_loop) {
			$html .= ",\n" . '<br><a href="' . $this->urls[0] .
					'?' . $this->urlparam_encemail . "=" . mod_emailprotect::encode($m, 1) .
					'&' . $this->urlparam_requestlv . "=$rl" .
					'"><b>generate more faked email addresses</b></a><br>' . "\n";
			($rl > 1) && sleep(3);
		}

		sleep(1);
		return($html);
	}

}





	function nat_rand($max, $dr=0.5) {
		$x = $max+1;
		while ($x > $max) {
			$x = rand(0, $max * 1000)/100;
			$x = $x * $dr + $x * $x / 2 * (1-$dr) / $max;
		}
		return((int)$x);
	}



?>
