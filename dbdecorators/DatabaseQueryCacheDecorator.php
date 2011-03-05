<?php
/**
 * Decorator that caches all queries that are exactly the same.. or have the same
 * md5 sum.
 *
 * @package dbprofiler
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
		static $_cached_sql_results;

		$sqlHashSum = md5( $sql );

		if( isset( $_cached_sql_results[ $sqlHashSum ] ) ) {
			return $_cached_sql_results[ $sqlHashSum ];
		}
		$handler = array_pop( $handlers );
		$_cached_sql_results[ $sqlHashSum ] = $handler->executeQuery( $handlers, $sql, $errorLevel );
		return $_cached_sql_results[ $sqlHashSum ];
	}
}