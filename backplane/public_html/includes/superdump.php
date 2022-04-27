<?php
function send_chunk($chunk)
{
    // The chunk must fill the output buffer or php won't send it
    $chunk = str_pad($chunk, 4096);

    printf("%x\r\n%s\r\n", strlen($chunk), $chunk);
    flush();
}

function raw_handle(){
	
	$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://';
    if(isset($_SERVER['REQUEST_SCHEME'])){
    	$protocol= strtolower($_SERVER['REQUEST_SCHEME']).'://';
    }
	$isHttps = $protocol == "https://";
    do_error_log("HTTPS: ".$protocol);
    $req = raw_dump_http_request();
    do_error_log("REQUEST:\n".$req);
    $res = raw_send_request($_SERVER['HTTP_HOST'],$isHttps,$req);
    
    $str =hex2bin('0D0A');
    $explContent = explode($str,$res);
    $headers = explode('\n',$explContent[0]);
    
    do_error_log("RECEIVED ".$explContent[0]);
    
    for($i=1;$i<sizeof($headers);$i++){
    	do_error_log($headers[$i]);
    	header($headers[$i], true);
    }
    $isHeader = true;
    for($i=1;$i<sizeof($explContent);$i++){
    	if(strlen($explContent[$i])==0){
    		$isHeader=false;
    	}
    	if($isHeader){
    		header($explContent[$i], true);
    	}else{
    		echo $explContent[$i];
    	}
    	
    }
    
    
    /*
    if(sizeof($explContent)>2){
	    for($i=1;$i<sizeof($explContent);$i++){
	    	
			do_error_log("CHUNK ".$i);
			if($i==1){
				do_error_log($explContent[$i]);
			}
	    	echo $explContent[$i];
	    }
	    //echo "0\r\n\r\n";
	}else{
		do_error_log("RESPONSE:\n".$res);
		echo $explContent[1];
	}*/
			do_error_log("SENT");
}

function raw_dump_http_request() {

  $request = "{$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']} {$_SERVER['SERVER_PROTOCOL']}\r\n";

  foreach (getallheaders() as $name => $value) {
    $request .= "$name: $value\r\n";
  }

  $request .= "\r\n" . file_get_contents('php://input');

  return $request;
}

function raw_send_request($url,$https,$body){
	$fp=null;
	if(!$https){
		$fp = fsockopen($url, 80, $errno, $errstr, 30);
	}else{
		$fp = fsockopen('ssl://'. $url, 443, $errno, $errstr, 30);
	}
	$result ="";
	if (!$fp) {
		do_error_log("$errstr ($errno)");
	} else {
		//$out = "GET / HTTP/1.1\r\n";
		//$out .= "Host: www.example.com\r\n";
		//$out .= "Connection: Close\r\n\r\n";
		fwrite($fp, $body);
		while (!feof($fp)) {
		    $result.=fgets($fp, 4096);
		}
		fclose($fp);
	}
	return $result;
}