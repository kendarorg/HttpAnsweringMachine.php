<?php

function handleSoapRequest($result){
    $entityBody = file_get_contents('php://input');

    do_error_log("SOAP REQUEST:");
    do_error_log(var_export($entityBody,true));


    $url = $result['url'];
    $headersList = [];
    $curl = curl_init();
    $headers = [];
    $headers[]="cache-control: no-cache";
    if(isset($_SERVER['CONTENT_TYPE'])){
    	$headers[]="Content-type: ".$_SERVER['CONTENT_TYPE'];
    }
    if(isset($_SERVER['HTTP_SOAPACTION'])){
    	$headers[]="SOAPAction: ".$_SERVER['HTTP_SOAPACTION'];
    }
    if(isset($_SERVER['HTTP_AUTHORIZATION'])){
    	$headers[]="Authorization-type: ".$_SERVER['HTTP_AUTHORIZATION'];
    }
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_HEADERFUNCTION => function($curl, $header) use (&$headersList)
        {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) // ignore invalid headers
                return $len;

            $headersList[strtolower(trim($header[0]))][] = trim($header[1]);

            return $len;
        },
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        //CURLOPT_TIMEOUT => 30,
        //CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_POSTFIELDS => $entityBody,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_USERPWD => $_SERVER['PHP_AUTH_USER'] . ":" . $_SERVER['PHP_AUTH_PW'],
        CURLOPT_HEADER => true
    ));
    $result = curl_exec($curl);



    if($result === false)
    {
        handle_curl_error($curl);
        return false;
    }
    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $headers = explode("\r\n",substr($result, 0, $header_size));
    $resultText = substr($result, $header_size);
    $propagatedHeaders=[];

    foreach($headers as $header){
        $data = trim($header);
        if(strlen($data)>0) {
            if (strpos(strtolower($header), "content-type") !== false) {
                header($header, true);
                $propagatedHeaders[] = $header;
            }
        }
    }


    curl_close($curl);
    /*if($function!=null){
        call_user_func_array($function,[&$propagatedHeaders, &$resultText]);
    }*/
    echo $resultText;
}