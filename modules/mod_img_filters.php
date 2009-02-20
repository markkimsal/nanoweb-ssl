<?php

  # Image Filters
  # ¯¯¯¯¯¯¯¯¯¯¯¯¯
  #  Filter =  image/*    copyright  © 2002 whoever wants to
  #  Filter =  image/png  convert  jpeg
  #  Filter =  image/*    wbmp  100x60
  #
  # libgd doesn't work with .gif images anymore, so these Filter rules
  # won't do any changes to them
  #
  # <mario@erphesfurt·de>


class mod_img_filters {

	function mod_img_filters() {

		$this->modtype = "pfilter_registration";
		$this->modname = "Image Filters";

	}

	function init() {

		if (function_exists("imagecreatefromstring")) {

			register_filter("copyright", new filter_copyright(), NW_PFILTER_ALL);
			register_filter("convert", new filter_convert(), NW_PFILTER_ALL);
			register_filter("wbmp", new filter_wbmp(), NW_PFILTER_ALL);

		}
		
	}
}



class image_pfilter extends pfilter {

	function process_image() {
	}

	function filter_func(&$lf, $args) {

		global $out_contenttype, $conf;

		$mime = $out_contenttype;
		$this->img_type = strtolower(substr($mime, strpos($mime, "/") + 1));

		if ($this->img = imagecreatefromstring($lf)) {

			if ($this->img_type == "gif") {
				$this->img_type = "png";
			}

			$this->img_width = imagesx($this->img);
			$this->img_height = imagesy($this->img);

			// work on image
			$this->process_image();

			// write new image to file
			$img_tmpfile = tempnam($conf["global"]["tmpdirectory"], "nwimgfilt");

			eval("
				image{$this->img_type} ( \$this->img, \$img_tmpfile );
			");

			imagedestroy($this->img);

			if ($f = fopen($img_tmpfile, NW_BSAFE_READ_OPEN)) {

				$lf = fread($f, 1024*1024);
				fclose($f);

				$this->content_length = strlen($lf);
				$out_contenttype = "image/" . $img_type;

			}

			unlink($img_tmpfile);

		}

	}

}


class filter_copyright extends image_pfilter {

	function process_image() {
		if ($this->img_type == "jpeg") {
			$str_color = 0xFFFFFF;
		}
		else {
			$str_color = 1;
		}
		imagestring($this->img, 1, $this->img_width - 1 - 5 * strlen($this->args), $this->img_height - 8, $this->args, $str_color);
	}
}


class filter_convert extends image_pfilter {

	function process_image() {
		$this->img_type = $this->args;
	}
}


class filter_wbmp extends image_pfilter {

	function process_image() {
		global $htreq_headers;
		if (strpos($htreq_headers["ACCEPT"], "wbmp") !== false) {

			$this->img_type = "wbmp";

			@list($dx, $dy) = explode("x", $this->args);
			if (($dx > 0) && ($dy > 0)) {
				$scale = $dx / $this->img_width;
				$scale2 = $dy / $this->img_height;
				if ($scale2 < $scale) {
					$scale = $scale2;
				}
				if ($scale < 1.0) {
					$x = (int) ($this->img_width * $scale);
					$y = (int) ($this->img_height * $scale);
					$smallimg = imagecreate($x, $y);
					imagecopyresized($smallimg, $this->img, 0, 0, 0, 0, $x, $y, $this->img_width, $this->img_height);
					imagedestroy($img);
					$img = $smallimg;
					unset($smallimg);
				}
			}
		}
	}
}


?>