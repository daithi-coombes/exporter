<?php
/**
 * Export data from mysql table
 *
 * @author daithi coombes <webeire@gmail.com>
 */
class Exporter{

	/* @var array An array of Error instances. Empty array if none */
	private $errors = array();
	/* @var integer The max rows to return before making new query */
	private $limit = 10000;
	/* @var string The sql query to run. @see Exporter::set_sql() */
	private $sql;
	/* @var resource File handle returned by tmpfile(). 
		@see Exporter::get_tmpfile() */
	private $tmpfile;

	function __construct(){

	}

	/**
	 * Factory method
	 * @return Exporter Returns new Exporter instance
	 */
	public function factory(){

		return new Exporter();
	}

	public function set( $param, $value ){

		//if setter for this param
		if( method_exists($this, "set_{$param}") ){

			$method = "set_{$param}";
			return $this->$method( $value );
		}

		//else set directly
		$this->$param = $value;
		return $this;
	}

	public function run( Database $db, $start=0 ){

		$args = func_get_args();

		$total = $this->get_total( $db );

		$records = array();
		$end = (int) $start + (int) $this->limit;
		$sql = $this->sql . "\nLIMIT {$start}, {$end}";


		$db->query( $this->sql );
		while( $row = $db->fetchArray('array') )
			$records[] = $row;

		$start = $end;

		if( count($records)>=$this->limit )
			$records = array_merge( $records, $this->run($db, $end) );

		return $records;
	}

	/**
	 * Get file handle to tmp file
	 * @return resource Returns tmp file handle
	 */
	private function get_tmpfile(){

		return tmpfile();
	}

	private function get_total( Database $db ){

		$sql = preg_replace('/^SELECT\s/i', 'SELECT SQL_CALC_FOUND_ROWS ', $this->sql);

		$db->query( $sql );
		$db->query( 'SELECT FOUND_ROWS()' );
		while( $row = $db->fetchArray('array') )
			$res[] = $row;

		return $res;
	}
}