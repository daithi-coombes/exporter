<?php
#!/usr/bin/php

//debug?
if( $params['debug']===true){
	error_reporting( E_ALL );
	ini_set('display_errors', 'on');
}

$results = Exporter::factory( $params )
	->batch()
	->output( 'rolls', false );
	//->get( 'records' );
//end Export csv and get results


print json_encode( $results );
die();