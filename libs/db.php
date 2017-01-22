<?php

class DB {

	// the name of the main table
	const TABLE_NAME = 'documentation';
	
	// limit the number of query results
	const QUERY_RESULTS_LIMIT = 30;

	// db connection handler
	/* @var \PDO */
	protected $db;
	
	protected $dbFilePath = __DIR__ . '/../docs.db';

	// singleton. create connection and check the table
	protected function __construct() {
		$this->createConnection();
		$this->checkTable();
	}

	// create DB connection
	protected function createConnection() {
		try {
			$this->db = new PDO('sqlite:'.$this->dbFilePath);
			$this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

		} catch (PDOException $e) {
			trigger_error('DB Connection error. '.$e->getMessage(), E_USER_ERROR);
		}
	}

	// check that the table exists
	protected function checkTable() {
		// check if the table exists
		$stmt = $this->db->prepare('
			SELECT name 
			FROM sqlite_master 
			WHERE type="table" AND 
				name=:name');
		$stmt->execute(array(':name' => static::TABLE_NAME));

		// create the table
		if ($stmt->rowCount() == 0) {
			$this->db->exec('
				create table '.static::TABLE_NAME.' (
					name TEXT,
					file TEXT,
					version TEXT,
					parsed_docblock TEXT,

					PRIMARY KEY (name, file, version)
				);
			');
		}
	}

	/**
	 * insert new DOC block to the DB.
	 * id the block already exists, just update
	 * 
	 * @param string $actionName
	 * @param string $actionFile
	 * @param array $docBlock
	 * @return string updated|added
	 */
	public function insertDoc($actionName, $actionFile, array $docData) {
		// retrieve the version from the doc data
		$version = $docData['version'] ?? '';

		// check if the row exists
		$stmt = $this->db->prepare('
			select * from '.static::TABLE_NAME.'
			where 
				name = :name and 
				file = :file and
				version = :version
			limit 1');

		$stmt->execute(array(
			':name' => $actionName, 
			':file' => $actionFile,
			':version' => $version
		));
		$theRecord = $stmt->fetch();

		// check whether insert or update the record
		if (!empty($theRecord)) {
			$operation = 'updated';

			$stmt2 = $this->db->prepare('
				update '.static::TABLE_NAME.' set
					parsed_docblock = :parsed_docblock
				where name = :name and file = :file and version = :version
			');
		} else {
			$operation = 'added';

			$stmt2 = $this->db->prepare('
				insert into '.static::TABLE_NAME.' (name, file, version, parsed_docblock)
				values (:name, :file, :version, :parsed_docblock)
			');
		}

		// execute the update/insert query
		$result = $stmt2->execute(array(
			':name' => $actionName, 
			':file' => $actionFile,
			':version' => $version,
			':parsed_docblock' => json_encode($docData),
		));

		if ($result === false) {
			$errorInfo = $this->db->errorInfo();
			return "error: {$errorInfo[2]}";
		}

		if ($result) {
			return $operation;
		}

		return '-error-';
	}
	
	/**
	 * get the search results
	 * 
	 * @param string $searchQuery 
	 * @return array
	 */
	public function getDocs($searchQuery = '') {
		// prepare the SQL query
		$sqlQuery = 'select * from '.static::TABLE_NAME;
		
		// add the search query if defined
		if (!empty($searchQuery)) {
			$sqlQuery.= ' where name like :query_string';
		}
		
		$sqlQuery.= ' order by version desc, name asc';
		$sqlQuery.= ' limit '.static::QUERY_RESULTS_LIMIT;
		$stmt = $this->db->prepare($sqlQuery);
		
		// add the search query if defined
		if (!empty($searchQuery)) {
			$stmt->bindValue(':query_string', '%'.$searchQuery.'%');
		}
		
		$stmt->execute();
		
		$result = $stmt->fetchAll();
		foreach ($result as $i => $rec) {
			$result[$i]['parsed_docblock'] = json_decode($result[$i]['parsed_docblock'], true);
		}
		
		return $result;
	}

	/////////////////////////////////////////////////////////

	protected static $_instance = null;

	public static function getInstance() {
		if (is_null(static::$_instance)) {
			static::$_instance = new DB;
		}

		return static::$_instance;
	}
}

