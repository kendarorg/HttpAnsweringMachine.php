<?php


function find_ip_dns($dnsName){
	return $dnsName;
	if(file_exists($GLOBALS['dns_root']."/".$dnsName)){
		return file_get_contents($GLOBALS['dns_root']."/".$dnsName.".txt");
	}else{
		foreach($GLOBALS['dns'] as $dnsServer){
			try{
				do_error_log("Querying ".$dnsServer);
				$smallList = [$dnsServer];
				$result = @dns_get_record ($dnsName , DNS_ANY ,$smallList );

				if(!$result){
					do_error_log("Errror querying ".$dnsServer);
					continue;
				}
				foreach($result as $record){
					if(isset($record['ip'])){
						file_put_contents($GLOBALS['dns_root']."/".$dnsName.".txt",$record['ip']);
						return $record['ip'];
					}
				}
			}catch(Exception $ex){
				do_error_log("Errror querying ".$dnsServer);
			}
		}
		
	}
	return null;
}

function set_xref_options(){
	/*header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: POST, PUT, GET, OPTIONS, DELETE");
	header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With,observe");
	header("Access-Control-Max-Age: 3600");
	header("Access-Control-Allow-Credentials: true");
	header("Access-Control-Expose-Headers: Authorization");
	header("Access-Control-Expose-Headers: responseType");
	header("Access-Control-Expose-Headers: observe");*/
}

function get_request_headers($multipart_delimiter=NULL,$result=NULL) {
    $headers = array();
    $headersMap =[];
    if(isset($result["origin"])){
        $headersMap["origin"]=$result["origin"];
        array_push($headers, "Origin: ".$result["origin"]);
        do_error_log("FORCED ORIGIN: ".$result["origin"]);
    }
    /*if(isset($result["referer"])){
        $headersMap["referer"]=$result["referer"];
        array_push($headers, "referer: ".$result["referer"]);
        do_error_log("FORCED REFERER: ".$result["referer"]);
    }*/
    foreach($_SERVER as $key => $value) {
        if(preg_match("/^HTTP/", $key)) { # only keep HTTP headers
            if(preg_match("/^HTTP_HOST/", $key) == 0 && # let curl set the actual host/proxy
            preg_match("/^HTTP_ORIGIN/", $key) == 0 &&
            preg_match("/^HTTP_CONTENT_LEN/", $key) == 0 && # let curl set the actual content length
            preg_match("/^HTTPS/", $key) == 0
            ) {
                $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                if ($key){
                	if (strpos($value, 'gzip') !== false) continue;
                	if(!isset($headersMap[strtolower($key)])){
                    	array_push($headers, "$key: $value");
                    	$headersMap[strtolower($key)] = $value;
                    }
                }
            }
        } else if (preg_match("/^CONTENT_TYPE/", $key)) {

            $key = "Content-Type";

            if(preg_match("/^multipart/", strtolower($value)) && $multipart_delimiter) {
                $value = "multipart/form-data; boundary=" . $multipart_delimiter;
                if(!isset($headersMap[strtolower($key)])){
                    	array_push($headers, "$key: $value");
                    	$headersMap[strtolower($key)] = $value;
                    }
            }
            else if(preg_match("/^application\/json/", strtolower($value))) {
                // Handle application/json
                if(!isset($headersMap[strtolower($key)])){
                    	array_push($headers, "$key: $value");
                    	$headersMap[strtolower($key)] = $value;
                    }
            }
        }
    }
    if(isset($_SERVER['HTTP_AUTHORIZATION'])){
        //array_push($headers, "Authorization: ".$_SERVER['HTTP_AUTHORIZATION']);
    }
    
    return $headers;
}

function handle_100_continue($contents)
{
    if (strpos($contents, "HTTP/1.1 100 Continue") !== false) {
        //echo "100";
        $contents = str_replace("HTTP/1.1 100 Continue", "", $contents);
        $next = strpos($contents,"HTTP/");
        $contents = substr($contents,$next);
        do_error_log("HTTP/1.1 100 Continue!");
    }
    if (strpos($contents, "HTTP/1.0 100 Continue") !== false) {
        //echo "100";
        $contents = str_replace("HTTP/1.0 100 Continue", "", $contents);
        $next = strpos($contents,"HTTP/");
        $contents = substr($contents,$next);
        do_error_log("HTTP/1.0 100 Continue!");
    }
    return $contents;
}

function build_domain_regex($hostname)
{
	$names = explode('.', $hostname); //assumes main domain is the TLD
	if(sizeof($names)==1){
		return preg_quote($hostname);
	}
	$regex = "";
	for ($i= 0; $i < count ($names)-2; $i++)
	{
		$regex .= '['.preg_quote($names[$i]).'.]?';
	}
	//echo var_export($names,true);
	$main_domain = $names[count($names)-2] .".". $names[count($names)-1];
	$regex .= $main_domain;
	return $regex;
}

function getRequestHeaders_extra() {
    $headers = array();
    foreach($_SERVER as $key => $value) {
        if (substr($key, 0, 5) <> 'HTTP_') {
            continue;
        }
        $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
        $headers[$header] = $value;
    }
    return $headers;
}

function setup_response_headers($header_text,  $result, $host)
{
    $headers_arr = preg_split('/[\r\n]+/', $header_text);
    
    $propagatedHeaders = [];
    
    foreach ($headers_arr as $header) {
    	$matcher = strtolower($header);
    	
    	if (preg_match('/^HTTP\//i', $matcher)||
    	preg_match('/^Sec-Fetch/i', $matcher)) {
    		continue;
    	}else if (!preg_match('/^transfer-encoding:/i', $matcher)) {
    		$propagatedHeaders[] = $header;
    	}else{
    		do_error_log("WARNING CHUNKED ENCODING!!");
    	}
    }
    return $propagatedHeaders;

    $propagatedHeaders = [];
    // Propagate headers to response.
    foreach ($headers_arr as $header) {
    	$matcher = strtolower($header);
		//Transfer-Encoding
        if (!preg_match('/^transfer-encoding:/i', $matcher)) {
            if (preg_match('/^location:/i', $matcher)) {
                # rewrite absolute local redirects to relative ones
                $header = str_replace($result['backend_url'], "/", $header);
            } else if (preg_match('/^set-cookie:/i', $matcher)) {
                # replace original domain name in Set-Cookie headers with our server's domain
                $domain_regex = build_domain_regex($result['backend_info']['host']);
                //prepared with prege_quote
                $header = preg_replace('/Domain=' . $domain_regex . '/', 'Domain=' . $host, $header);
            }
            if ($header != null && strlen($header) > 0) {
                $propagatedHeaders[] = $header;
            }
        }
    }
    return $propagatedHeaders;
}