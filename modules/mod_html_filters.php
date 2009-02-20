<?php

  # HTML Filters
  # ¯¯¯¯¯¯¯¯¯¯¯¯
  #  Filter =  text/html  shrink          # converts to one-line html file
  #  Filter =  text/html  downcase        # strtolower(tags)
  #  Filter =  text/html  wap             # html->wml conversion if requested by client
  #  Filter =  dont/use!  garbage
  #
  #  <mario@erphesfurt·de>


class mod_html_filters {

	function mod_html_filters() {

		$this->modtype = "pfilter_registration";
		$this->modname = "HTML Filters";

		register_filter("shrink", new filter_shrink(), NW_PFILTER_ALL);
		register_filter("downcase", new filter_downcase(), NW_PFILTER_ALL);
		register_filter("wap", new filter_wap(), NW_PFILTER_ALL);
		register_filter("garbage", new filter_garbage(), NW_PFILTER_ALL);
		
	}
}


class filter_shrink extends pfilter {
	function filter_func(&$lf, $args) {
		$lf = preg_replace('/>\s+</ms', '><', $lf);
		if (stristr('<pre', $lf) === false) {
			$lf = preg_replace('/\s+/m', ' ', $lf);
		}
		$this->content_length = strlen($lf);
}	}


class filter_downcase extends pfilter {
        function filter_func(&$lf, $args) {
		$lf = preg_replace('/<([-_\/:.\w\d]+)(.*?)>/me',
                   '"<" . strtolower("\\1").
                   $this->downcase_attrs("\\2") . ">"', $lf);
		$this->content_length = strlen($lf);
        }
	function downcase_attrs($string) {
		return(
			preg_replace('/(\s[a-zA-Z]+)(?:(=)([^"\'][^\s]*|"[^">]*?"|\'[^\'>]*?\'))?/me',
			'preg_replace("/\s+/m", " ", strtolower("\\1\\2")) .
                         $this->addquotes("\\3")', $string)
		);
	}
	function addquotes($string) {
		if (($string[0] != '"') && ($string[0] != "'")) {
			return('"' . htmlentities($string) . '"');
		}
		else {
			return($string);
		}
}	}


class filter_wap extends pfilter {
	function filter_func(&$wml, $args) {
		global $out_contenttype, $out_add_headers, $htreq_headers;
		if (strpos($out_contenttype, 'html') !== false)
                if ( (strpos($htreq_headers['ACCEPT'], 'application/vnd.wap.wml') !== false)
                  || (strpos($htreq_headers['ACCEPT'], 'text/wml') !== false))
		{
			preg_match('#^(.*)<body[^>]*>(.*)</body#i', $wml, $uu);
			if ($uu[2]) { $wml = $uu[2]; }
			if (preg_match('#<title[^>]*>(.+)<#i', $uu[1], $uu)) {
				$title = $uu[1];
			} else { $title = "untitled document"; }

			$from_orig = array('br', '/tr', 't[dfh]', '[h]', '/[h]', 'li', '/li',
				'[fcdgjklmnoqrvwxyz]', '/[fcdgjklmnoqrvwxyz]',
				'/*t\w+', '/*img', '/*table', '/*[uo]l', '/*input');
			$to_wml = array("<br/>", " |<br/>", " | ", "<b>", "</b><br/>", '<b>*</b> ', '<br/>',
				"<em>", "</em>");
			foreach ($from_orig as $value) { 
				$from_html[] = '#<' . $value . '[^>]*>#ims';
			}

			if (preg_match('#<title[^>]*>(.+)<#i', $wml, $uu)) {
				$title = $uu[1];
			} else { $title = "untitled document"; }
			$wml = preg_replace('#^.{0,4096}<body[^>]*?>#is', '', $wml);
			$wml = preg_replace('#</body.+$#is', '', $wml);

			$from_orig = array('br', '/tr', 't[dfh]', '[h]', '/[h]', 'li', '/li',
				'[fcdgjklmnoqrvwxyz]', '/[fcdgjklmnoqrvwxyz]',
				'/*t\w+', '/*img', '/*table', '/*[uo]l', '/*input');
			$to_wml = array("<br/>", " |<br/>", " | ", "<b>", "</b><br/>", '<b>*</b> ', '<br/>',
				"<em>", "</em>");
			foreach ($from_orig as $value) { 
				$from_html[] = '#<' . $value . '[^>]*>#ims';
			}
			$wml = preg_replace($from_html, $to_wml, $wml);
			$wml = preg_replace(
				array('/&nbsp;/', '/&auml;/', '/&ouml;/', '/&uuml;/', '/&Auml;/', '/&Ouml;/', '/&Uuml;/', '/&szlig;/', '/&shy;/', '/&[#a-z0-9]+;/'),
				array(' ', 'ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß', ' ', '?'), $wml);

			$wml = preg_replace('/(<a[^>]+>)(.+?)(<\/a>)/imse', '"\\1" . strip_tags("\\2") . "\\3"', $wml);
#			filter_downcase::filter_func($wml, "");

			$wml =
			'<?xml version="1.0" encoding="ISO-8859-1"?>' .
			'<!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.1//EN" "http://www.wapforum.org/DTD/wml_1.1.xml">' .
			'<wml>'.
			'<template><do type="prev" label="back"><prev/></do>' .
			'<do type="refresh" label="erneuern"><refresh/></do></template>' .
			'<card id="page"><p>' .
			"$wml";
			'</p></card></wml>';

			$out_add_headers["Content-Type"] = "application/vnd.wap.wml";
			$this->content_length = strlen($wml);
                }
}	}


class filter_garbage extends pfilter {
	function filter_func(&$lf, $args) {
		$lf = strtr($lf, '<>', '()');
}	}

?>