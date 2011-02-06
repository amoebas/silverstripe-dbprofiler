<?php
/**
 * Decorator that caches the queries.
 *
 */
class DatabaseQueryCacheDecorator implements DatabaseQueryExecutable {

	/**
	 * Cache the query and pass on execution of it.
	 *
	 * @param array $handlers
	 * @param string $sql
	 * @param int $errorLevel
	 * @return MySQLQuery
	 */
	public function executeQuery( $handlers, $sql, $errorLevel = E_USER_ERROR ) {
		static $result;
		$key = md5( $sql );
		if( !isset( $result[ $key ] ) ) {
			if( !empty( $handlers ) ) {
				$handler = array_pop( $handlers );
				$result[ $key ] = $handler->executeQuery( $handlers, $sql, $errorLevel );
			}
		}
		return $result[ $key ];
	}

}
