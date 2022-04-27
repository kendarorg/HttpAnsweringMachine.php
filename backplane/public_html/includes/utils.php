<?php

function do_error_log($log){
	if($GLOBALS['debug']==true){
		error_log($log);
	}
}

if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}

function find_partial_in_array($what,$where){
	$what = strtolower($what);
	foreach($where as $line){
		$lower = strtolower($line);
		if(str_contains($lower,$what)){
			return $line;
		}
	}
	return null;
}

function str_replace_limit($search, $replace, $string, $limit = 1) {
    $pos = strpos($string, $search);

    if ($pos === false) {
        return $string;
    }

    $searchLen = strlen($search);

    for ($i = 0; $i < $limit; $i++) {
        $string = substr_replace($string, $replace, $pos, $searchLen);

        $pos = strpos($string, $search);

        if ($pos === false) {
            break;
        }
    }

    return $string;
}

if(!function_exists("str_starts_with")){

	function str_starts_with(string $haystack, string $needle): bool {
	    return \strncmp($haystack, $needle, \strlen($needle)) === 0;
	}

	function str_ends_with(string $haystack, string $needle): bool {
	    return $needle === '' || $needle === \substr($haystack, - \strlen($needle));
	}
}


function milliseconds() {
    $mt = explode(' ', microtime());
    return ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
}

function read_file_from_disk($src,$contentType){
	header('Content-Type: '.$contentType);
	readfile($src);
}

function initialize_data(){
	if(!is_dir($GLOBALS['data_root'])){
		mkdir($GLOBALS['data_root']);
	}
	
	if(!is_dir($GLOBALS['dns_root'])){
		mkdir($GLOBALS['dns_root']);
	}
}

function json_prettify($json)
{
    $array = json_decode($json, true);
    $json = json_encode($array, JSON_PRETTY_PRINT);
    return $json;
}

function sanitizePath($str,$pre=false)
{
	$str = str_replace("/","_",$str);
	$str = str_replace("'","",$str);
	$str = str_replace("\"","",$str);
	$str = str_replace("\\","",$str);
	$str = str_replace("\"","",$str);
	$str = str_replace("`","",$str);
	$str = str_replace("..","",$str);
	$str = str_replace("./","",$str);
	$str = str_replace(":","",$str);
	if (substr($str,0,1) == "/" && $pre == false) { $str = substr($str,1); }
	return trim($str,"- \\/.\"'_");
}