<?php


class mod_asis extends mod_static {


	function mod_asis() {

		$this->modtype = "parser_asis";
		$this->modname = "message/http (.asis files) support";
	
	}


	function parser_open($args, $filename, &$rq_err, &$cgi_headers) {

		global $conf, $htreq_headers;

		parent::parser_open($args, $filename, $rq_err, $cgi_headers);

		if ($this->fp) {

			// open secondary (non-bsafe) file pointer
			$this->fp_C = @fopen($filename, "r");

			// read .asis http headers from start of file
			fseek($this->fp_C, 0);

			while ($hline = trim(fgets($this->fp_C))) {

				list($h, $v) = explode(":", $hline, 2);

				$cgi_headers[$h] = ltrim($v);
				
			}

			// current position == start of content;
			$this->begin = ftell($this->fp_C);

			// close C-safe file pointer
			fclose($this->fp_C);

			// adjust some values
			if (($this->rng_to += $this->begin) > $this->content_length) {
				$this->rng_to = $this->content_length;
			}
			$this->content_length -= $this->begin;
			$this->rng_from += $this->begin;

//echo "begin=".$this->begin."\ncontent_length=".$this->content_length."\nrng_from=".$this->rng_from."\n";
			// go to real content start in fp
			fseek($this->fp, $this->rng_from);
			$this->cur_ptr=$this->rng_from;

		}

	}

}

?>
