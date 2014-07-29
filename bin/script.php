<?php
#!/usr/bin/php

//debug?
if( $params['debug']===true){
	error_reporting( E_ALL );
	ini_set('display_errors', 'on');
}

$results = Exporter::factory( $params )
	->batch()
	->output( 'rolls', false )
	->get( 'records' );
//end Export csv and get results


/**
 * Mark used roll id's as exported
 */


//print result
if( is_object($results) ){

	$errors = $results->get_errors('errors');
	$res = array(
		'ok' => 'false',
		'errors' => $errors
	);
}
else
	$res = array(
		'ok' => 'true',
		'message' => $message
	);

print json_encode( $res );
die();