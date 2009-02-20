<?php

/*

   mod_proxy
   ==========
   a web proxy for nanoweb


   myrdin@planet-d.net
	
*/

/*

ProxyCacheDir     = /var/cache/nanoweb
ProxyAllowIP      = 192.168.100
ProxyDenySite     = /etc/nanoweb/badsites
ProxyDenyPopup    = /etc/nanoweb/popup.txt
ProxyDenyPub      = /etc/nanoweb/images.txt
ProxyAccessLog    = /var/log/nanoweb/mod_proxy
#if proxyuseragent is not set, the proxy will use your actual user agent
ProxyUserAgent   = aEGiS nanoweb mod_proxy
#for hide you ip, use a fake proxyuseragent, and hide your referer
ProxyAnonyme      = 1
#when in anonymous mode, use this referer and this IP
ProxyReferer      = http://dtc.fr.st/
ProxyIp           = 127.0.0.1
# 2592000 seconds = 1 month, u can put whatever you want here
ProxyCacheMaxAge  = 2592000

*/

class mod_proxy {
	
	function mod_proxy(){

		$this->modtype="core_after_decode";
        $this->modname="Nanoweb mod_proxy";

	}
	
	function main(){

		global $http_action, $http_uri, $add_errmsg, $pri_redir, $conf, $pri_err, $lf, $http_action, $query_string, $htreq_headers;
		global $out_contenttype;

		if (substr($GLOBALS["real_uri"], 0, 7)=="http://") {

			if (strpos($GLOBALS["remote_ip"], $conf["global"]["proxyallowip"][0])===0) {
			
				// A setter en fait en fonction de la réponse du serveur distant (200 le + souvent)
				$pri_err=200;
				
				if($conf["global"]["proxyanonyme"][0]==1){

					if(isset($conf["global"]["proxyuseragent"][0])) $this->user_agent = $conf["global"]["proxyuseragent"][0];
					else $this->user_agent = "aEGiS nanoweb mod_proxy";

					if(isset($conf["global"]["proxyreferer"][0])) $this->referer = $conf["global"]["proxyreferer"][0];
					else $this->referer = "http://dtc.fr.st/";

					if(isset($conf["global"]["proxyip"][0])) $this->forward = $conf["global"]["proxyip"][0];
					else $this->forward = "192.168.100.1";

				}else{

					$this->user_agent = $htreq_headers["USER-AGENT"];
					$this->referer = $htreq_headers["REFERER"];

				}

				$this->url = $http_uri;

				$this->cache_d = strtolower($conf["global"]["proxycachedir"][0]);

				$deny_access = $this->_deny_access();

				if ($deny_access == false){

					$this->url_parse = parse_url($this->url);
					$this->encoded_path = urlencode($this->url_parse['path']);
					$this->ask_path = str_replace(" ", "%20", $this->url_parse['path']);

					$this->cache_exist = false;
					$this->not_create_cache = false;
					$this->_check_cache();

					if (! $this->cache_exist){

						$this->_read_page();

						// séparer dans le buffer (ou stocker séparément?) les headers du contenu, mettre les headers 
						// dans $out_add_headers (et $out_contenttype pour le cas spécial du header content-type.
						// le contenu lui va dans $lf
						$this->_get_header($this->bufcach);

						if ($this->not_create_cache == false){

							$this->_create_cache();
						}
					}

					$this->_write_cache_log($this->url);

					$lf_str = $this->_remove_header($this->bufcach);

					if ($out_contenttype == "text/plain" || $out_contenttype == "text/html") $lf_str = $this->_deny_pub($lf_str);

					if (version_compare(VERSION, "1.9")<0) {

						// nanoweb 1.8.x and <

						$lf = $lf_str;

					} else {

						// nanoweb 1.9.x and >

						$lf = new static_response($lf_str);

					}

				}else{

					$pri_err = 500;
					$add_errmsg = "the remote page could not be processed.<BR><B>You are not allowed to see this bad sites</B><BR>";

				}

			}else{

				$pri_err = 500;
				$add_errmsg = "you are not allowed to use this proxy";

			}

		}

	}
	
	function _open_page(){
		
		global $add_errmsg, $pri_err;
		
		if(!$this->url_parse['port']) $this->url_parse['port'] = 80;

		$fp = fsockopen ($this->url_parse['host'], $this->url_parse['port'], $errno, $errstr, 10);
		
		if (!$fp){

			$add_errmsg = "the remote page could not be processed<br> reason : $errno ($errstr).<br>";
			$pri_err = 500;
		}

		return $fp;

	}

