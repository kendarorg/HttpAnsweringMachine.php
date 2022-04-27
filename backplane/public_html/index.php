<?php
//https://www.phpliveregex.com/

$GLOBALS['debug']=true;
$GLOBALS['debugcurl']=false;
$GLOBALS['showfullresponse']=false;
$GLOBALS['root']=__DIR__;
$GLOBALS['data_root']=__DIR__."/data";
$GLOBALS['dns_root']=__DIR__."/dns";
$GLOBALS['dns']=[
	'10.136.111.21',
	'10.110.49.21',
	'8.8.8.8',
	'8.8.4.4'
];
$GLOBALS['simpleproxies']=[];

error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('log_errors', 'On');
ini_set('error_log', $GLOBALS['data_root'].'/request.log'); 
set_time_limit(9999999);

foreach(glob($GLOBALS['root'].'/includes/*.php') as $filename){
    require_once($filename);
}

foreach(glob($GLOBALS['root'].'/redirects/*.php') as $filename){
    require_once($filename);
}
if(file_exists($GLOBALS['root'].'/redirects/redirect.json')){
	$redirects = file_get_contents($GLOBALS['root'].'/redirects/redirect.json');
	$GLOBALS['simpleproxies'] = json_decode($redirects,true);
	
}


/*raw_handle();
die();*/

initialize_data();

$pathInfo = "index";
if(isset($_SERVER['PATH_INFO'])){
	$pathInfo = sanitizePath($_SERVER['PATH_INFO']);
}

//Handle favicon
if(str_ends_with($_SERVER['REQUEST_URI'],"favicon.ico")){
	read_file_from_disk($GLOBALS['root']."/favicon.ico","image/x-icon");
	die();
}

//Setup the log for the request
$val = milliseconds();

$GLOBALS['dalog']=$GLOBALS['data_root']."/".$val.'_'.$_SERVER['HTTP_HOST']."_".$pathInfo;


$forbidden = ["html","htm","js","map","jpg","jpeg","css","ico","svg","woff2","png","gif","ttf"];

$ext = trim(strtolower(pathinfo($pathInfo, PATHINFO_EXTENSION)),".");

if(in_array($ext, $forbidden)){
	$GLOBALS['debug']=false;
	$GLOBALS['debugcurl']=false;
}
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('log_errors', 'On');
ini_set('error_log', $GLOBALS['dalog'].'.log');  // change here

do_error_log("Requested: ".$_SERVER['HTTP_HOST']."/".$_SERVER['REQUEST_URI']);

/*if(handle_options()){
	die();
}*/

    
set_xref_options();
do_redirect();
