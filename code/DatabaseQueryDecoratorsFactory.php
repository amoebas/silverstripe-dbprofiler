<?php
/**
 * Factory for Query decorators.
 *
 * @package dbprofiler
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

		if( SapphireTest::is_running_test() ) {
			return $handlers;
		}

		if( Director::is_cli() ) {
			return $handlers;
		}

		if( stristr( $_REQUEST[ 'url' ], 'ProfilerLogViewerController' ) ) {
			return $handlers;
		}

		if( stristr( $_REQUEST[ 'url' ], 'favicon' ) ) {
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
	 * Enables the
	 *
	 * @param SS_Database $handlers
	 * @param string $dbdecorators
	 * @return array - an array of handlers, that is instanced dbdecorators
	 */
	protected function enableDecorators( SS_Database $handlers, $dbdecorators ) {
		$decoratorIdentifiers = explode( ',', $dbdecorators );
		foreach( array_reverse( $decoratorIdentifiers ) as $identifier ) {
			$className = 'DatabaseQuery' . ucfirst( trim( $identifier ) ) . 'Decorator';
			if( ClassInfo::classImplements( $className, 'DatabaseQueryExecutable' ) ) {
				$handlers[] = new $className();
			}
		}
		return $handlers;
	}

}
