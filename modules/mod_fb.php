<?php

/*

Nanoweb Files and Directories Browser Module
============================================

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

define("NW_TMPL_FB_HEADER", "fb_header");
define("NW_TMPL_FB_FOOTER", "fb_footer");
define("NW_TMPL_FB_PARENT", "fb_parent_name");
define("NW_TMPL_FB_ROW_D", "fb_directory_row");
define("NW_TMPL_FB_ROW_F", "fb_file_row");

class mod_fb {

	function mod_fb() {

		$this->modtype="core_directory_handler";
		$this->modname="Files and directories browser";

	}

	function main() {

		global $http_uri, $docroot, $conf, $vhost, $rq_err, $out_contenttype, $real_uri, $out_add_headers, $accessdir, $mime, $query_string;

		foreach (access_query("fbiconbytype") as $icndef) {

			$ic=explode(" ", $icndef);
			$icons[trim($ic[1])]=trim($ic[0]);
		
		}

		$icndef=access_query("fbicondefault", 0);
		if (!($icndir=access_query("fbicondirectory", 0))) $icndir=$icndef;

		if ($http_uri[strlen($http_uri)-1]!="/") $http_uri.="/";

		if (access_query("filebrowser", 0)) {

			if (@is_readable($docroot.$http_uri)) {
			
				$dfmt=access_query("fbdateformat", 0)
				or $dfmt="d-M-Y H:i:s";
				
				$rq_err=200;
				$out_contenttype="text/html";
				
				// Generate directory listing
				
				$hnd=opendir(realpath($docroot.$http_uri));

				unset($fb_arr);
				unset($fsort);
				
				while ($f=readdir($hnd)) {

					$fi=stat($docroot.$http_uri.$f);
					$fi["isdir"]=is_dir($docroot.$http_uri.$f);
					$fi["f"]=$f;

					$fb_arr[$f]=$fi;

					if (!$fi["isdir"]) {
					
						$fb_ts+=$fi[7];
						$fb_tf++;

					}
				
				}

				if ($fbstmp=access_query("fbsortorder", 0)) {

					$fbsort=explode(" ", $fbstmp);
					
				} else {
					
					$fbsort=array("name");

				}

				parse_str($query_string, $ptmp);

				if (count($ptmp)) {

					if ($ptmp["sort"]) $fbsort[0]=$ptmp["sort"];
					if ($ptmp["order"]) $fbsort[1]=$ptmp["order"];

				}
			
				switch ($fbsort[0]) {

					case "date": $sortidx=9;
					break;

					case "size": $sortidx=7;
					break;

					case "name":
					default: $sortidx="f";
			
				}

				$dsort = $fsort = array();
				
				foreach ($fb_arr as $fstmp) if (!$fstmp["isdir"]) {
					
					$fsort[$fstmp["f"]] = $fstmp[$sortidx];

				} else {

					if ($fstmp["f"] != "..") {
						
						$dsort[$fstmp["f"]] = $fstmp[$sortidx];

					} else {

						$has_parent = $fstmp[$sortidx];
					
					}
				
				}

				if ($fbsort[1]=="desc") {
					
					arsort($fsort);
					arsort($dsort);

				} else {
					
					asort($fsort);
					asort($dsort);

				}
				
				if ($has_parent) {

					$dsort = array_reverse($dsort);
					$dsort[".."] = $has_parent;
					$dsort = array_reverse($dsort);
				
				}
				
				// Do other processing
				
				if (@is_readable($wfn=$docroot.$http_uri.$conf[$vhost]["fbwelcomefile"][0])) {

					$wfc=implode("<br>", file($wfn));
					$welcome_formated="<br><font size=\"1\" face=\"fixedsys\">".$wfc."</font><br><br>";
				
				} else $welcome_formated="";
				
				$fhdr=array();
				$fhdr["dir_name"]="/".$real_uri;
				$fhdr["welcome"]=$welcome_formated;
				$fhdr["total_files"]=$fb_tf;
				$fhdr["total_files_formated"]=number_format($fb_tf);
				$fhdr["total_size"]=$fb_ts;
				$fhdr["total_size_formated"]=number_format($fb_ts);
				
				$resp=nw_apply_template(NW_TMPL_FB_HEADER, $fhdr);
				
				$dfile=access_query("fbdescfile", 0);
				
				unset($fb_desc);
				
				if (@is_readable($dfcomp=realpath($accessdir."/".$dfile))) if ($descf=file($dfcomp)) foreach ($descf as $dfline) if (trim($dfline)) {

					$didx=trim(substr($dfline, 0, strpos($dfline, " ")));
					$desc=trim(substr($dfline, strpos($dfline, " ")));

					$fb_desc[$didx]=$desc;

				}

				// Display each row
				
				foreach (array_keys($dsort) as $fidx) {

					$fi=$fb_arr[$fidx];
					$f=$fi["f"];
					
					if ($f=="..") {
						
						$dname=nw_apply_template(NW_TMPL_FB_PARENT, array());

						$tmpdl=explode("/", trim($real_uri,"/"));
						array_pop($tmpdl);
						$dlink=url_to_absolute(implode("/", $tmpdl)."/");

					} else {
						
						$dname=$f;
						$dlink=url_to_absolute($real_uri.rawurlencode($f)."/");

					}
					
					if (((substr($f, 0, 1)!="." || $f=="..") || $conf[$vhost]["fbshowdotfiles"][0]) && ($f!=".") && !($f==".." && $http_uri=="/")) {
						
						$d_row=array();
						$d_row["icon"]=$icndir;
						$d_row["link"]=$dlink;
						$d_row["name"]=$dname;
						$d_row["date"]=date($dfmt, $fi[9]);
						$d_row["desc"]=($fb_desc[$f]?$fb_desc[$f]:"-");
						
						$resp.=nw_apply_template(NW_TMPL_FB_ROW_D, $d_row, true);

					}
						
				}
				
				foreach (array_keys($fsort) as $fidx) {

					$fi=$fb_arr[$fidx];
					$f=$fi["f"];
					$fp=pathinfo($f);
					$t=$mime[strtolower($fp["extension"])];

					$icnf=$icndef;
					
					if ($icons) foreach ($icons as $key=>$val) if (strpos($t, $key)===0) {

						$icnf=$val;
						break;

					}
					
					if ((($f[0]!="." || $f=="..") || $conf[$vhost]["fbshowdotfiles"][0]) && ($f!=".") && !($f==".." && $http_uri=="/")) {
						
						$f_row=array();
						$f_row["icon"]=$icnf;
						$f_row["link"]=url_to_absolute(($real_uri).rawurlencode($f));
						$f_row["name"]=$f;
						$f_row["date"]=date($dfmt, $fi[9]);
						$f_row["size"]=number_format($fi[7]);
						$f_row["desc"]=($fb_desc[$f]?$fb_desc[$f]:"-");
						
						$resp.=nw_apply_template(NW_TMPL_FB_ROW_F, $f_row, true);
						
					}

				}

				closedir($hnd);
				
				$resp.=nw_apply_template(NW_TMPL_FB_FOOTER, $fhdr);

			} else {

				$rq_err=403;
			
			}
		
		} else $rq_err=404;

		if ($resp) $GLOBALS["lf"] =& new static_response($resp);

	}

}

?>
