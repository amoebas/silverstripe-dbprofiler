<?php
/**
 * Profiling class for the MySQLDatabase.
 *
 */
class ProfilerMySQLDatabase extends MySQLDatabase implements DatabaseQueryExecutable {

	/**
	 * All handlers complying to the DatabaseQueryExecutable interface.
	 *
	 * @var array
	 */
	protected static $handlers = null;

	/**
	 * Constructor
	 *
	 * @param array $parameters
	 * @return ProfilerMySQLDatabase
	 */
	public function __construct( $parameters ) {
		if( !isset( self::$handlers ) ) {
			$decoratorFactory = new DatabaseQueryDecoratorsFactory();
			self::$handlers = $decoratorFactory->getDecorators( $this );
		}
		parent::__construct( $parameters );
	}

	/**
	 * Overloading of the query method to apply the decorators.
	 *
	 * @param string $sql
	 * @param int $errorLevel
	 * @return MySQLQuery
	 */
	public function query($sql, $errorLevel = E_USER_ERROR) {
		$handlers = self::$handlers;
		$handler = array_pop( $handlers );
		return $handler->executeQuery( $handlers, $sql, $errorLevel );
	}

	/**
	 * Executes the query.
	 *
	 * @param array $handlers
	 * @param string $sql
	 * @param int $errorLevel
	 * @return MySQLQuery
	 */
	public function executeQuery( $handlers, $sql, $errorLevel = E_USER_ERROR ) {
		if( !empty( $handlers ) ) {
			throw new Exception( 'No handler should be executed after this as this is the base! Handler left is: ' . get_class( reset( $handlers ) ) );
		}
		return parent::query( $sql, $errorLevel );
	}

}
