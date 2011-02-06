<?php
/**
 * Interface for Query decorators
 *
 */
interface DatabaseQueryExecutable {

	/**
	 * Interface method for query decorators
	 *
	 * @param array $handlers
	 * @param string $sql
	 * @param int $errorLevel
	 * @return MySQLQuery
	 */
	function executeQuery( $handlers, $sql, $errorLevel = E_USER_ERROR );

}
