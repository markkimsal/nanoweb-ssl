<?php

/*

Nanoweb TRACE HTTP method handler
=================================

Copyright (C) 2002-2003 Vincent Negrier aka. sIX <six@aegis-corp.org>

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2, or (at your option)
any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.

*/

class mod_method_TRACE {

	var $modtype="method";
	var $modname="TRACE method support";
	var $methods=array("TRACE");
	
	function parser_open($args, $filename, &$rq_err, &$cgi_headers) {

		$rq_err=200;
		$cgi_headers["Content-Type"]="message/http";
		$this->parsed_output=$GLOBALS["http_rq_block"];
		$this->content_length=strlen($GLOBALS["http_rq_block"]);

	}

	function parser_get_output() {

		$tmp=$this->parsed_output;
		$this->parsed_output="";
		return($tmp);
	
	}
	
	function parser_eof() {

		return($this->parsed_output==="");
	
	}
	
	function parser_close() {

	}

	function options() {

		return($this->methods);
	
	}

}

?>