	function _read_page(){

		global $htreq_content, $http_action, $query_string, $htreq_headers, $sck_connected, $dp, $pn, $htreq_headers;
		global $mime, $rq_file, $mimetype, $conf, $out_contenttype, $pri_err;

		if ($mimetype=$mime[strtolower($rq_file["extension"])]) $out_contenttype=$mimetype; else $out_contenttype=$conf["global"]["defaultcontenttype"][0];

		if ($query_string) $query="?$query_string";

		if ($http_action == "POST" || $http_action == "PUT"){

			$command = $http_action." ".$this->ask_path.$query.$this->url_parse['fragment']." HTTP/1.0\r\n";
			$command .= "Host: ".$this->url_parse['host']."\r\n";
			$command .= "User-agent: ".$this->user_agent."\r\n";
			$command .= "Cookie: ".$htreq_headers['COOKIE']."\r\n";
			$command .= "Content-type: application/x-www-form-urlencoded\r\n";
			$command .= "Referer: ".$this->referer."\r\n";
			$command .= "Via: aEGiS nanoweb mod_proxy\r\n";
			$command .= "X-Forwarded-For: ".$this->forward."\r\n";
			$command .= "Content-Length: ".strlen($htreq_content)."\r\n\r\n";
			$command .= $htreq_content."\r\n";
 
		}else{

			$command = $http_action." ".$this->ask_path.$query." HTTP/1.0\r\nHost: ".$this->url_parse['host']."\r\n";
			$command .= "Content-type: ".$out_contenttype."\r\n";
			$command .= "Cookie: ".$htreq_headers['COOKIE']."\r\n";
			$command .= "Referer: ".$this->referer."\r\n";
			$command .= "User-agent: ".$this->user_agent."\r\n";
			$command .= "Via: aEGiS nanoweb mod_proxy\r\n";
			$command .= "X-Forwarded-For: ".$this->forward."\r\n\r\n";

		}
		
		$no_dns = false;

		@exec("nslookup -sil ".$this->url_parse['host'], $output );
		$tmp = join(" ",$output);
		if (strstr("not found",$tmp)) $no_dns = true;

		if (!$no_dns){
			
			$fp = $this->_open_page();

			if ($fp){

				fputs ($fp, $command);

				unset ($this->bufcach);

				while(! feof($fp)){

					$tmp = fread($fp, 4096);
					$this->bufcach .= $tmp;

				}

			}

		}else{

			$add_errmsg = "the remote page could not be processed.";
			$pri_err = 500;
		
		}

		$this->_close_page($fp);

	}
	
	function _close_page($fp){

		fclose($fp);

	}
	
	function _get_header($file){		
		
		global $out_contenttype, $http_action, $out_add_headers, $pri_err;
	
		$headerend = strpos($file,"\r\n\r\n");
		$headers = substr($file,0,$headerend);

		$this->not_create_cache = $this->check_pub = false;

		$tmp = explode("\r\n",$headers);
		
		for ($i=0;$i<count($tmp);$i++){
			
			if (preg_match("/content-type/i",$tmp[$i])){
				$out_contenttype = eregi_replace("content-type: ","",$tmp[$i]);
			}
			
			else if (preg_match("/date/i",$tmp[$i])){
				$xp_date = explode(":",$tmp[$i]);
				$timestamp = strtotime($xp_date[1]);
				if ($timestamp > $this->cache_date) $last_modif = true; else $this->cache_exist = false;
			}

			else if (preg_match("/set-cookie/i",$tmp[$i])){
				$out_add_headers["cookies"][] = eregi_replace("set-cookie: ","",$tmp[$i]);
 			}
			
			else if (preg_match("/WWW-Authenticate/i",$tmp[$i])){
				$realm = explode("\"",$tmp[$i]);
			}

			else if (preg_match("/^location/i",$tmp[$i])){

				$location = eregi_replace("location: ","",$tmp[$i]);
				$http_action = "GET";
				$pri_err = 302;

				if (substr($location, 0, 7)=="http://") {
					$url_parse = parse_url($location);
					$out_add_headers["Location"] = $location;
				}elseif (substr($location,0,1)=="/"){
					$out_add_headers["Location"] = $location;
				}else{
					$out_add_headers["Location"] = "/".$location;
				}

			}
			
			else if (preg_match("/X-Cache/i",$tmp[$i])){
				$this->not_create_cache = true;
			}

			else if (preg_match("/cache-control/i",$tmp[$i])){

				if (strstr($tmp[$i],"no-cache") || strstr($tmp[$i],"no-store") || strstr($tmp[$i],"private")){
					$this->not_create_cache = true;	
				}

			}

		}

	}

	function _remove_header($file){
		
		$headerend = strpos($file,"\r\n\r\n");
		if (is_bool($headerend)){
			$result = $file;
		}else{
			$result = substr($file,$headerend+4,strlen($file) - ($headerend+4));
		}

		return $result;

	}

