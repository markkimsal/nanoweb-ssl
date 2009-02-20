<?php

#
#  HTTP/1.x PUT method
#  ¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯¯
#  WriteAccess = 1
#
#  overwrites resources if permissions are set to ???????rw?
#

class mod_method_PUT {


	var $modtype = "request_method";
	var $methods = array("PUT");
	var $modname = "PUT method request support";


	function init() {

		global $HTTP_HEADERS;

		define("NW_METHOD_PUT_OVERWRITTEN", "204");   // 200 could be used as well
		define("NW_METHOD_PUT_CREATED", "201");
		
	}


	function options() {
		$t = $GLOBALS['docroot'].$GLOBALS['real_uri'];
		return(is_writeable($t)||is_writeable(dirname($t))?$this->methods:array());
	}


	function parser_open($uu_args, &$real_uri, &$rq_err, &$out_add_headers, &$out_contenttype) {

		global $lf, $htreq_headers, $htreq_content, $add_errmsg, $docroot;

		// guess final error code
		if (!file_exists($docroot.$real_uri)) {

			$rq_err = NW_METHOD_PUT_CREATED;
			$add_errmsg = "<b>Resource created.</b><br><br>";

		}
		else {

			$rq_err = NW_METHOD_PUT_OVERWRITTEN;
			$add_errmsg = "<b>Resource overwritten.</b><br><br>";

		}

		// unsupported stuff
		if (! empty($htreq_headers["CONTENT-RANGE"])) {

			$rq_err = 501;
			$add_errmsg = "Partially overwriting resources is not implemented. ";

		}

		// supported stuff
		else {

			// authentication is handled by httpd kernel
			if (!access_query("writeaccess", 0)) {

				$rq_err = 403;
				$add_errmsg = "Only wizards can do that. ";

			}
			else {
				// backup code goes here
				#...

				// try to open the file
				$put_there = fopen($docroot.$real_uri, NW_BSAFE_WRITE_OPEN);

				// if open failed
				if (! $put_there) {

					// may be we'll try ftp-method instead?
					#... (!is_writeable($docroot.$real_uri) && !trim($logged_user))

					$rq_err = 403;
					$add_errmsg = "Could not open URI for write access. ";

				}
				else {

					fwrite($put_there, $htreq_content);
					fclose($put_there);

				}
			}

		}

		// replacing ourselfes with a standard error response
		$lf = new static_response(nw_error_page($rq_err, $add_errmsg));

	}


	function parser_eof() {
		$GLOBALS["lf"] = $GLOBALS["null_response"];
		return($GLOBALS["rq_err"]=500);
	}

}

?>