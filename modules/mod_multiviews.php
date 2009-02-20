<?php

/*

	MultiViews
	¯¯¯¯¯¯¯¯¯¯
	content-negotiation for nanoweb

	suggestions and bugfixes to <mario@erphesfurt·de>

*/



class mod_multiviews {



	function mod_multiviews() {

		$this->modtype="core_after_decode";
		$this->modname="MultiViews (content negotiation)";

		$GLOBALS["mime_enc"]["gz"] = "gzip";
		$GLOBALS["mime_enc"]["Z"] = "compress";

		$this->negotiate_features = array(
			 "javascript" => "javascript",
			 "js" => "javascript",
			 "java" => "java",
			 "flash" => "flash",
			 "tables" => "tables",
			 "graphic" => "graphic",
			 "textonly" => "textonly"
		);

	}




		# fetches extensions that match the given mime glob
		#

	function extensions($string = "text/*") {
		global $mime;

		$extensions = array();

		$compare_part = (strrpos($string, '*'));
		$string = rtrim($string, '*');

		foreach ($mime as $ext => $mimetype) {
			if ($compare_part && (strpos($mimetype, $string) === 0)
				 || !$compare_part && ($mimetype == $string))
			{
				$extensions[] = $ext;
			}
		}

		return($extensions);
	}




		# returns array with quality values indexed by
		# file name extensions

	function parseQualities($qstring, $q=0.92, $divide_q=1.05, $set_no_q=false) {

		$qualities = array();

		foreach (explode(',', $qstring) as $qspart) {

			@list($mime, $options) = explode(';', $qspart, 2);

			if ($mime = trim($mime)) {


				//-- mime type or country code?
				if (strrpos($mime, '/') <= 0) {
					list($ext) = explode('_', $mime);
					$ext = array($ext);
				}
				else {
					$ext = $this->extensions($mime);
				}

				//-- extract options  (q=NN; level=NN; ...)
				foreach (@explode(";", @$options) as $option) {

					@list($qsarg, $new_q) = @explode('=', trim(@$option));

					if ( ($qsarg[0] == "q") && !empty($new_q) ) {
						$q = trim($new_q);
					}
				}

				//-- append fetched extensions to qualities array
				foreach ($ext as $add) {
					if (empty($qualities[$add])) {
						$qualities[$add] = $q;
					}
				}

				//-- q value for next round
				$q /= $divide_q;
				if ($set_no_q) {
					$q = $set_no_q;
				}
			}
		}

		return($qualities);
	}







