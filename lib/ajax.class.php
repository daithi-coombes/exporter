<?php

/**
 * Ajax class.
 *
 * In your php file, before the header.php snippets called, declare your nonces and actions like so:
 * <code>
 * global $ajaxObj;
 * $nonces = array(
 * 	'get_more' => $ajaxObj->nonce_create( 'get_more' )
 * );
 * </code>
 *
 * You can then make ajax post requests, using jquery, like so
 * <code>
 * 		var data = {
 * 			action: 'get_more',			//same key as used for nonce declaration above
 * 			_nonce: nonces.get_more,	//the nonce
 * 			lib: 'News.get_more'		//the class and method
 * 		};
 * 
 * 		$.post(
 * 			BASE_URL + '/ajax.php',
 * 			data,
 * 			function( res ){ console.log(res); },
 * 			'json'
 * 		);
 * </code>
 *
 * The above javascript sample will look for the 'ajax_get_more' STATIC method in the news.class.php file
 * ie: will call: News::ajax_get_more( $_REQUEST ) This method must return an associative array, which
 * will then be json_encoded and printed to stdOut by this ajax system
 *
 * Please note calling class will not be constructed and method must be prepended with 'ajax_'. This is
 * for security reasons.
 * 
 * n.b.
 * 	nonce logic taken from the class: @link http://fullthrottledevelopment.com/php-nonce-library
 */
class Ajax{

	const ALIVE_TIME = 43200;	//nonce stays alive for 12hrs

	function __construct(){
	}

	static function factory(){
		return new Ajax();
	}

	// This method creates an nonce. It should be called by one of the previous two functions.
	function nonce_create( $action = '' , $user='' ){
		return substr( $this->nonce_generate_hash( $action . $user ), -12, 10);
	}

	// This method validates an nonce
	function nonce_is_valid( $nonce , $action = '' , $user='' ){
		// Nonce generated 0-12 hours ago
		if ( substr( $this->nonce_generate_hash( $action . $user ), -12, 10) == $nonce ){
			return true;
		}
		return false;
	}

	// This method generates the nonce timestamp
	function nonce_generate_hash( $action='' , $user='' ){
		$i = ceil( time() / ( self::ALIVE_TIME / 2 ) );
		return md5( $i . $action . $user . $action );
	}
}