<?php


/*

 mod_unzip allows to access files inside a .zip archive, just be
 referencing them:
 http://localhost/path/packed.zip/subdir/index.html

 this of course only works if your PHP interpreter was compiled with
 zzlib support 

*/


class mod_unzip {

	var $modname = "transparent ZIP archive decompression";
	var $modtype = "parser_UNZIP";

	function mod_unzip() {

		if (!function_exists("zip_open")) {
			$this->modname .= " (disabled)";
			$this->modtype = "disabled";
		}

	}


	function parser_open($args, $filename, &$rq_err, &$cgi_headers) {

		global $path_info, $docroot, $add_errmsg, $rq_file, $mime, $http_uri, $pri_redir, $out_contenttype;

		if (empty($path_info)) {
			return(loadfile($docroot.$http_uri, $rq_file["extension"], $rq_err, $cgi_headers));
		}

		if (! ($this->ziphandle = zip_open($docroot.DIRECTORY_SEPARATOR.$filename))) {

			$rq_err = 500;
			$add_errmsg = "Accessed file was not in correct .ZIP format.";
		}
		else {
			$this->fp=$this->fpos=$this->size=0;

			$pi = trim($path_info, "/");
			$pi_dir = substr($path_info, strlen($path_info)-1) == "/";
			$dirname = false;
			$dirlist = array();

			while ($this->fp = zip_read($this->ziphandle)) {

				$fn = zip_entry_name($this->fp);
				$fn = ltrim(str_replace("\\", "/", $fn), "/");

				if ($fn == $pi) {
					break;
				}
				elseif (($dirname) && (strpos($fn, $dirname)===0) || ($dirname==="")){
					$is_dir = substr($fn, strlen($fn)-1) == "/";
					$fn = substr($fn, strlen($dirname));
					if (!strpos(trim($fn, "/"), "/")) {
						$dirlist[] = array("filename"=>$fn, "size"=>zip_entry_filesize($this->fp), "is_dir"=>$is_dir);
					}
				}
				elseif (($path_info == "/") || ($fn == $pi."/") && !zip_entry_filesize($this->fp)) {
					if ($pi_dir) {
						$dirname = $pi;
						$dirlist[] = array("filename"=>"..", "is_dir"=>1, "size"=>0);
					}
					else {
						$pri_redir = $http_uri . $path_info . "/";
						return;
					}
				}
			}

			if (!$this->fp) {
				if (count($dirlist) || ($dirname!==false)) {
					$rq_err = 501;
					$add_errmsg = "<h3>ZIP contents:</h3>\n";
					foreach ($dirlist as $i) {
						$add_errmsg .= ($i["is_dir"]?'[dir] ':'') . '<a href="'.$i["filename"].'">'.$i["filename"].'</a> ('.$i['size'].' bytes)<br>'."\n";
					}
					$add_errmsg .= "<br>\n";
				}
				else {
					$rq_err = 404;
				}
			}
			elseif (zip_entry_open($this->ziphandle, $this->fp, "rb")) {

				$this->size = zip_entry_filesize($this->fp);
				$this->fpos = 0;

				$http_uri .= $path_info;
				$rq_file = pathinfo($http_uri);
				($out_contenttype = $mime[strtolower($rq_file["extension"])]) or ($out_contenttype = $default_ct);

				$cgi_headers["X-Powered-By"] = "zziplib";
				$cgi_headers["Content-Length"] = $this->size;
			}
		}
	}


	function parser_get_output() {

		$buf = zip_entry_read($this->fp, 16384);

		if ($n = strlen($buf)) {

			$this->fpos += $n;
			return($buf);
		}
		else {
			$GLOBALS["rq_err"] = 500;
			$this->fpos = $this->size;
		}
	}


	function parser_eof() {

		return($this->fpos < $this->size);
	}


	function parser_close() {

		zip_entry_close($this->fp);
		zip_close($this->ziphandle);
	}

}

?>