	function _check_cache(){
		
		global $query_string;

		if (is_dir($this->cache_d."/".$this->url_parse['host'])){
			
			if ($query_string) $query = urlencode("?".$query_string);

			if (file_exists($this->cache_d."/".$this->url_parse['host']."/".$this->encoded_path.$query)){
			
				$stat_f = stat($this->cache_d."/".$this->url_parse['host']."/".$this->encoded_path);
				$this->cache_date = $stat_f[8];

				$fp = $this->_open_page();
				fputs ($fp,"GET ".$this->url_parse['path']." HTTP/1.0\r\nHost: ".$this->url_parse['host']."\r\nUser-agent: ".$this->user_agent."\r\nIf-Modified-Since: ".$this->cache_date."\r\nConnection: close\r\n\r\n");

				while (!feof($fp)){

					$tmp = fgets($fp,128);

					if (preg_match("/^last-modified/i",$tmp)){
						$xp_date = explode(":",$tmp);
						$timestamp = strtotime($xp_date[1]);
						if ($timestamp > $this->cache_date) $last_modif = true;
					}
					
					$header .= $tmp;

				}

				$this->_close_page($fp);			

				if ($last_modif){

					$this->cache_exist = false;

				}else{
					
					$time = time();
					
					if ($time > $this->cache_date + $conf["global"]["proxycachemaxage"][0] ){

						$this->cache_exist = false;

					}else{

						$cache = fopen($this->cache_d."/".$this->url_parse['host']."/".$this->encoded_path.$query,"rb");				
	
						while (!feof($cache)) {
							$this->bufcach .= fread($cache, 4096);
						}

						fclose ($cache);
	
						$this->cache_exist = true;

						$this->_get_header($this->bufcach);

					}

				}
	
			}

		}

	}
	
	function _create_cache(){
		
		global $query_string;

		if ($query_string) $query = urlencode("?".$query_string);

		mkdir($this->cache_d."/".$this->url_parse['host'], 0755);
		$cache = fopen($this->cache_d."/".$this->url_parse['host']."/".$this->encoded_path.$query,"wb");
		fwrite($cache, $this->bufcach);
		fclose($cache);

	}

	function _write_cache_log($file){
		
		$str = date("m/d H:i")." ".$file." ".$GLOBALS["remote_ip"]."\n";
		$log = fopen(strtolower($conf["global"]["proxyaccesslog"][0]));

		fputs($log,$str);
		fclose($log);

	}
	
	function _deny_pub($str){
	
		global $conf;

		$tmp = explode("\n",$str);
		$find = false;

		unset ($str);

		for ($i=0;$i<count($tmp);$i++){

			if (preg_match ("/window.open/i",$tmp[$i])){

				if (preg_match("/pub/i",$tmp[$i]) || preg_match("/ads/i",$tmp[$i])){

					$tmp[$i] = preg_replace("/((http:\/\/)|(www\.))([\S\.]+)\b/i", "http://nanoweb.si.kz/nanoweblogo.gif", $tmp[$i]);

				}else{

					if (($popup_list = strtolower($conf["global"]["proxydenypopup"][0])) && ($fp=fopen($popup_list,"r"))){

						while(!feof($fp)){

							$buf=trim(fgets($fp,256));

							if (substr($buf,0,1)!="#"){
								
								if($buf){
	
									if (stristr($tmp[$i],$buf)){
										$tmp[$i] = preg_replace("/((http:\/\/)|(www\.))([\S\.]+)\b/i", "http://nanoweb.si.kz/nanoweblogo.gif", $tmp[$i]);
										break;
									}

								}

							}

						}

					}

					fclose($fp);

				}

			}else{
				
				if (preg_match ("/src=/i",$tmp[$i])){

					if (($images_list = strtolower($conf["global"]["proxydenypub"][0])) && ($fp = fopen($images_list,"r"))) {

						while (!feof($fp)){

							$buf = trim(fgets($fp,256));
							
							if (substr($buf,0,1)!="#"){

								if($buf){

									if (stristr($tmp[$i],$buf)){
										$tmp[$i] = preg_replace("/iframe src\b/i", "filtered ad", $tmp[$i]);
										$tmp[$i] = preg_replace("/img src\b/i", "filtered ad", $tmp[$i]);
										break 1;
									}

								}

							}

						}
					
						fclose($fp);

					}

				}

			}

			$str .= $tmp[$i]."\n";

		}

		return $str;

	}

	function _deny_access(){

		global $conf;
		
		$block = false;

		if (($sites_list = strtolower($conf["global"]["proxydenysite"][0])) && ($fp = fopen ($sites_list,"r"))) {

			while (!feof($fp)){

				$buf = trim(fgets($fp, 256));
				
				if (stristr($this->url,$buf)){
					$block = true;
					break;
				}

			}

			fclose ($fp);

		}

		return $block;

	}

}

?>
