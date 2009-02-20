<?php

/*

Nanoweb URL mispell module
==========================

Original patch by myrdin, ported to module by sIX

*/

class mod_mispell {

	function mod_mispell() {

		$this->modtype="core_after_decode";
		$this->modname="Mispelled URL correction";
	
	}

	function check_d($path_arr, $dir="", $i=0) {

		global $docroot, $docroot_prefix, $url_fa;

		$handle = opendir(realpath($docroot.$dir));
		while ($f = readdir($handle)) if ($f != "." && $f != "..") $t[$f] = levenshtein($path_arr[$i], $f);
		closedir($handle);
		asort($t);
		
		while((list($rep,$val)=each($t))) if (($val>=0) && ($val<=1)) {

			if ($i+1==count($path_arr)) $url_fa[]=url_to_absolute($docroot_prefix.$dir.$rep);
			else if (is_dir($docroot.$dir.$rep)) $this->check_d($path_arr, $dir.$rep."/", $i+1);

		}

	}

	function main() {

		global $conf, $http_uri, $docroot, $add_errmsg, $pri_redir, $url_fa;

		if (is_file($docroot.$http_uri) || is_dir($docroot.$http_uri)) return(true);
		
		$this->check_d(explode("/",rtrim($http_uri, "/")));
		sort($url_fa);
		$r=count($url_fa);

		if ($r!=0) {

			switch (strtolower($conf["global"]["mispellaction"][0])) {

				case "advice":
				$add_errmsg.="You may find what you were looking for at the following location(s) : <br>";
				for($j=0;$j<$r;$j++) $add_errmsg.="<li><a href=\"".$url_fa[$j]."\"><b>".$url_fa[$j]."</b></a><br>";
				$add_errmsg.="<br>";
				break;

				case "redirect":
				$pri_redir=$url_fa[0];
				break;

			}
		
		}

	}

}

?>
