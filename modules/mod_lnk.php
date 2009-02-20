<?php

/*

   ParseExt = lnk LNK

Allows you to use .lnk files which contain just one line to redirect
the server to another file source, when accessed like /a.lnk/path_info

Such a .lnk file could contain for example:

   /home/www/other/stuff
or
   ftp://ftp3.example.com/export/www/
or
   http://www3.example.com/redirected/to/path/
or
   \\STAT3\C\Files\Export\Www3.example.com\

Note, no directory listings possible, due to the current implementation
of PHPs proto:// handler API.

Windows® visual shortcut file support is also inside.

*/


class mod_lnk {

	var $modname = ".lnk file support";
	var $modtype = "parser_LNK";

	function init() {

		global $modules;

		$modules["core_after_decode"][] = &$this;

	}


	function main() {

		global $http_uri, $docroot, $rq_file, $rq_err, $path_info;

		$this->uri = false;

		if ($this->modtype == "parser_" . trim(access_query("_parseext", "_".strtolower($rq_file["extension"])))) {

			if (! ($f = fopen($docroot.DIRECTORY_SEPARATOR.$http_uri, NW_BSAFE_READ_OPEN))) {
				$rq_err = 500;
			}

			$bin = fread($f, 2048);
			fclose($f);

			if (substr($bin, 0, 20) == "L\000\000\000\001\024\002\000\000\000\000\000\300\000\000\000\000\000\000F") {

				$lnk = $this->decode_windows_visual_shortcut($bin);
				$this->uri = $lnk["path"];  // .$lnk["file"]

			}
			else {

				list($uri, $uu) = explode("\n", $bin, 2);
				$this->uri = trim($uri);

			}

			#-- change immediately for directories
			if (!strpos($this->uri, "://") && is_dir($this->uri)) {

				$docroot = rtrim($this->uri, "/");
				$http_uri = $path_info;
				$rq_file = pathinfo($uri);
				$path_info = "";
			}
		}
	}


	function parser_open($args, $filename, &$rq_err, &$cgi_headers) {

		global $mime, $rq_file, $rq_err, $path_info, $out_contenttype;

		$this->uri .= $path_info;
		$rq_file = pathinfo($uri);

		if ($m = $mime[$rq_file["extension"]]) {
			$out_contenttype = $m;
		}
		
		$ps = new static_response(@implode("", @file($this->uri)));

		return($ps);
	}


	function parser_eof() { return(true); }
	function parser_close() { }
	function parser_get_output() { }



	function decode_windows_visual_shortcut($bin) {

		# taken from "The Windows Shortcut File Format.pdf" V1.0 as
		# reverse-engineered by Jesse Hager <jessehager@iname.com>

		if (!defined("WIN_LNK_F_ITEMLIST")) {

			define("WIN_LNK_F_ITEMLIST", 1);
			define("WIN_LNK_F_FILE", 2);
			define("WIN_LNK_F_DESC", 4);
			define("WIN_LNK_F_RELATIVE", 8);
			define("WIN_LNK_F_WORKDIR", 16);
			define("WIN_LNK_F_CMDARGS", 32);
			define("WIN_LNK_F_ICON", 64);
			define("WIN_LNK_F2_DIR", 16);

			function bread(&$bin, &$p, $bytes=4) {
				$h = bin2hex( strrev($s = substr($bin, $p, $bytes)) );
				$v = base_convert($h, 16, 10);
				$p += $bytes;
				return($v);
			}
		}

		$res = array();
		$p = 0x14;
		$fl=$res["flags"] = bread($bin,$p);
		$res["t_attr"] = bread($bin,$p);
		$p = 0x4C;

		if ($fl & WIN_LNK_F_ITEMLIST) {
			#-- don't need this
			$p += bread($bin,$p,2);
		}

		if ($fl & WIN_LNK_F_FILE) {
			#-- File Location Info
			$p0 = $p;
			$p = $p0 + 0x10;
			$p_path = $p0 + bread($bin,$p);
			$p = $p0 + 0x18;
			$p_file = $p0 + bread($bin,$p);
			$path = substr($bin, $p_path, 704);
			$path = substr($path, 0, strpos($path, "\000"));
			$file = substr($bin, $p_file, 704);
			$file = substr($file, 0, strpos($file, "\000"));
			$res["path"] = $path;
			$res["file"] = $file;
		}

		return($res);
	}

}

?>