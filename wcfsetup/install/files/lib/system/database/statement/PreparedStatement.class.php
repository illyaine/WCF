<?php
namespace wcf\system\database\statement;
use wcf\data\DatabaseObject;
use wcf\system\benchmark\Benchmark;
use wcf\system\database\exception\DatabaseQueryException;
use wcf\system\database\exception\DatabaseQueryExecutionException;
use wcf\system\database\Database;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Represents a prepared statements based upon pdo statements.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Database\Statement
 * 
 * @mixin	\PDOStatement
 */
class PreparedStatement {
	/**
	 * database object
	 * @var	Database
	 */
	protected $database;
	
	/**
	 * SQL query parameters
	 * @var	array
	 */
	protected $parameters = [];
	
	/**
	 * pdo statement object
	 * @var	\PDOStatement
	 */
	protected $pdoStatement;
	
	/**
	 * SQL query
	 * @var	string
	 */
	protected $query = '';
	
	/**
	 * Creates a new PreparedStatement object.
	 * 
	 * @param	Database	$database
	 * @param	\PDOStatement	$pdoStatement
	 * @param	string		$query		SQL query
	 */
	public function __construct(Database $database, \PDOStatement $pdoStatement, $query = '') {
		$this->database = $database;
		$this->pdoStatement = $pdoStatement;
		$this->query = $query;
	}
	
	/**
	 * Delegates inaccessible methods calls to the decorated object.
	 * 
	 * @param	string		$name
	 * @param	array		$arguments
	 * @return	mixed
	 * @throws	SystemException
	 */
	public function __call($name, $arguments) {
		if (!method_exists($this->pdoStatement, $name)) {
			throw new SystemException("unknown method '".$name."'");
		}
		
		try {
			return call_user_func_array([$this->pdoStatement, $name], $arguments);
		}
		catch (\PDOException $e) {
			throw new DatabaseQueryException("Could call '".$name."' on '".$this->query."'", $e);
		}
	}
	
	/**
	 * Executes a prepared statement.
	 * 
	 * @param	array		$parameters
	 * @throws	DatabaseQueryExecutionException
	 */
	public function execute(array $parameters = []) {
		$this->parameters = $parameters;
		$this->database->incrementQueryCount();
		
		try {
			if (WCF::benchmarkIsEnabled()) Benchmark::getInstance()->start($this->query, Benchmark::TYPE_SQL_QUERY);
			
			$result = $this->pdoStatement->execute($parameters);
			
			if (!$result) {
				$errorInfo = $this->pdoStatement->errorInfo();
				throw new DatabaseQueryExecutionException("Could not execute statement '".$this->query."': ".$errorInfo[0].' '.$errorInfo[2], $parameters);
			}

			if (WCF::benchmarkIsEnabled()) Benchmark::getInstance()->stop();
		}
		catch (\PDOException $e) {
			if (WCF::benchmarkIsEnabled()) Benchmark::getInstance()->stop();
			
			throw new DatabaseQueryExecutionException("Could not execute statement '".$this->query."'", $parameters, $e);
		}
	}
	
	/**
	 * Fetches the next row from a result set in an array.
	 * 
	 * @param	integer		$type		fetch type
	 * @return	mixed
	 */
	public function fetchArray($type = null) {
		// get fetch style
		if ($type === null) $type = \PDO::FETCH_ASSOC;
		
		return $this->fetch($type);
	}
	
	/**
	 * Fetches the next row from a result set in an array.
	 * Closes the 'cursor' afterwards to free up the connection
	 * for new queries.
	 * Note: It is not possible to fetch further rows after calling
	 * this method!
	 * 
	 * @param	integer		$type		fetch type
	 * @return	mixed
	 * @see		\wcf\system\database\statement\PreparedStatement::fetchArray()
	 */
	public function fetchSingleRow($type = null) {
		$row = $this->fetchArray($type);
		$this->closeCursor();
		
		return $row;
	}
	
