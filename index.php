<?php
/**
 * Handles ajax requests.
 */


require('bootstrap.php');


/**
 * Build your sql statement here
 */
$id = $_POST['manufacturer_id'];
$sql = "SELECT r.id AS roll, b.barcode_hash, m.name AS manufacturer, r.template_name AS template, c.name AS brand, r.created 
	FROM dgu_rolls r 
		LEFT JOIN dgu_barcodes b 		ON b.roll_id 	= r.id 
		LEFT JOIN dgu_manufacturers m 	ON m.id 		= r.manufacturer_id 
		LEFT JOIN dgu_customers c 		ON c.id 		= r.brand
";
if( @$id )
	$sql .= "WHERE r.manufacturer_id = '{$id}'
		AND r.exported=0";


/**
 * Set your Exporter params
 */
$ar_rolls = array();
$params = array(
	'debug' => true,
	'db_settings'	=> array(
		'host' => $db_host,
		'user' => $db_user,
		'pswd' => $db_pswd,
		'name' => $db_name
	),
	'batch_size'	=> 5000,
	'row_hook'		=> 'get_roll_id',
	'end_hook'		=> 'mark_rolls_exported',
	'sql'			=> $sql
);
//end Exporter params


/**                                  **/
/* --  Don't edit below this line  -- */
/**                                  **/


//parse http vars
if( @$_REQUEST['_nonce'] )
	$nonce = $_REQUEST['_nonce'];
if( @$_REQUEST['action'] )
	$action = $_REQUEST['action'];
if( @$_REQUEST['user'] )
	$user = $_REQUEST['user'];
if( @$_REQUEST['id'] )
	$id = $_REQUEST['id'];
//end parse vars


//run script
require_once( EXPORTER_DIR . '/bin/script.php' );


/**
 * Calback function for the exporter package. Will build up array of unique
 * roll id's
 * @param  array  $row Row from the mysql query in Exporter package
 */
function get_roll_id( array $row ){

	global $ar_rolls;
	$ar_rolls[$row['roll']] = $row;
}

/**
 * Mark exported rolls flag
 */
function mark_rolls_exported(){

	global $ar_rolls;
	return;

	$roll_ids = array_keys($ar_rolls);
	$sql = "UPDATE dgu_rolls
		SET exported=1
		WHERE id IN(".implode(",", $roll_ids).")";
	$db->query( $sql );
}