<?php


  # Misc Filters
  # ¯¯¯¯¯¯¯¯¯¯¯¯
  # no content filters, but mime/.ext triggered actions:
  #
  #   Filter = .so|.exe       error  401
  #   Filter = .cgi|.pl       addservervar LD_LIBRARY_PATH=/usr/lib/perl7:/lib
  #   Filter = text/*         nocache
  #   Filter = application/*  addheader  X-Access-Rights: 755
  #
  # this one correspons to the apache "AddHandler" directive:
  #
  #   Filter = .htm           handler  /cgi-bin/lib/needs_frame.php
  #
  # <mario17@web·de>




class mod_misc_filters {

	function mod_misc_filters() {

		$this->modtype = "pfilter_registration";
		$this->modname = "Miscellaneous/Control Filters";

	}

	function init() {

		register_filter("error", new filter_control_error(), NW_PFILTER_IMMEDIATE | NW_PFILTER_MORE);
		register_filter("addheader", new filter_control_addheader(), NW_PFILTER_IMMEDIATE | NW_PFILTER_MORE);
		register_filter("nocache", new filter_control_nocache(), NW_PFILTER_IMMEDIATE);
		register_filter("addservervar", new filter_control_addservervar(), NW_PFILTER_IMMEDIATE | NW_PFILTER_MORE);
		register_filter("handler", new filter_handler(), NW_PFILTER_ALL);

	}

}



class filter_control_error extends pfilter {

	function filter_func(&$lf, $args) {
		if (($rq_err = (int) trim($args)) <= 0) {
			$rq_err = 500;
		}
		$GLOBALS["rq_err"] = $rq_err;
		$GLOBALS["lf"] = $GLOBALS["null_response"];

	}
}



class filter_control_addheader extends pfilter {

	function filter_func(&$lf, $args) {

		list($hd_key, $hd_val) = explode(":", trim($args));
		if ($hd_key && $hd_val) {
			$GLOBALS["out_add_headers"][$hd_key] = ltrim($hd_val);
		}
	}
}



class filter_control_nocache extends filter_control_addheader {

	function filter_func(&$lf, $args) {

		global $out_add_headers;
		$out_add_headers["Cache-Control"] = 
		$out_add_headers["Pragma"] = "no-cache";
	}
}



class filter_control_addservervar extends pfilter {

	function filter_func(&$lf, $args) {
		$del = strpos($args, "="); $del2 = strpos($args, " "); if ($del2<$del) {$del=$del2;}
		$GLOBALS["add_nsv"][ substr($args,0,$del) ] = substr($args,$del+1);
	}
}




//-- a "pipe" variant
class filter_handler extends pfilter {

	function parser_get_output() {

		global $docroot, $http_uri, $path_info, $rq_err,
		$out_add_headers, $pri_parser, $add_nsv, $lf;

		// mk absolute paths
		if (!($dest_handler = realpath($this->args)) && !($dest_handler = realpath($docroot . $this->args))) {
			return;
		}
		$rq_dest = pathinfo($dest_handler);
		$path_info = "/" . $http_uri;
		$http_uri = $this->args;
unset($add_nsv);
		$add_nsv["SCRIPT_FILENAME"] =
		$add_nsv["PATH_TRANSLATED"] = $dest_handler;
		$add_nsv["REDIRECT_STATUS"] = "200";
		$add_nsv["SCRIPT_NAME"] = $this->args;
		$add_nsv["REDIRECT_URL"] = $path_info;
		$add_nsv["REQUEST_URI"] = $path_info;
#print_r($add_nsv);

		// clean parent parser, as it will be removed
		if (is_object($this->pp)) {
			$this->pp->parser_close();
			$this->pp->peof = true;
		}

#print_r($this);
		// replace $this with CGI module
		$lf = loadfile($dest_handler, $rq_dest["extension"], $rq_err, $out_add_headers, $pri_parser);
#print_r($ps);
		return($lf->parser_get_output());
	}
}


?>