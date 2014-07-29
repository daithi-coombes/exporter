<?php

class ExporterDatabase extends mysqli{

	public function factory( $host, $user, $pswd, $name ){

		$obj = new ExporterDatabase();
		$obj->__construct( $host, $user, $pswd, $name );

		return $obj;
	}
}