	/**
	 * Returns the specified column of the next row of a result set.
	 * Closes the 'cursor' afterwards to free up the connection
	 * for new queries.
	 * Note: It is not possible to fetch further rows after calling
	 * this method!
	 * 
	 * @param	integer		$columnNumber
	 * @return	mixed
	 * @see		\PDOStatement::fetchColumn()
	 */
	public function fetchSingleColumn($columnNumber = 0) {
		$column = $this->fetchColumn($columnNumber);
		$this->closeCursor();
		
		return $column;
	}
	
	/**
	 * Fetches the next row from a result set in a database object.
	 * 
	 * @param	string			$className
	 * @return	DatabaseObject
	 */
	public function fetchObject($className) {
		$row = $this->fetchArray();
		if ($row !== false) {
			return new $className(null, $row);
		}
		
		return null;
	}
	
	/**
	 * Fetches the all rows from a result set into database objects.
	 * 
	 * @param	string		$className
	 * @param	string|null	$keyProperty
	 * @return	DatabaseObject[]
	 */
	public function fetchObjects($className, $keyProperty = null) {
		$objects = [];
		while ($object = $this->fetchObject($className)) {
			if ($keyProperty === null) {
				$objects[] = $object;
			}
			else {
				$objects[$object->$keyProperty] = $object;
			}
		}
		
		return $objects;
	}
	
	/**
	 * Returns a map of all fetched rows using one column as key and another column as value.
	 * 
	 * @param	string		$keyColumn	name of the key column
	 * @param	string		$valueColumn	name of the value column
	 * @param	boolean		$uniqueKey	if `true`, a one-dimensional array is returned, otherwise, for each key an array of fetched values is returned 
	 * @return	string[]|string[][]
	 */
	public function fetchMap($keyColumn, $valueColumn, $uniqueKey = true) {
		$map = [];
		
		while ($row = $this->fetchArray()) {
			$key = $row[$keyColumn];
			$value = $row[$valueColumn];
			
			if ($uniqueKey) {
				$map[$key] = $value;
			}
			else {
				if (!isset($map[$key])) {
					$map[$key] = [];
				}
				
				$map[$key][] = $value;
			}
		}
		
		return $map;
	}
	
	/**
	 * Returns a one-dimensional list of all rows holding only the value of the specified column. Please see
	 * `fetchAll()` if you simply want to read all rows into an array. 
	 * 
	 * @param string $column
	 * @return string[]|int[]|float[]
	 */
	public function fetchList($column) {
		$list = [];
		
		while ($row = $this->fetchArray()) {
			if (!array_key_exists($column, $row)) {
				throw new \RuntimeException("The requested column '{$column}' is not contained in the result rows.");
			}
			
			$list[] = $row[$column];
		}
		
		return $list;
	}
	
	/**
	 * Counts number of affected rows by the last sql statement (INSERT, UPDATE or DELETE).
	 * 
	 * @return	integer		number of affected rows
	 * @throws	DatabaseQueryException
	 */
	public function getAffectedRows() {
		try {
			return $this->pdoStatement->rowCount();
		}
		catch (\PDOException $e) {
			throw new DatabaseQueryException("Could fetch affected rows for '".$this->query."'", $e);
		}
	}
	
	/**
	 * Returns the number of the last error.
	 * 
	 * @return	integer
	 */
	public function getErrorNumber() {
		if ($this->pdoStatement !== null) return $this->pdoStatement->errorCode();
		
		return 0;
	}
	
	/**
	 * Returns the description of the last error.
	 * 
	 * @return	string
	 */
	public function getErrorDesc() {
		if ($this->pdoStatement !== null) {
			$errorInfoArray = $this->pdoStatement->errorInfo();
			if (isset($errorInfoArray[2])) return $errorInfoArray[2];
		}
		
		return '';
	}
	
	/**
	 * Returns the SQL query of this statement.
	 * 
	 * @return	string
	 */
	public function getSQLQuery() {
		return $this->query;
	}
	
	/**
	 * Returns the SQL query parameters of this statement.
	 * 
	 * @return	array
	 */
	public function getSQLParameters() {
		return $this->parameters;
	}
}