	function main() {

		global $http_uri, $rq_file, $conf, $docroot, $htreq_headers, $mime, $mime_enc, $out_add_headers, $pri_err;
		if (access_query("reflectrewriting", 0)) { global $real_uri; }

		// quick skip
		$enabled = strtolower(trim(access_query("multiviews", 0)));
		if ( ($enabled === "0") || ($enabled == "off") || file_exists($docroot.$http_uri) && !is_dir($docroot.$http_uri) ) { return; }

		// determine file name to be worked on
		$file = $http_uri;
		if ($last_slash = strrpos($file, "/")) {
			$act_path = substr($file, 0, $last_slash + 1);
			$file = substr($file, $last_slash + 1);
		}
		if ($file) {
			$allowed_filenames = array($file);
		}
		else {
			$allowed_filenames = explode(" ", access_query("directoryindex", 0));
		}

		// find files with same basename and different extensions
		$alternative_files = array();
		$dir = opendir($docroot . $act_path);
		while($filename = readdir($dir)) {
			foreach ($allowed_filenames as $filebn) {
				if (! $filebn) { continue; }
				$filebn .= ".";
				if (substr($filename, 0, strlen($filebn)) == $filebn) {
					$alternative_files[] = $filename;
				}
			}
		}
		closedir($dir);

		if (empty($alternative_files)) {
			return(2);
		}  // quick skip2

#print_r($alternative_files);

		// fetch priorities
		$qualities = array_merge(
			array(  /* fallback file extensions */
				"php" => 0.75, "shtml" => 0.72, "html" => 0.71, "xhtml"=> 0.70,
				"png"  => 0.33, "jpeg" => 0.32, "gif"  => 0.31
			),
			$this->parseQualities(implode(", ", access_query("otherpriority")), 1.3, 1.1),
			$this->parseQualities(@$htreq_headers["ACCEPT-FEATURES"], 1.15, 1),
			$this->parseQualities(strtr(access_query("languagepriority", 0), " ", ","), 0.8, 1.2),
			$this->parseQualities(@$htreq_headers["ACCEPT-LANGUAGE"], 1, 1.03),
			$this->parseQualities(@$htreq_headers["ACCEPT"], 1, 1.02),
			$this->parseQualities((strpos(@$htreq_headers["ACCEPT-ENCODING"], "gzip") !== false) ? "gz" : "", 1.5, 1)
		);

		// other algorithm flags
		$accept_all = strpos(@$htreq_headers["ACCEPT"], "*/*") !== false;
		$http10 = $GLOBALS['http_version'] < "1.1";
		$agent_negotiate = (strpos($htreq_headers["NEGOTIATE"], "vlist") !== false)
			|| (strpos($htreq_headers["NEGOTIATE"], "trans") !== false);
		
		// will contain variants and their attributes
		$alternates = array();

		// go thru filename extensions, and sum qualities
		foreach ($alternative_files as $filename) {

			$q_mime = -1;
			$q_enc = $q_lang = $q_features = $q_else = +1;

			$file_extensions = array_slice(explode('.', $filename), 1);
			foreach ($file_extensions as $ext) {

				$ext_q = $qualities[$ext];
				if (empty($ext_q) && ($ext_q !== 0)) { if ($accept_all) { $ext_q = 0.1; } else { $ext_q = 0.001; } }

				if (@$mime[$ext]) {
					$alternates[$filename]["type"] = $mime[$ext];
					$q_mime += $ext_q + ($q_mime < 0 ? +1 : +0);
				}
				elseif (@$mime_enc[$ext]) {
					$alternates[$filename]["encoding"] = $mime_enc[$ext];
					$q_enc *= $ext_q;
				}
				elseif (strlen($ext) == 2) {
					$alternates[$filename]["language"] = $ext;
					$q_lang = $ext_q;
				}
				else {
					$q_else *= 0.9;
				}
				if ($feature = $this->negotiate_features[$ext]) {
					$alternates[$filename]["feature"] .= " $feature;+1.2-0.9";
					$q_features *= ($qualities[$feature]) ? 1.2 : 0.9;
					$q_else /= 0.9;
				}

			}

			if ($q_mime < 0) { $q_mime = 0.005; }

			$alternates[$filename]["q"] = ($q_mime * $q_enc * $q_lang * $q_features * $q_else);

		}

		// sort
		uasort($alternates, 'mod_multiviews_uarsort_by_q');

#print_r($alternates);

		// return selected variant
		list($file) = array_keys($alternates);
		$real_uri = $http_uri = $act_path . $file;
		$rq_file = pathinfo($http_uri);

		// fallback output
		$out_add_headers["TCN"] = "adhoc";
		$out_add_headers["Vary"] = "negotiate";

		// server-driven negotiation
		if ( !$agent_negotiation )
		{
			$out_add_headers["Content-Location"] = $file;
			if (!$http10) { $out_add_headers["TCN"] = "choice"; }

			$out_add_headers["Vary"] = "negotiate, accept, accept-language, accept-features";

			foreach ($mime_enc as $ext => $encoding) {  // report file's encoding
				if (strpos($file, ".{$ext}")) {
					$out_add_headers["Content-Encoding"] = $encoding;
					$GLOBALS["out_encoded"] = true;
				}
			}
		}

		// agent-driven negotiation
		else {
			$out_add_headers["TCN"] = "list";

			$GLOBALS["pri_err"] = $http10 ? 200 : 300; // HTTP 300 Choose Yourself
			$GLOBALS["out_contenttype"] = "text/html";
			$GLOBALS["add_errmsg"] = "The document you requested exists in different variants, and your browser gives you the opportunity to select one of them (or just does not support transparent content negotiation):<BR><UL>";
			foreach ($alternative_files as $f => $q) {
				$GLOBALS["add_errmsg"] .= '<LI><A HREF="' . url_to_absolute($act_path . $f) . '">' . $f . '</A></LI>';
			}
			$GLOBALS["add_errmsg"] .= "</UL>";
			if ($http10) { $out_add_headers["Refresh"] = "10; URL=" . url_to_absolute($http_uri); }
		}

		// add alternates-header
		$ah = " ";
		foreach ($alternates as $filename => $a) {
			$qstr = substr($a["q"], 0, 5);
			if (strpos($qstr, ".") === false) { $qstr .= "."; }
			while (strlen($qstr) < 5) { $qstr .= "0"; }
			$ah .= '{ "' . $filename . '" ' . $qstr;
			unset($a["q"]);
			foreach($a as $desc => $value) {
				$ah .= ' {' . $desc . ' ' . $value . '}';
			}
			$ah .= " },\n	";
		}
		$ah .= "proxy-rvsa=1.0";
		$out_add_headers["Alternates"] = $ah;

		if ($GLOBALS['path_info']) {
			// may help(?)
			$out_add_headers["Content-Base"] = "/" . ($act_path ? "$act_path/" : "");
		}
		if ($http10) {
 			$out_add_headers["Vary"] = "*";
		}
		if (is_dir($docroot . $http_uri) && !is_file($docroot . $http_uri . "/index.html") || (strpos($file, ".var") !== false))
		{
			// very rare error / 506 Variant Also Negotiates
			$GLOBALS["pri_err"] = 506;
		}

	}


}




function mod_multiviews_uarsort_by_q($a, $b) {

	return( ($a["q"] < $b["q"]) ? 1 : -1 );

}


?>