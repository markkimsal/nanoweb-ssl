<?php

/*

	RewriteEngine
	¯¯¯¯¯¯¯¯¯¯¯¯¯
	<mario@erphesfurt·de>

*/



class mod_rewrite {



	function mod_rewrite() {

		$this->modtype="core_before_decode";
		$this->modname="RewriteEngine";

	}




	function init() {

		$this->redirectiontypes = array("P" => 301, "F" => 302, "S" => 303, "T" => 307);
		define("NW_R_CHAIN", 1);
		define("NW_R_FORCED", 4);
		define("NW_R_NEGATE", 8);
		define("NW_R_COND", 64);

	}




		# fetches rewriting regexs from .htaccess file in
		# given $path

	function read_rules($path, $rules_string = '') {

		$rules = array();
		$ht_regex = "((?:[^\s]+|\\\\\s)+)";
		$accessfile = access_query("accessfile", 0);

		if (is_array($rules_string) ||
			 is_readable($path .= $accessfile)
			 && ($rules_string = file($path)))

			foreach ($rules_string as $line) {


			#-- RewriteRULE/COND
			if ((strtoupper(substr(ltrim($line), 0, 7)) == "REWRITE") &&
			preg_match("/^\s*Rewrite(Rule|Cond)\s+{$ht_regex}\s+{$ht_regex}(\s+\[(.+)\])*/i", $line, $uu)) {

				$switch = strtoupper($uu[1]);
				$r_flags = (($uu[2][0] == "!") || ($uu[3][0] == "!"))  ?  NW_R_NEGATE  :  0;
				$r_regex = ltrim($uu[2], "!");
				$r_replacement = preg_replace('/[$](\d\d?)/', '\\\\\1', ltrim($uu[3], "!"));
				$flagstr = @$uu[4];

				// defaults
				$r_modifiers = '';
				$r_eval = '';

				if (!empty($flagstr)) {

					$flagstr = substr($flagstr, $a = 1 + strpos($flagstr, "["), strpos($flagstr, "]") - $a);

					foreach( explode(",", $flagstr) as $flag ) {
						list($flag, $flagopts) = explode("=", trim($flag), 2);
						$uflag = strtoupper($flag);

						if (($uflag == "NC") || ($uflag == "NOCASE")) {
							$r_modifiers .= 'i';
						}
						elseif (($uflag == "R") || ($uflag == "REDIRECT")) { 
							if (!($rq_err=$this->redirectiontypes[strtoupper($flagopts[0])])) {
								$rq_err = 307;
								if ($flagopts > 0) {
									$rq_err = $flagopts;
								}
							}
							$r_eval .= " \$r_posteval = '\$pri_redir = url_to_absolute(\$http_uri); \$this->last = 255; \$rq_err = $rq_err;'; "; 
						}
						elseif (($uflag == "F") || ($uflag == "FORBIDDEN")) {
							$r_eval .= ' $pri_err = 403; ';
						}
						elseif (($uflag == "G") || ($uflag == "GONE")) {
							$r_eval .= ' $pri_err = 410; ';
						}
						elseif (($uflag == "N") || ($uflag == "NEXT")) {
							$r_eval .= ' $r_no = -1; ';
						}
						elseif (($uflag == "L") || ($uflag == "LAST")) {
							$r_eval .= ' $this->last = 255; ';
						}
						elseif (($uflag == "S") || ($uflag == "SKIP")) {
							if ($flagopts < 1) { $flagopts = 1; }
							$r_eval .= " \$r_no += $flagopts; ";
						}
						elseif (($uflag == "C") || ($uflag == "CHAIN") || ($uflag == "OR") || ($uflag == "ORNEXT")) {
							$r_flags |= NW_R_CHAIN;
						}
						elseif (($uflag == "T") || ($uflag == "TYPE")) {
							$r_eval .= " \$GLOBALS['out_contenttype'] = '$flagopts'; ";
						}
						elseif (($uflag == "QSA") || ($uflag == "QSAPPEND")) {
							$r_eval .= ' $QSA = $query_string ? "{$query_string}&" : ""; ';
						}
						elseif (($uflag == "P") || ($uflag == "PROXY")) {
							$r_eval .= ' global $real_uri; ';
						}
						elseif (preg_match('/^E(?:NV)?=([^:]+):(.+)$/i', $flag, $uu)) {
							$r_eval .= " putenv('{$uu[2]}={$uu[3]}'); ";
						}
						elseif (preg_match('/^H(?:EAD(?:ER)?)?=([^:]+):(.+)$/i', $flag, $uu)) {
							$r_eval .= " \$out_add_headers['{$uu[2]}'] = '{$uu[3]}'; ";
						}
						else {
							# (preg_match('/^(PT|passthrough|NS|nosubreq|NE|noescapesomething)$/i', $flag)) {
							techo("unsupported RewriteRule-flag '$flag'", NW_EL_WARNING);
						}

					}
				}

                                $r_flags |= ($r_replacement == "-") ? NW_R_FORCED : 0;

				switch ($switch) {

					case "RULE":
						$r_regex = "\255{$r_regex}\255$r_modifiers";
						break;

					case "COND":
						$r_flags |= NW_R_COND;
						$r_replacement = "\255{$r_replacement}\255$r_modifiers";
						break;
				}

				$rules[] = array($r_regex, $r_replacement, $r_eval, $r_flags);
								 
			}

		}#--if & foreach(line)

		return($rules);

	}




