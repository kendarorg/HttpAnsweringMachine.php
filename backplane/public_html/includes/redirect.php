<?php

$GLOBALS['hooks']=[];
$GLOBALS['pre-hooks']=[];
$GLOBALS['replace']=[];

function register_hook($path,$function){
	$GLOBALS['hooks'][]=[
		'callback'=>$function,
		'path'=>$path
	];
}

function register_replace($path,$function){
	$GLOBALS['replace'][]=[
		'callback'=>$function,
		'path'=>$path
	];
}

function register_prehook($path,$function){
	$GLOBALS['pre-hooks'][]=[
		'callback'=>$function,
		'path'=>$path
	];
}

function path_matches($regexp,$fullPath){
	$realRegexp = substr($regexp,1);
	if(str_starts_with($regexp,"@")){
		return preg_match($realRegexp,$fullPath);
	}else if(str_starts_with($regexp,"#")) {
		return str_starts_with($fullPath,$realRegexp);
	}else{
		do_error_log("WRONG REGEXP ".$regexp);
		return false;
	}
}
function fake_favicons($request_uri)
{
    if (str_ends_with($request_uri, "favicon.ico")) {
        read_file_from_disk($GLOBALS['root']."/favicon.ico","image/x-icon");
		return true;
    }
    return false;
}

function find_simple_proxy($pathOnly, $request_uri){
    $uriFounded = false;
    $result =[];
    
    $result['specialUrls']=[];
    $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://';
    if(isset($_SERVER['REQUEST_SCHEME'])){
    	$protocol= strtolower($_SERVER['REQUEST_SCHEME']).'://';
    }
    
    $ip = find_ip_dns($_SERVER['SERVER_NAME']);
    $result['url']=$protocol.$ip.$_SERVER['REQUEST_URI'];
    
    if(isset($_SERVER['HTTP_REFERER'])){
    	$result['referer']=$_SERVER['HTTP_REFERER'];
    }
    
    foreach($GLOBALS['simpleproxies'] as $simpleProxy){
    	if(str_starts_with($result['url'],$simpleProxy['src'])){
    		do_error_log("MATCHED ".$simpleProxy['dst']);
    		$result['url']=str_replace_limit(
    			$simpleProxy['src'],
    			$simpleProxy['dst'],
    			$result['url']);
    		break;
    	}
    }
    
    return $result;
}


function preprare_request_headers($result)
{
    $headers = get_request_headers(null,$result);


    $contentTypeSet = false;
    foreach ($headers as $headerContent) {
        if (str_starts_with(strtolower($headerContent), "content-type")) {
            $contentTypeSet = true;
            break;
        }

    }
    if (!$contentTypeSet && isset($_SERVER['CONTENT_TYPE'])) {
        $headers[] = "Content-type: " . $_SERVER['CONTENT_TYPE'];
    }
    $result=[];
    
    //Cleanup empty headers
    foreach($headers as $head){
    	if (str_starts_with(strtolower($head), "sec-fetch")) {
    		continue;
    	}
    	$h = explode(":",$head);
    	if(sizeof($h)==2){
    		$sub = trim($h[1]);
    		if(strlen($sub)==0){
    			do_error_log("UNSET HEADER ".$head);
    			//continue;
    		}
    	}
    	$result[]=$head;
    }
    return $result;
}

function hack_response_body($result, $contents, $host,$useVirtualHosts)
{
    /*if(isset($result['specialUrls'])) {
        foreach ($result['specialUrls'] as $key => $value) {
            $contents = str_replace($key, $value, $contents);
        }
    }
    $source_data = $result["source_data"];
    $hosted = "http://" . $host . "/" . $source_data;
    if($useVirtualHosts){
    	$hosted = "http://" . $source_data;
    	
    }
//echo $result['backend_url']." ".$hosted;die();
    $contents = str_replace($result['backend_url'], $hosted, $contents);
    /*$contents = str_replace('src="/', 'src="'.$hosted . "/", $contents);
    $contents = str_replace("src='/", "src='".$hosted . "/", $contents);
    $contents = str_replace('srcset="/', 'srcset="'.$hosted . "/", $contents);
    $contents = str_replace("srcset='/", "srcset='".$hosted . "/", $contents);
    $contents = str_replace("background:url(/", "background:url(".$hosted . "/", $contents);*/
    return $contents;
}

