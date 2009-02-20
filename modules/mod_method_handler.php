<?php

#
#  HTTP actions handled by dedicated cgi's
#  ппппппппппппппппппппппппппппппппппппппп
#
#  MethodHandler = FOO /cgi-bin/FOO_handler.php
#
#  NOTE: the path name to the handler script given here
#  must be absolute to the docroot! (not as relaxed as
#  with Filter handlers)
#

class mod_method_handler {


	var $modtype = "core_after_decode";
	var $methods = array("*");
	var $modname = "httpd user space request method handlers";


	function main() {

		global $http_action;

		$this->$http_action = $http_action;
		$this->$handler = '';

		if (($http_action != "GET") && ($http_action != "POST") && ($http_action != "HEAD"))
		foreach	(access_query("methodhandler") as $m_h) {

			list($method, $handler) = explode(" ", $m_h);

			if ($method == $http_action) {

				$this->handler = $handler;
				$http_action = "*"; // we'll get called later again

			}

		}

	}


	function options() {

		$methods = array();

		foreach	(access_query("methodhandler") as $m_h) {

			$methods[] = strtok($method, " ");

		}

		return($methods);

	}


	function parser_open($pri_parser_args, &$real_uri, &$rq_err, &$out_add_headers, &$out_contenttype) {

		global $lf, $http_action, $htreq_content;
		global $docroot, $http_uri, $path_info, $pri_parser, $add_nsv;

		// remove "*" again
		$http_action = $this->http_action;

		// handler cgi needs absolute path
		if (!($handler = realpath($docroot . $this->handler))) {
			$lf = new static_response(nw_error_page($rq_err=500));
		}
		$rq_dest = pathinfo($handler);
		$path_info = "/" . $http_uri;
		$http_uri = $this->handler;
		#-- $add_nsv["SCRIPT_FILENAME"] =
		$add_nsv["PATH_TRANSLATED"] = $handler;
		$add_nsv["REDIRECT_STATUS"] = "200";
		$add_nsv["SCRIPT_NAME"] = $path_info;
		$add_nsv["REDIRECT_URL"] = $path_info;
		$add_nsv["REQUEST_URI"] = $path_info;

		// replace $this with CGI module
		return($ps = loadfile($handler, $rq_dest["extension"], $rq_err, $out_add_headers, $pri_parser));

	}



	function parser_eof() {
		die("something went really wrong");
	}


}

?>