		# interpolates %{ENV_VARs}, %{REGEX_FROM_LAST_COND}, and
		# some others from the $teststring

	function backreferences (&$teststring, $for_cond = 0) {

		global $htreq_headers, $vhost, $conf;

		$NANOWEB_VARS = nw_server_vars();
		
		$teststring = preg_replace('/%(\d)/e', '$this->last_cond[\1]', $teststring);
		if ($for_cond) {
			$teststring = preg_replace('/[$](\d)/e', '$this->last_rule[\1]', $teststring);
		}
		$teststring = preg_replace('/%{HTTP[_: ]+([-_A-Z0-9]+?)}/ie', '@$htreq_headers[strtoupper("\1")]', $teststring);
		$teststring = preg_replace('/%{ENV[_: ]+(.+?)}/ie', '@getenv("\1")', $teststring);
		$teststring = preg_replace('/%{([-_A-Z0-9]+?)}/ie', '@$NANOWEB_VARS[strtoupper("\1")]', $teststring);

	}




		# applies regexs (of the fetched .htaccess file from
		# $act_path) to the $sub_path

	function rewrite($act_path, &$sub_path) {

		global $conf, $http_uri, $docroot, $pri_redir, $rq_err, $pri_err, $add_errmsg, $query_string, $out_add_headers;
		if (@$conf["global"]["reflectrewriting"][0]) {
			global $real_uri;
		}

		// get rules for actual directory
		$rules = $this->read_rules("$docroot$act_path");

#echo "ACT/SUB == $act_path / $sub_path\n";

		for ($r_no = 0; $r_no < count($rules); $r_no++) {


			list($r_regex, $r_replacement, $r_eval, $r_flags) = $rules[$r_no];
			$r_posteval = '';
			$r_negate = $f_flags & NW_R_NEGATE ? 1 : 0;

			// ======================================== RewriteRule ===========
			if ($r_flags ^ NW_R_COND) {

				// replaces %N's and $N's from last Rules/Conds
				$this->backreferences($r_regex, 1);
				$this->backreferences($r_replacement, 0);

				if (($r_flags & NW_R_FORCED) ||
				($r_negate XOR preg_match($r_regex, $sub_path, $this->last_rule)) )
				{

					techo("TRUERULE preg_replace(\"$r_regex\", \"$r_replacement\", \"$sub_path\");", NW_EL_DEBUG);

					if (!$r_negate) {
						$sub_path = preg_replace($r_regex, $r_replacement, $sub_path);
					}

					if (!empty($r_eval)) {
						eval($r_eval);
					}

					// did the rule add an querystring?
					if (preg_match('/[?]/', $sub_path)) {
						list($sub_path, $appended_querystring) = explode('?', $sub_path);
						$query_string = @$QSA . $appended_querystring;
                                                $QSA = "";
					}
				  
					// which nanoweb variable to put the new uri
					if ($sub_path[0] == "/") {
						$real_uri=$http_uri = $sub_path;
						$this->act = 0;
					}
					elseif (substr($sub_path, 0, 7) == "http://") {
						$pri_redir = $sub_path;
						$this->last = 255;
					}
					else {
						$real_uri  // <- makes rewritten urls visible in errormsgs
						= $http_uri = ($act_path ? "$act_path/" : "") . $sub_path;
					}

					$this->new_parts();	// collapse .. and . in path

					if (!empty($r_posteval)) {
						eval($r_posteval);
					}

				}
				elseif ($r_flags & NW_R_CHAIN) { //-- on mismatch+chain skip next rules
					while (($rules[$r_no][3] & NW_R_CHAIN) && !($rules[$r_no][3] & NW_R_COND)) {  $r_no++; }
				}

			}

			// ========================================== RewriteCond ===========
			else {

				techo("TRUECONDITION($r_regex, $r_replacement)", NW_EL_DEBUG);

				// replace $N's and %N's from last Rules/Conds
				$this->backreferences($r_regex, 1);
				$this->backreferences($r_replacement, 1);

				$cond_forced = false && ($r_flags ^ NW_R_FORCED);
				$cond_or = ($r_flags & NW_R_CHAIN);
				$r_condpattern = trim(preg_replace('/[a-z]+$/', '', $r_replacement), "\255");

				if (preg_match('/^(-[dfslFU])$/', $r_condpattern, $uu)) {
					$filename = "$docroot$actpath/$r_regex";
					switch (strtolower($uu[1])) {
					  case "-u":  $cond_match = ("" != implode("", file($r_regex))); break;
					  case "-s":  $cond_match = (filesize($filename) > 0); break;
					  case "-d":  $cond_match = (is_dir($filename)); break;
					  case "-l":  $cond_match = (is_link($filename)); break;
					  default:  $cond_match = (file_exists($filename));
					}
				}
				elseif (preg_match('/^([<>=])(.+)$/', $r_condpattern, $uu)) {
					if ($uu[2] == '""')  $uu[2] = "";  // make really empty
					switch($uu[1]) {
						case "<":  $cond_match = ($r_regex < $uu[2]); break;
						case ">":  $cond_match = ($r_regex > $uu[2]); break;
						default:  $cond_match = ($r_regex == $uu[2]);
					}
				}
				else {
					$cond_match =
					preg_match($r_replacement, $r_regex, $this->last_cond);
				}
				$cond_match = $cond_forced OR ($cond_match XOR $r_negate);
 
				// skip following RewriteConds + RewriteRule
				if ((! $cond_match) && (! $cond_or)) {
					while (($rules[$r_no][3] & NW_R_COND) && !($rules[$r_no][3] & NW_R_CHAIN)) {
						$r_no++;	// skip RewriteCond
					}
					if (!($rules[$r_no][3] & NW_R_COND)) {
						do {
							$r_no++;	// skip RewriteRules in chain
						} while (($rules[$r_no-1][3] & NW_R_CHAIN) && !($rules[$r_no][3] & NW_R_COND));
					}
					$r_no--;	// correcting, loop also counts up this var
				}

			}

			if ($this->last)  break;

		}#--foreach(rule)
	}





	function new_parts() {

		global $http_uri;

		// canonicialize $http_uri
		$http_uri = preg_replace('#[^/]+/[.][.]#', '', $http_uri);
		$http_uri = preg_replace('#/[.]+/#', '/', $http_uri);
		$http_uri = preg_replace('#^[.]+/#', '', $http_uri);

		$http_uri = ltrim($http_uri, "/");
	}





	function main() {

		global $conf, $http_uri, $docroot;

		if (!($e = access_query("rewriteengine", 0)) || (strtolower($e) == "off")) {
			return;
		}

		$this->last = false;
		$this->act = 0;
		$this->last_rule = $this->last_cond = array();

		while (($this->act = strpos($http_uri, "/", $this->act)) !== false)
		{

			// split http_uri
			$act_path = substr($http_uri, 0, ++$this->act);
			$sub_path = substr($http_uri, $this->act, 255);


			if (is_dir($docroot . $act_path)) {

				$this->rewrite($act_path, $sub_path);

			}
			else {
				break;
			}

			if ($this->last)  break;

		}

		return;

	}


}


?>