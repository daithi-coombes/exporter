<?php
/**
 * Export data from mysql table
 *
 * @usage:
 * <code>
 * </code>
 * @author daithi coombes <webeire@gmail.com>
 */
class Exporter{

	/* @var integer The max rows to return before making new query */
	private $batch_size = 10000;
	/* @var Database The database instance */
	private $db;
	/* @var array An array of Error instances. Empty array if none */
	private $errors = array();
	/* @var integer The max results to return */
	private $max_results;
	/* @var array An array of mysql records */
	private $records = array();
	/* @var string The sql query to run. @see Exporter::set_sql() */
	private $sql;
	/* @var resource File handle returned by tmpfile(). 
		@see Exporter::get_tmpfile() */
	private $tmpfile;

	function __construct(){

	}

	/**
	 * Factory method
	 * @param  array  $params An array of param=>value pairs
	 * @return Exporter         Returns new Exporter instance
	 */
	public function factory( $params=array() ){

		$obj = new Exporter();

		if( count($params) )
			return $obj->set( $params );

		return $obj;
	}

	/**
	 * Batch process.
	 * Writes records to tmpfile
	 * @param  integer $start   Default 0. LIMIT start value
	 * @return Exporter           Returns self for chaining
	 */
	public function batch( $start=0 ){

		//vars
		$this->start = $start;
		$this->end = (int) $start + (int) $this->batch_size;
		
		//set the LIMIT statement
		$this->sql_set_limit( $this->start, $this->end );

		//run query
		$this->db->query( $this->sql );
		while( $row = $this->db->fetchArray('array') )
			$this->records[] = $row;

		//need to run more?
		if( 
			(count($this->records) <= $this->batch_size) ||
			($this->max_results && (count($this->records) < $this->max_results))
		)
			$this->batch( $this->end, $this->records );

		//trim records if max_results set
		if( $this->max_results )
			$this->records = array_slice($this->records, 0, $this->max_results);

		return $this;
	}

	/**
	 * Get a params value
	 * @param  string $param The param to get
	 * @return mixed        The param value
	 */
	public function get( $param ){

		return $this->$param;
	}

	/**
	 * Set a param.
	 * If the private method set_$param exists then it will be executed
	 * @param string|array $params An array of param=>value pairs or param name
	 * @param mixed $value Optional. If $param is string pass value here.
	 * @return returns this for chaining
	 */
	public function set( $params, $value=null ){

		//array of params
		if( is_array($params) ){

			foreach( $params as $param=>$value)
				if( method_exists($this, "set_{$param}") ){

					$method = "set_{$param}";
					$this->$method( $value );
				}
				else
					$this->$param = $value;
		}

		//else set directly
		else{

			if( method_exists($this, "set_{$params}") ){

				$method = "set_{$params}";
				$this->$method( $value );
			}
			$this->$params = $value;
		}

		return $this;
	}

	/**
	 * Get file handle to tmp file
	 * @return resource Returns tmp file handle
	 */
	private function get_tmpfile(){

		return tmpfile();
	}

	/**
	 * Sets the database.
	 * Use $this->set( 'db', Database )
	 * @param Database $db The database instance
	 */
	private function set_db( Database $db ){

		$this->db = $db;
	}

	/**
	 * Sets the LIMIT statement
	 * @param  integer $start The start
	 * @param  integer $end   The end
	 * @return Exporter        Returns self for chaining
	 */
	private function sql_set_limit( $start, $end ){

		$pattern = '/LIMIT\s+([0-9]+).+\s([0-9]+)/im';
		$statement = "LIMIT {$start}, {$end}";

		//search for LIMIT statement
		preg_match($pattern, $this->sql, $matches);

		//replace current limit
		if( count($matches)>1 )
			$this->sql = preg_replace($pattern, $statement, $this->sql);

		//else append LIMIT statement
		else
			$this->sql .= $statement;

		return $this;
	}
}