<?php

/*

Nanoweb Status Report Module
============================

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

class mod_status {

	function mod_status() {

		global $conf;
		
		$this->modtype="url";
		$this->modname="Server status report";
		$this->urls=array("/server-status");

	}

	function url(&$rq_err, &$out_contenttype, &$out_add_headers) {

		global $stats_start, $stats_cnx, $stats_hits, $stats_modlist, $stats_resperr, $stats_vhosts, $stats_xfer, $stats_rej, $remote_ip, $remote_host, $modules, $conf, $logger_pids, $scoreboard, $mypid, $query_string, $htreq_headers, $banned_ips;
		
		if ($allow=$conf["global"]["statusallowhost"]) for ($a=0;$a<count($allow);$a++) if ((strpos($remote_host, $allow[$a])!==false) || (strpos($remote_ip, $allow[$a])!==false)) $host_allowed=true;
		
		$t = time();
		
		if ($host_allowed) {
		
			$rq_err=200;
			
			if ($query_string=="who") {

				$out_contenttype="text/plain";
				
				$c=count($scoreboard);
				$s=($c?$c:"no")." active servers";
				$st["status"]["active_servers"]=$c;

				if ($c) {
				
					$s.="\n\n";
					
					foreach ($scoreboard as $pid=>$st_arr) {
						
						$s.=str_pad($pid, 5, " ", STR_PAD_LEFT)." - ".str_pad($this->ts2str($t - $st_arr[NW_SB_FORKTIME]), 7, " ", STR_PAD_LEFT)." - ".$st_arr[NW_SB_PEERHOST]."\n     -> ".$st_arr[NW_SB_STATUS]."\n";

						$pst["pid"]=$pid;
						$pst["peer_host"]=$st_arr[NW_SB_PEERHOST];
						$pst["status"]=$st_arr[NW_SB_STATUS];
						$pst["uptime"]=$t - $st_arr[NW_SB_FORKTIME];

						$st["who"][]=$pst;

					}
					
				}
			
			} else {
			
				if (is_callable("memory_get_usage")) {

					$im = memory_get_usage();
					$um = (int)($im/1024)." KB";
				
				} else {
					
					$um = false;

				}
				
				$out_contenttype="text/html";

				$startd=gmdate("D, d M Y H:i:s T", $stats_start);
				$uptime=time()-$stats_start;
				
				$upstr=$this->ts2str($uptime);
				
				$nst=nw_server_string();
				
				$s="<center>Nanoweb server status module</center><br>";
				$s.="<br>";
				$s.="Server  : <b>".$nst."</b><br>";
				$s.="Started : <b>".$startd."</b><br>";
				$s.="Uptime  : <b>".$upstr."</b><br>";
				
				if ($um) $s.="Memory  : <b>".$um."</b><br>";

				$s.="<br>";

				$st["status"]["server_string"]=$nst;
				$st["status"]["started_str"]=$startd;
				$st["status"]["uptime_str"]=$upstr;
				$st["status"]["started"]=$stats_start;
				$st["status"]["uptime"]=$uptime;

				if ($im) $st["status"]["memory_usage"] = $im;
				
				$acs=(count($scoreboard)+1);
				
				if (!$conf["global"]["singleprocessmode"][0]) {
				
					$s.="Active servers : <b>".$acs."</b>/".((int)$conf["global"]["maxservers"][0]?(int)$conf["global"]["maxservers"][0]:"-")." (";

					$st["status"]["active_servers"]=$acs;
					$st["status"]["max_active_servers"]=(int)$conf["global"]["maxservers"][0];
					
					unset($tmp);
					foreach ($scoreboard as $pid=>$dummy) $tmp[]=$pid;
					$tmp[]=$mypid;
					$s.=implode(" ", $tmp);
					$st["status"]["servers_pid"]=$tmp;
						
					$s.=")<br>";
				
				}
				
				if ($conf["global"]["loggerprocess"][0]) {

					$acl=count($logger_pids);
					
					$s.="Active loggers : <b>".$acl. "</b>/".(int)$conf["global"]["loggerprocess"][0]." (";

					$st["status"]["active_loggers"]=$acl;
					$st["status"]["configured_loggers"]=(int)$conf["global"]["loggerprocess"][0];

					if ($c=count($logger_pids)) {
						
						unset($tmp);
						while (list($pid, $lgid)=each($logger_pids)) $tmp[]="#".$lgid."=".$pid;
						$s.=implode(" ", $tmp);
						$st["status"]["loggers_pid"]=array_keys($logger_pids);
					
					}

					$s.=")<br>";
				
				}

				$s.="<br>";
				$s.="Total hits/connections : <b>".($stats_hits+1)."/".($stats_cnx)."</b> (avg <b>".sprintf("%.2f", (($stats_hits/$uptime)*60))."</b>/m <b>".sprintf("%.2f", ($stats_hits/$uptime))."</b>/s)<br>";

				$st["stats"]["total_hits"]=($stats_hits+1);
				$st["stats"]["total_connections"]=$stats_cnx;

				$s.="Total sent size        : <b>".number_format(sprintf("%.1f", $stats_xfer/1024))."</b> KB (avg <b>".sprintf("%.2f", ((($stats_xfer/1024)/$uptime)*60))."</b> KB/m <b>".sprintf("%.2f", (($stats_xfer/1024)/$uptime)*8)."</b> Kbit/s)<br>";

				$st["stats"]["total_sent_bytes"]=$stats_xfer;

				$s.="<br>";

				if (strpos($query_string, "detailed")!==false) {

					$s.="Loaded modules<br><br>";

					for ($a=0;$a<count($stats_modlist);$a++) {
						
						$s.=str_pad($stats_modlist[$a][0], 18)." - ".$stats_modlist[$a][1]."<br>";

						$mst["module_name"]=$stats_modlist[$a][0];
						$mst["module_desc"]=$stats_modlist[$a][1];
						$st["modules"][]=$mst;

					}
					
					$s.="<br>";
					
					$tresp=$stats_resperr;
					ksort($tresp);
					
					$s.="HTTP responses statistics<br><br>";
					
					foreach ($tresp as $key=>$val) {
						
						$s.=str_pad($GLOBALS["HTTP_HEADERS"][$key], 26)." : <b>".$val."</b><br>";
						$tst["response_code"]=$key;
						$tst["hits"]=$val;
						$st["responses"][]=$tst;

					}

					$s.="<br>";

					$bln_str=", ".(int)$stats_rej." total rejected connections";
					
					if (($nbip=count($banned_ips))==0) {

						$s.="No blocked IP address".($nbip>1?"es":"").$bln_str."<br><br>";
					
					} else {
						
						$s.=$nbip." blocked IP addresses".$bln_str."<br><br>";
						
						$s.="Source               Address          Type (expires)                 Rej<br>-------------------- ---------------- ------------------------------ -------<br>";
						
						foreach ($banned_ips as $addr=>$bip) {

							$bip["address"]=$addr;
							$s.=str_pad($bip["source"], 20)." ".str_pad($addr, 16)." ".str_pad($bip["type"].($bip["type"]=="TEMP"?(" (".date("Y-m-d H:i:s", $bip["expires"]).")"):""), 30)." ".(int)$bip["rejects"]."<br>";
							$st["blocked_ips"][]=$bip;

						}

						$s.="<br>";
					
					}

				}

				if (strpos($query_string, "vstats")!==false) {

					$s.="Virtual hosts statistics<br><br>";

					$sv=$stats_vhosts;
					arsort($sv);

					foreach ($sv as $vh=>$hits) {

						if (!$hmax) $hmax=$hits or $hmax=1;
						$s.=str_pad(substr($vh, 0, 20), 20, " ", STR_PAD_LEFT)." ".str_pad("", (int)($hits/$hmax*50), "#")." ".$hits."<br>";

						$vst["name"]=$vh;
						$vst["hits"]=$hits;

						$st["vhosts"][]=$vst;
					
					}

					$s.="<br>";
				
				}

				$s.="<center>The server is working properly</center>";

				if (strpos($htreq_headers["USER-AGENT"], "Lynx")===0) {

					$out_contenttype="text/plain";
					$s=strip_tags(str_replace("<br>", "\n", $s));
				
				}

			}
		
	
		} else {

			// Return 403 Forbidden if hit is not coming from allowed host
			
			$rq_err=403;
			$s="no access";

		}

		if (strpos($query_string, "php-serialize")!==false) {

			$out_contenttype="text/plain";
			$s=serialize($st);
		
		} else if (strpos($query_string, "wddx")!==false) {

			if (is_callable("wddx_packet_start")) {
			
				$wd=wddx_packet_start("nanoweb mod_status");
				wddx_add_vars($wd, "st");
				$s=wddx_packet_end($wd);
		
			} else {

				$s="wddx support not built in PHP";
			
			}
		
		} else if (strpos($query_string, "xml")!==false) {

			$out_contenttype="text/xml";
			$s=$this->arr_to_xml($st, "nanoweb_mod_status");
		
		}

		return($s);
	
	}

	function ts2str($timestamp) {

		$up_d=(int)($timestamp/86400);
		$up_h=((int)($timestamp/3600))%24;
		$up_m=((int)($timestamp/60))%60;
		$up_s=$timestamp%60;

		$tstr=($up_d?($up_d."d, "):"").(($up_h||$up_d)?($up_h."h, "):"").(($up_m||$up_h||$up_d)?($up_m."m, "):"").$up_s."s";

		return ($tstr);
	
	}
	
	function arr_to_xml($arr, $rootname="php_array", $lvl=0) {

		$fill=($lvl+1)*4;
		for ($a=0;$a<$fill;$a++) $filler.=" ";
		
		if ($lvl==0) {

			$s="<"."?xml version=\"1.0\"?".">\n\n<$rootname>\n\n";
		
		}
		
		foreach ($arr as $key=>$val) {

			if (is_int($key)) {
				
				$skey_o="element id=\"".$key."\"";
				$skey_c="element";
				
			} else {
				
				$skey_o=$skey_c=$key;

			}
			
			if (is_array($val)) {

				$s.=$filler."<$skey_o>\n\n".$this->arr_to_xml($val, false, $lvl+1).$filler."</$skey_c>\n\n";

			} else {

				$s.=$filler."<$skey_o>$val</$skey_c>\n\n";
			
			}
		
		}
	
		if ($lvl==0) {

			$s.="</$rootname>\n";
		
		}
	
		return($s);

	}

}

?>
