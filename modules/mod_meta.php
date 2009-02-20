<?php

#
#  mod_meta
#  ¯¯¯¯¯¯¯¯
#  extracts meta-information from hypertext files and makes
#  http-equiv data available as standard https header inside
#  nanoweb
#
#  data from a companion .meta file is additionally appended
#  to the http headers, but these values cannot be accessed
#  by other modules via the herein supplied fetch() method
#
#  get_meta_tags() does nothing more than that, so we'll need
#  to reinvent this here to get <meta http-equiv> data
#
#  MetaFetch = Fast
#            [ Fast | Regex | no | 0 ]
#


class mod_meta {

	var $modtype = "core_after_decode";  # ???unsure???
	var $modname = "hypertext meta information support";
	var $maxhtmlhead = 16384;


	function init() {

		$GLOBALS['mime']["meta"] = "message/http";  # or "httpd/header"

	}


	function main() {

		global $docroot, $http_uri, $out_contenttype, $rq_err, $http_resp, $out_add_headers;

		if ($method = access_query("metafetch", 0)) {

			$meta = $this->fetch($docroot.$http_uri, $method);

			if (file_exists($fmeta = $docroot.$http_uri.'.meta')) {
				foreach (file($fmeta) as $line) {
					$p = strpos($line, ':');
					if ($h = substr($line, 0, $p)) {
						$meta['HTTP-EQUIV'][$h] = trim(substr($line, $p+1));
					}
				}
			}

			$out_add_headers = array_merge(
				$out_add_headers,
				$meta['HTTP-EQUIV']
			);
		}

	}



	function fetch($file, $procedure = "fast") {

		if (file_exists($file) && ((strpos($file, '.ht')!==false) || (strpos($file, '.xht')!==false))) {

			return($this->extract($file, $procedure));

		}

	}



	function extract($file, $procedure = "fast") {

		$r = array();

		if ($f = fopen($file, "r")) {     //-- no bin-safe!

			$orig = $html = "";
			$head_end = false;

			while (!feof($f) && (!$end) && (strlen($html) < $this->maxhtmlhead)) {

				$in = fread($f, 1024);
				$orig .= $in;
				$html .= strtoupper($in);

				$head_end = strpos($html, '</HEAD>');

			}

			if ($head_end) {

				switch(strtolower($procedure)) {

					case "regex":

						preg_match_all('/<META([^>]+)(HTTP-EQUIV|NAME)=(?:"([^"]+)"|\'([^\']+)\'|([^\s]+))([^>]*)>/ims',$orig,$uu);

						foreach ($uu[1] as $i=>$a) {

							$group = strtoupper($uu[2][$i]);
							if ($group == "NAME") { $group = "META"; }

							$name = $uu[3][$i] . $uu[4][$i] . $uu[5][$i];

							$rest = $uu[1][$i] . $uu[6][$i];
							preg_match('/\bCONTENT=(?:"([^"]+)"|\'([^\']+)\'|([^\s]+))/ims',$rest,$cc);

							$value = $cc[1] . $cc[2] . $cc[3];

							$r[$group][$name] = $value;

						}
					break;


					default:
					case 1:
					case "fast":

						$pos = $m = 0;
						while (($m = strpos($html, '<META', $pos)) && ($m < $head_end)) {
							$pos = strpos($html, '>', $m) + 1;
							$ev = substr($orig, $m, $pos - $m);
							$meta_class = strtok($ev, '"');
							if (strpos(strtoupper($meta_class), 'HTTP-EQUIV=')!==false) {
								$name = strtok('"');
								while ($attr = strtok('"')) {
									if (strpos(strtoupper($attr), 'CONTENT=')!==false) {
										$content = strtok('"');
										$r['HTTP-EQUIV'][$name] = $content;
									}
								}
							}
                                                }
					break;

					case "no":

				}

			}
		}
						
		return($r);

	}

}


?>