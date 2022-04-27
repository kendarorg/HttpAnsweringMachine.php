<?php
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
ini_set('log_errors', 'On');
ini_set('error_log', __DIR__.'/request.log'); 


//cat /etc/os-release |grep -oE '^ID=([a-zA-Z0-9]+)' | cut -d = -f 2


function add_file(&$zip,$base,$sub,$lastTime,&$files){
	
	$baseSub = $base;
	if($sub!=null){
		$baseSub = $base.'/'.$sub;
		$sub=$sub.'/';
	}else{
		$sub="";
	}
	if(file_exists($baseSub)){
		error_log("SCANNING ".$baseSub);
		foreach(glob($baseSub.'/*') as $filename){
			if(is_dir($filename))continue;
			$name = basename($filename);
			$myTime = filemtime($filename);
			$mySize = filesize($filename);
			$zip->addFile($filename, $sub.$name);
			$files[]=$filename." ".date("F d Y H:i:s.",$myTime);
			if($myTime>$lastTime){
				$lastTime = $myTime;
			}
		}
		foreach(glob($baseSub.'/.*') as $filename){
			if(is_dir($filename))continue;
			$name = basename($filename);
			$myTime = filemtime($filename);
			$mySize = filesize($filename);
			$zip->addFile($filename, $sub.$name);
			$files[]=$filename." ".date("F d Y H:i:s.",$myTime);
			if($myTime>$lastTime){
				$lastTime = $myTime;
			}
		}
	}
	return $lastTime;
}

$configs = explode(";",$_GET['config']);
$os = $_GET['os'];

error_log("LOADING ".$os."=>".$_GET['config']);

//$GLOBALS['config']=__DIR__."/".$os."/".$config;
$GLOBALS['commons']=__DIR__."/".$os."/commons";
$GLOBALS['globals']=__DIR__."/globals";
$file = tempnam("tmp", "zip");
$zip = new ZipArchive();


if ($zip->open($file, ZipArchive::OVERWRITE) === TRUE)
{
	//http://localhost:20080/index.php?os=alpine&config=basic;ssh
	$files=[];
	$lastTime = 0;
	$lastTime = add_file($zip,$GLOBALS['globals'],'conf',$lastTime,$files);
	$lastTime = add_file($zip,$GLOBALS['globals'],'runonce',$lastTime,$files);
	$lastTime = add_file($zip,$GLOBALS['globals'],'svcs',$lastTime,$files);
	$lastTime = add_file($zip,$GLOBALS['globals'],null,$lastTime,$files);
	$lastTime = add_file($zip,$GLOBALS['commons'],'conf',$lastTime,$files);
	$lastTime = add_file($zip,$GLOBALS['commons'],'runonce',$lastTime,$files);
	$lastTime = add_file($zip,$GLOBALS['commons'],'svcs',$lastTime,$files);
	$lastTime = add_file($zip,$GLOBALS['commons'],null,$lastTime,$files);
	
	foreach($configs as $config){
		error_log("CONFIG ".$config);
		$GLOBALS['config']=__DIR__."/".$os."/".$config;
		$lastTime = add_file($zip,$GLOBALS['config'],'conf',$lastTime,$files);
		$lastTime = add_file($zip,$GLOBALS['config'],'runonce',$lastTime,$files);
		$lastTime = add_file($zip,$GLOBALS['config'],'svcs',$lastTime,$files);
		$lastTime = add_file($zip,$GLOBALS['config'],null,$lastTime,$files);
	}
	
    // Add files to the zip file inside demo_folder
    //$zip->addFile('text.txt', 'demo_folder/test.txt');
    
    // Add random.txt file to zip and rename it to newfile.txt and store in demo_folder
    //$zip->addFile('random.txt', 'demo_folder/newfile.txt');
 
    // Add a file demo_folder/new.txt file to zip using the text specified
   	$updated = "Last update: ".date ("F d Y H:i:s.",$lastTime);
   	foreach($files as $ff){
   		$updated.="\n".$ff;
   	}
   	
    $zip->addFromString('lastupdate.txt', $updated);
 
    // All files are added, so close the zip file.
    $zip->close();
}
header("Content-Type: application/zip");
header("Content-Disposition: attachment; filename=config.zip");
header("Content-Length: " . filesize($file));

readfile($file);
unlink($file); 
exit();