<?php

/// mod_pfilters - parser wrapping filters
///
/// <mario@erphesfurt·de>
///
///
///  # .nwaccess:
///
///     FilterEnable = 1
///     Filter =    */*      null
///     Filter =    */*      unchunk   128
///     Filter = text/html   pipe      /usr/bin/tidy -clean -latin1 -quiet
///     Filter = text/xhtml  pipe      /usr/bin/tidy -xml -q
///     Filter =    .bak     null
///     Filter = .html|doc   null
///
///  Note: "*/*" can now be abbreviated as "*" or left out


// ------------------------------------------ std nanoweb module corp ---


class mod_pfilters {


	function mod_pfilters() {

		$this->modtype="core_before_response";
		$this->modname="Filter support";

		// this one works with static, parsed and chunked:
		register_filter("null", new pfilter(false, "filter_null"), NW_PFILTER_ALL|NW_PFILTER_MORE);

		// any external program with this one:
		register_filter("pipe", new filter_pipe(false, "piping through arbitrary filter programs"), NW_PFILTER_ALL|NW_PFILTER_MORE);

		// this one makes many other filters happy:
		register_filter("unchunk", new filter_unchunk(false, "filter to resemble chunky parser content"), NW_PFILTER_CHUNKY);

	}



	function main() {

		global $pfilters, $lf, $out_contenttype, $chunky, $keepalive, $http_version;
		$used_filters = array();

		$chunky = !isset($lf->content_length) || ($lf->content_length == NW_PLF_MAGIC);

		$hbn = basename($GLOBALS["http_uri"]);
		$hbnext = array_flip(explode(".", $hbn));

		// assign filters
 		if (access_query("filterenable", 0))
		foreach (access_query("filter") as $filter_rule) {

			// split rule into: [ mime/ext _ filter _ fargs ]
			list($mimematch, $filter_rule) = explode(" ", ltrim($filter_rule), 2);
			@list($fname, $fargs) = explode(" ", ltrim($filter_rule), 2);
			$fargs = trim($fargs);

			// check for missing mime match
			if (empty($fname) || empty($pfilters[strtolower($fname)])) {
				if ((strpos($mimematch, "/")===false) && (strpos($mimematch, "|")===false) && (strpos($mimematch, "*")===false) && (strpos($mimematch, ".")===false)) {
					$fargs = $fname . " " . $fargs;
					$fname = $mimematch;
					$mimematch = "*/*";
				}
			}

			// is filter available
			$fname = strtolower($fname);
			if (empty($pfilters[$fname])) {
				techo("filter '$fname' not available", NW_EL_ERROR);
				continue;
			}

			// match mime / extension
			@list($mime, $uu) = explode(";", $out_contenttype, 2);
			$mime = trim($mime);
			$no_match = true;
			foreach (explode("|", $mimematch) as $match) {
				$ext = ltrim($match, ".");
				if (($match == $mime) || ($match == "*/*") || ($match == "*")
				 || (strpos($match, '*')!==false) && (strpos($mime, rtrim($match, "*"))!==false)
				 || ($hbn==$match) || isset($hbnext[$ext])  )
				{
					$no_match = false;
					break;
				}
			}
			if ($no_match) { continue; }

			// most filters _may_ be used once only
			if (@$used_filters[$fname]++ && !($pfilters[$fname][1] & NW_PFILTER_MORE)) {
				continue;
			}

			// convert to parser object (this should have been done already in the core)
			if (! is_object($lf)) {
				$lf = new static_response($lf);
			}

			// real-static or parsed/chunked
			$fflags = $pfilters[$fname][1];
			if (  (NW_PFILTER_IMMEDIATE & $fflags) || (NW_PFILTER_STATIC & $fflags) && is_a($lf, "static_response")  ) {

				// filter content on the fly / immediate
				$GLOBALS["first_chunk"]=true;
				$pfilters[$fname][0]->filter_func($lf->str, $fargs);
				$lf->content_length=strlen($lf->str);

				techo("filter '$fname' run on static \$lf content", NW_EL_DEBUG);

			}

			elseif (($fflags & NW_PFILTER_ALL) >= NW_PFILTER_PARSED) {

				if ($chunky && !($fflags & NW_PFILTER_CHUNKY)) {
					continue;
				}

				// create wrapper around current $lf
				$newf = $pfilters[$fname][0];
				$newf->pp = $lf;
				$newf->args = $fargs;
				$newf->fflags = $fflags;
				$newf->content_length = $lf->content_length;
				$lf = $newf;
				unset($newf);

				techo("filter object '$fname' wrapped around current \$lf object", NW_EL_DEBUG);

			}

		}#foreach(filter_rule)

		core_modules_hook("after_pfilters");

        }#main

}




