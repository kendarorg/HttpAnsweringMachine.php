<?php

function handle_options(){
	if($_SERVER['REQUEST_METHOD']=="OPTIONS"){
		do_error_log("OPTIONS CALLED:");
		do_error_log("SERVER. ".var_export($_SERVER,true));
		do_error_log("HEADERS OPTIONS. ".var_export(get_request_headers(),true));
		return true;
	}else{
		return false;
	}
}