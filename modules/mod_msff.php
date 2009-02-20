<?php

# campaign against "Smart-Tags"

class mod_msff {

	var $modname = "Microsoft®-Free Fridays";
	var $modtype = "core_after_decode";

	function main() {

		global $htreq_headers, $pri_err, $lf, $add_errmsg;

		$tm = localtime(time(), true);
		$holiday=array(5=>1);
		if ($f = access_query("microsoftfree")) {
			foreach(explode(" ", implode(" ", $f)) as $n)
			$holiday[$n] = 1;
		}

		if ($holiday[$tm["tm_wday"]])
		if (strpos(($ua = $htreq_headers["USER-AGENT"]), "MSIE")
			&& (strpos($ua, "XP") || strpos($ua, "NT"))
			&& !strpos(strtolower($ua), "opera")
			&& !strpos(strtolower($ua), "oregano")	)
		{
			$pri_err = 403;
			$add_errmsg =
		            "<b>Happy <a href=\"http://davenet.userland.com/2001/06/13\">Microsoft<sup>®</sup>-Free Friday</a>!</b>\n" .
		            "<p>In support of freedom of choice in browser software, this web site is Microsoft-Free on Fridays.  Please use any browser except MSIE to access this web site today.</p>\n";
		}
	}
}

?>