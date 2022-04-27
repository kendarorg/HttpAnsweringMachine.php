<?php


function handle_curl_error(&$curl)
{
    $error = curl_error($curl);
    $GLOBALS['curlerror']=true;
    do_error_log("ERROR: CURL ERROR " . $error);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    http_response_code($httpCode);
    curl_close($curl);
}

function setup_curl(&$curl, array $headers)
{
    //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); # follow redirects
    curl_setopt($curl, CURLOPT_HEADER, true); # include the headers in the output
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); # return output as string
    //curl_setopt($curl, CURLOPT_POSTREDIR, CURL_REDIR_POST_ALL);
    //curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    
    
	

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        curl_setopt($curl, CURLOPT_POST, true);
    } else {
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'PUT' || $_SERVER['REQUEST_METHOD'] == 'PATCH') {
        $post_data = file_get_contents("php://input");
        do_error_log("REQUEST BODY: ");
        do_error_log(var_export(explode("\r\n", $post_data), true));

        if (isset($_SERVER['CONTENT_TYPE'])) {
            if (preg_match("/^multipart/", strtolower($_SERVER['CONTENT_TYPE']))) {
                $delimiter = '-------------' . uniqid();
                $post_data = build_multipart_data_files($delimiter, $_POST, $_FILES);
                curl_setopt($curl, CURLOPT_HTTPHEADER, get_request_headers($delimiter));
            }
        }

        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    }
}