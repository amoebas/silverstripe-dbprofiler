<?php
/**
 * Decorator that logs the queries and statistics in a logfile in
 *
 * getTempFolder( BASE_PATH . '-query-stats' ) . '/querystats.php'
 *
 * @package dbprofiler
 */
class DatabaseQueryLogDecorator implements DatabaseQueryExecutable {

	/**
	 * The keeper of the seven data
	 *
	 * @var array
	 */
	protected static $queries  = array(
		'TotalQueries' => 0,
		'Timestamp' => 0,
		'TotalSize' => 0,
		'MemoryUsage' => 0,
		'TotalTime' => 0
	);

	/**
	 * Include the css for the query log bar
	 */
	public function  __construct() {
		Requirements::css( 'dbprofiler/css/querylog.css' );
	}

	/**
	 * Log the query and pass on execution of it.
	 *
	 * @param array $handlers
	 * @param string $sql
	 * @param int $errorLevel
	 * @return MySQLQuery
	 */
	public function executeQuery( $handlers, $sql, $errorLevel = E_USER_ERROR ) {
		$this->logQueryData( $sql );
		$key = md5( $sql );
		$this->logTotals( $key );
		$this->logBacktrace( $key );
		return $this->runQuery( $handlers, $key, $sql, $errorLevel );
	}

	/**
	 * Log data about this sql query 
	 *
	 * @param string $sql
	 * @return void
	 */
	protected function logQueryData( $sql ) {
		$key = md5( $sql );

		// Query already logged
		if( isset( self::$queries[ 'Queries' ][ $key ] ) ) {
			self::$queries[ 'Queries' ][ $key ][ 'Requests' ]++;
			return;
		}
		
		self::$queries[ 'Queries' ][ $key ][ 'Requests' ] = 1;
		self::$queries[ 'Queries' ][ $key ][ 'ID' ] = $key;
		self::$queries[ 'Queries' ][ $key ][ 'Query' ] = str_replace( '"', '`', $sql );
		self::$queries[ 'Queries' ][ $key ][ 'QuerySize' ] = strlen( $sql );
		self::$queries[ 'Queries' ][ $key ][ 'Time' ] = 0;
		self::$queries[ 'Queries' ][ $key ][ 'BacktraceLog' ][ 'Keys' ] = array();
		self::$queries[ 'Queries' ][ $key ][ 'Backtrace' ] = '';
	}

	/**
	 *
	 * @param string $key
	 */
	protected function logTotals( $key ) {
		self::$queries[ 'TotalQueries' ]++;
		self::$queries[ 'TotalSize' ] += self::$queries[ 'Queries' ][ $key ][ 'QuerySize' ];
	}

	/**
	 *
	 * @param string $key
	 */
	protected function logBacktrace( $key ) {
		$backtrace = array_slice( debug_backtrace(), 2 );

		$backtraceKey = '';
		foreach( $backtrace as $stackLevel => $val ) {
			if( isset( $backtrace[ $stackLevel ][ 'file' ] ) ) {
				$backtraceKey .= $backtrace[ $stackLevel ][ 'file' ] . '_' . $backtrace[ $stackLevel ][ 'line' ] . '____';
			} else {
				$backtraceKey .= $backtrace[ $stackLevel ][ 'class' ] . '_' . $backtrace[ $stackLevel ][ 'function' ] . '____';
			}
			unset( $backtrace[ $stackLevel ][ 'object' ] );
			unset( $backtrace[ $stackLevel ][ 'args' ] );
		}

		$backtraceKey = md5( $backtraceKey );
		
		if( !isset( self::$queries[ 'Queries' ][ $key ][ 'BacktraceLog' ][ 'Keys' ][ $backtraceKey ] ) ) {
			self::$queries[ 'Queries' ][ $key ][ 'BacktraceLog' ][ 'Keys' ][ $backtraceKey ] = true;
			if( class_exists( 'SS_Backtrace' ) ) {
				self::$queries[ 'Queries' ][ $key ][ 'Backtrace' ] .= SS_Backtrace::get_rendered_backtrace( $backtrace );
			} else {
				self::$queries[ 'Queries' ][ $key ][ 'Backtrace' ] .= Debug::get_rendered_backtrace( $backtrace );
			}
		}
	}

