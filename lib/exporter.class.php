<?php
/**
 * Export large sized data from mysql table as csv file
 *
 * @usage:
 * <code>
 * 	$export_db = mysqli_connect( $db_host, $db_user, $db_password, $db_name );
 * 	$params = array(
 * 		'db'			=> $export_db,	//must be mysqli instance
 * 		'batch_size'	=> 5000,
 * 		'max_results'	=> 200,
 * 		'sql'			=> $sql
 * 	);
 * 	$results = Exporter::factory( $params )
 * 		->batch()
 * 		->output( 'rolls', false )
 * 		->get( 'results' );
 * 	
 * 	var_dump($results);
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
	 * @param  mysqli $db   Connected mysqli instance
	 * @return Exporter           Returns self for chaining
	 */
	public function batch( $start=0 ){

		//vars
		$this->start = $start;
		$this->end = (int) $start + (int) $this->batch_size;
		
		//set the LIMIT statement
		$this->sql_set_limit( $this->start, $this->end );

		//set the tmpfile
		if( !$this->tmpfile )
			$this->tmpfile = $this->get_tmpfile();

		//run query
		$this->query = mysqli_query( $this->db, $this->sql );
		while( $row = mysqli_fetch_assoc( $this->query ) ){
			$this->records[] = $row;
			fputcsv( $this->tmpfile, $row );
		}

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
	 * Output csv to stdout
	 * @param  string $filename Default null. Filename
	 * @param  boolean $die Default true. Whether to die after or not
	 * @return Exporter If $die is false then returns this for chaining
	 */
	public function output( $filename=null, $die=true ){

		//set extension
		$info = pathinfo($filename);
		if( empty($info['extension']) )
			$filename .= ".csv";

		//send headers
		header("Content-Type: application/csv");
		header("Content-Disposition: attachment;Filename={$filename}");

		//get file
		rewind($this->tmpfile);
		while( ($line=fgets($this->tmpfile))!==false )
			print $line."\n";

		if( $die )
			die();
		else
			return $this;
	}

	/**
	 * Set a param.
	 * If the private method set_$param exists then it will be executed
	 * @param string|array $params An array of param=>value pairs or param name
	 * @param mixed $value Optional. If $param is string pass value here.
	 * @return Exporter this for chaining
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
	private function set_db( mysqli $db ){

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
		$statement = "
			LIMIT {$start}, {$end}";

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