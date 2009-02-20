<?php

/*

   This module allows to limit the average transfer bandwidth
   for each incoming request. This can be espacially useful
   if you would like to serve hundreds of clients over a 33.6
   connection, for example ;)

   [vhost]
     DocumentRoot = /var/www/vhost
     Bandwidth = 16K/s

   The "Bandwidth" directive can be used in [global], [vhost] and
   .htaccess. You can leave out the "/s", as this is the only
   possible time slice. The amount can be specified in bytes
   or in kilobytes (if a "K" is appended).

   You can alternatively use it as filter:
     Filter = .zip|.tgz|.iso  throttle  100K/s

   <nanoweb.20.mario17@spamgourmet.org>

*/



class mod_throttle extends pfilter {

	var $modtype = "core_before_response";


	function mod_throttle() {
		$this->modname = "download bandwidth limiting";
	}


	function init() {
		register_filter("throttle", new mod_throttle(false, $this->modname), NW_PFILTER_ALL);
	}


	function main($args="") {

		global $lf;

		if (empty($args)) {
			$args = access_query("bandwidth", 0);
		}

		if ($n = strtok(trim($args), "/")) {

			if (stristr($n, "K")) {
				$n = substr($n, 0, -1) * 1024;
			}

			$this->bandwidth = $n;
			$this->start = time() - 1;
			$this->sent = 0;
			$this->buf = '';

			$this->pp = $lf;
			$lf = $this;

			$this->content_length = $this->pp->content_length;

		}

	}


	function parser_get_output() {

		if (!isset($this->bandwidth)) {
			$this->main($this->args);
		}

		#-- fill input buffer
		if (empty($this->buf)) {
			if ($this->pp->parser_eof()) {
				return;
			}
			else {
				$this->buf .= $this->pp->parser_get_output();
			}
		}

		#-- how much to send this turn
		while ( ($write = ((time() - $this->start) * $this->bandwidth) - $this->sent) == 0 ) {
			sleep(1);
		}

		#-- output estimated amount of $buf
		$this->sent += $write;

		$tmp = substr($this->buf, 0, $write);
		$this->buf = substr($this->buf, $write);

		return($tmp);
	}


	function parser_eof() {

		return($this->pp->parser_eof() && empty($this->buf));

	}


	function parser_close() {

		parent::parser_close();
		unset($this->bandwidth);

	}

}


?>