function do_redirect(/*$function = null, /*$callbacks=[],$useVirtualHosts =false*/ ){
	do_error_log("==================================");
	
	$serverRequestUri = $_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI'];
	

	$requestOriginal =  trim($serverRequestUri,'/,\\');
	do_error_log("ORIGINAL REQUEST: ".$requestOriginal);

	$pathOnly = trim(parse_url($serverRequestUri, PHP_URL_PATH),"\\/");

	$host = $_SERVER['HTTP_HOST'];
	$request_uri = $serverRequestUri;

    if(fake_favicons($request_uri)){
        return false;
    }

    /* KKK if(executeCallbackIfPresent($callbacks, $redirects,$serverRequestUri,$useVirtualHosts)){
        return true;
    }*/
    $result = find_simple_proxy($pathOnly, $request_uri );
    if($result == null){
        http_response_code(404);
        return false;
    }
    
    

    if(isset($_SERVER['HTTP_SOAPACTION'])){
        handle_soap($result/*, $function*/);
        return true;
    }
	
	//START STANDARD REQUEST

    $headers = preprare_request_headers($result);

    do_error_log("DESTINATION URL: ".$result['url']);
	do_error_log("METHOD: ".$_SERVER['REQUEST_METHOD']);
	do_error_log("HEADERS: ".var_export($headers,true));

	
	if(isset($_SERVER['PHP_AUTH_USER'])){
		do_error_log("WARNING: HAS PHP_AUTH_USER");
    	//curl_setopt($curl, CURLOPT_USERPWD, $_SERVER['PHP_AUTH_USER'] . ":" . $_SERVER['PHP_AUTH_PW']);
    }
	$fullPath = $_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI'];
    
	foreach($GLOBALS['pre-hooks'] as $hook){
		$path = $hook['path'];
		if(path_matches($path,$fullPath)){
			$callback = $hook['callback'];
			do_error_log("RUNNING PRE HOOK ".$path);
			$nullo = "";
			$resultCb = $callback($path,$fullPath,$headers,$nullo);
			$headers = $resultCb['headers'];
			if(isset($resultCb['block'])){
				die();
			}
			do_error_log("RUNNED PRE HOOK ".$path);
		}
	}

    $curl = curl_init( $result['url'] );
    
    $verbose=null;
    if($GLOBALS['debugcurl']==true){
	    curl_setopt($curl, CURLOPT_VERBOSE, true);
	    $verbose = fopen($GLOBALS['dalog'].'.curl', 'w+');
		curl_setopt($curl, CURLOPT_STDERR, $verbose);
	}

    setup_curl($curl, $headers);

    $resultContent = curl_exec( $curl ); # reverse proxy. the actual request to the backend server.
    
	if($resultContent === false){
        handle_curl_error($curl);
        return false;
    }

	$curlInfo = curl_getinfo($curl);
	


    curl_close( $curl ); # curl is done now
    
    if($GLOBALS['debugcurl']){
    	do_error_log("CURL SUCCESSFUL:\n".var_export($curlInfo,true));
    	fclose($verbose);
    }
	//do_error_log(var_export($contents,true));
    $resultContent = handle_100_continue($resultContent);


	$pregResult = preg_split( '/([\r\n][\r\n])\\1/', $resultContent, 2 );

	list( $header_text, $contents ) = $pregResult;


    $propagatedHeaders = setup_response_headers($header_text, $result, $host);
    do_error_log("EXTRA".var_export(getRequestHeaders_extra(),true));
    
    do_error_log("PROPAGATED: ".var_export($propagatedHeaders,true));


    $contents = hack_response_body($result, $contents, $host,true);
    
    if(str_starts_with($contents,"HTTP/1")){
    	$str =hex2bin('0D0A');
    	
    	$explContent = explode($str,$contents);//preg_split('/[\r\n]+/', $contents,-1,0);
    	do_error_log("EXTRA HEADERS: ".$explContent[0]);
    	$contents = $explContent[1];
    }
	/*$str =hex2bin('0D0A');
	$explContent = explode($str,$contents);//preg_split('/[\r\n]+/', $contents,-1,0);
	$realContent=[];
	$startedContent = false;
	for($i=0;$i < sizeof($explContent);$i++){
		$line = trim($explContent[$i]);
		if($startedContent){
			$realContent[] = $line;
		}else if(strlen($line)==0){
			$startedContent = true;
		}
	}
	$contents = implode($str,$realContent);*/
	foreach($GLOBALS['hooks'] as $hook){
		$path = $hook['path'];
		if(path_matches($path,$fullPath)){
			$callback = $hook['callback'];
			do_error_log("RUNNING HOOK ".$path);
			$resultCb = $callback($path,$fullPath,$propagatedHeaders,$contents);
			$propagatedHeaders = $resultCb['headers'];
			$contents = $resultCb['contents'];
			do_error_log("RUNNED HOOK ".$path);
		}
	}
	
	if($GLOBALS['showfullresponse'] || isset($GLOBALS['curlerror'])|| $curlInfo['http_code']>=400){
		do_error_log("CONTENT:\n".$contents);
	}else{
		do_error_log("CONTENT:\n".substr($contents,0,100));
	}
	
	//do_error_log("CONTENT: ".$contents);
    /*if(strpos($contentType,"html")!==false){
        if(strpos(strtolower($contents),"<base ")===false){
            str_replace("<head>","<head><base href='http://".$host.$source_data."'/>",$contents);
        }
    }*/
	
	/*if($function!=null){
		call_user_func_array($function,[&$propagatedHeaders, &$contents]);
	}*/
	
	foreach($propagatedHeaders as $header){
		header( $header, false );
	}

	
	//die();
	
	print $contents; # return the proxied request result to the browser
	return true;
}