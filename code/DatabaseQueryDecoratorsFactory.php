<?php
/**
 * Factory for Query decorators.
 *
 */
class DatabaseQueryDecoratorsFactory {

	/**
	 * Factory method for query decorators
	 *
	 * @param DatabaseQueryExecutable $baseHandler
	 * @return array
	 */
	public function getDecorators( DatabaseQueryExecutable $baseHandler ) {
		$handlers = array();
		$handlers[] = $baseHandler;

		if( !isset( $_REQUEST[ 'url' ] ) ) {
			return $handlers;
		}

		if( stristr( $_REQUEST[ 'url' ], 'ProfilerLogViewer' ) ) {
			return $handlers;
		}
		if( isset( $_GET[ 'dbprofiler' ] ) && $_GET['dbprofiler'] == 'cache_duplicates' )  {
			$handlers = $this->enableDecorators( $handlers, 'cache,log' );
		} else {
			$handlers = $this->enableDecorators( $handlers, 'log' );
		}
		
		return $handlers;
	}

	/**
	 * 
	 */
	protected function enableDecorators( $handlers, $string ) {
		$decoratorIdentifiers = explode( ',', $string );
		foreach( array_reverse( $decoratorIdentifiers ) as $identifier ) {
			$className = 'DatabaseQuery' . ucfirst( trim( $identifier ) ) . 'Decorator';
			if( ClassInfo::classImplements( $className, 'DatabaseQueryExecutable' ) ) {
				$handlers[] = new $className();
			}
		}
		return $handlers;
	}

}