	/**
	 *
	 * @param array $handlers | Type: DatabaseQueryExecutable
	 * @param string $key
	 * @param string $sql
	 * @param <type> $errorLevel
	 * @return MySQLQuery
	 */
	protected function runQuery( $handlers, $key, $sql, $errorLevel ) {
		if( empty( $handlers ) ) {
			throw new Exception( 'Base handler have not yet been processed, still handlers are empty!' );
		}

		$handler = array_pop( $handlers );
		$startTime = microtime( true );
		$result = $handler->executeQuery( $handlers, $sql, $errorLevel );
		$queryTime = number_format( 1000.00 * ( microtime( true ) - $startTime ), 2);
		self::$queries[ 'Queries' ][ $key ][ 'Time' ] += $queryTime;
		self::$queries[ 'TotalTime' ] += $queryTime;
		return $result;
	}


	/**
	 * Destructor. Log file is created here.
	 *
	 * @return void
	 */
	public function __destruct() {
		if( !isset( self::$queries[ 'Queries' ] ) ) {
			return;
		}
		
		self::$queries[ 'Timestamp' ] = date( 'Y-m-d H:i:s' );
		self::$queries[ 'MemoryUsage' ] = number_format( (float) memory_get_peak_usage( true ) / ( 1024 * 1024 ), 2 );
		self::$queries[ 'TotalSize' ] = number_format( self::$queries[ 'TotalSize' ] / 1024.00, 2);
		file_put_contents( getTempFolder( BASE_PATH . '-query-stats' ) . '/querystats.php', '<?php $logData = ' . var_export( self::$queries, true ) . '; ?>' );

		if( Director::is_ajax() ) {
			return;
		}
		$this->printProfilingInformation();
	}

	/**
	 * Outputs the information of the profiling
	 */
	protected function printProfilingInformation() {
		echo '<div id="QueryLog_Offset"><div id="QueryLog_Info">';
		echo ' PHP peak memory: '.self::$queries[ 'MemoryUsage' ].'MB ';
		echo ' | Querysize: '.self::$queries[ 'TotalSize' ].'KB ';
		echo '| Queries: ';
		
		if( $this->haveCachedQueries() ) {
			echo count(self::$queries[ 'Queries' ]);
			echo ' <a href="'.$this->getCacheLink().'">Uncache queries</a>';
		} else {
			echo self::$queries[ 'TotalQueries' ].' ('.count(self::$queries[ 'Queries' ]);
			echo ' <a href="'.$this->getCacheLink().'">unique</a>) ';
		}

		echo ' | Time in db: '.self::$queries[ 'TotalTime'].'ms ';
		echo ' | <a href="'.Director::absoluteURL( 'ProfilerLogViewerController', true ).'" target="queryprofiler">Read more</a>';
		echo('</div></div>');
	}

	/**
	 *
	 * @return string 
	 */
	protected function getCacheLink() {
		$link = '';
		if( empty( $_GET ) ) {
			return $link;
		}

		if( !( isset( $_GET['dbprofiler'] ) && $_GET['dbprofiler'] == 'cache_duplicates' ) ) {
			$link .= 'dbprofiler=cache_duplicates';
		}
		$ignore = array( 'dbprofiler', 'url' );
		foreach( $_GET as $param => $value ) {
			if( in_array( $param, $ignore ) ) {
				continue;
			}
			$link.='&'.$param.'='.$value;
		}
		return $_GET['url'].'?'.$link;
	}

	/**
	 *
	 * @return boolean
	 */
	protected function haveCachedQueries() {
		if( isset( $_GET['dbprofiler'] ) && $_GET['dbprofiler'] == 'cache_duplicates' ) {
			return true;
		}
		return false;
	}

}