// --------------------------------------------- global register func ---

	define("NW_PFILTER_STATIC", 1);     # really static
	define("NW_PFILTER_PARSED", 2);     # simple parsed (read at once)
	define("NW_PFILTER_CHUNKY", 4);     # content is read in multiple steps
	define("NW_PFILTER_ALL", 7);
	define("NW_PFILTER_MORE", 8);       # filter can be applied more than once
	define("NW_PFILTER_IMMEDIATE", 16); # applied as 'static'-filter

        function register_filter($name, $func, $pflags=NW_PFILTER_STATIC) {

		global $pfilters;

                if (is_object($func)) {
			$pfilters[$name] = array($func, $pflags);
		}
		else {
			$pfilters[$name] = array(new pfilter($func, $func), $pflags);
                }

        }


// ---------------------------------------------- generic filter class ---


class pfilter {

	var $pp;                         // "parser parent"
	var $modtype = "pfilter";

	function pfilter($func=false, $name=false) {

		if ($func && function_exists($func)) {
			$this->real_filter_func = $func;
		}

		if (!$name) { $name = get_class($this); }
		$this->modname = $name;
        }

	function filter_func(&$lf, $args) {

		if ($func = $this->real_filter_func) {

			$func($lf, $args);
		}
        }

	function parser_open($args, $filename, $rq_err, $cgi_headers) {

		return($this->pp->parser_open($args, $filename, $rq_err, $cgi_headers));
        }

	function parser_eof() {

		return($this->pp->parser_eof());
        }

	function parser_close() {

		unset($this->content_length);
		return($this->pp->parser_close());
        }

	function parser_get_output() {

		$this->content_length = $this->pp->content_length;

		$tmp = $this->pp->parser_get_output();

		$this->filter_func($tmp, $this->args);
		techo("pfilter(" . get_class($this) .")->content_length=" . $this->content_length, NW_EL_DEBUG);

		return($tmp);
	}

}


// ---------------------------------------------------- core filters ---

class filter_pipe extends pfilter {


	function filter_func(&$lf, $f_args) {

		if ($GLOBALS["chunky"]) {
			techo("WARN: you must run the 'unchunk' filter before using 'pipe'!", NW_EL_WARNING);
			return;
		}

		list($f_prog, $f_args) = explode(" ", $f_args, 2);

		if (is_executable($f_prog)) {

			$lf_tmpfile = tempnam($conf["global"]["tempdirectory"][0], "nweb_filter.");
			$fp = fopen($lf_tmpfile, NW_BSAFE_WRITE_OPEN);
			fwrite($fp, $lf);
			fclose($fp);

			$f_cmd = $f_prog . " " . $f_args  . " < " . $lf_tmpfile;

			if ($fp = @popen($f_cmd, NW_BSAFE_READ_OPEN)) {

				$line = 0;
				while (! feof($fp)) {
					if (! ($line++)) { $lf = ""; }
					$lf .= fgets($fp);
				}

				pclose($fp);

			}
			#<off># else techo("WARN: cannot open pipe to '".$f_prog."'");

			$this->content_length = strlen($lf);

			unlink($lf_tmpfile);

		}

	}

}



class filter_unchunk extends pfilter {

	function parser_get_output() {

		global $chunky, $first_chunk;

		$tmp = "";

		$maxsize = $this->args ? $this->args * 1024 : 131072;

		while ((strlen($tmp) < $maxsize) && !($eof = $this->pp->parser_eof())) {
			$tmp .= $this->pp->parser_get_output();
		}

		$this->content_length = $this->pp->content_length;

		if ($eof && $first_chunk) {
			$chunky = false;
			$this->content_length = strlen($tmp);
		}

		return($tmp);
	}

}


?>