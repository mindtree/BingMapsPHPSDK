<?php

/**
 * Microsoft Bing Maps
 * 
 * PHP library to support the Microsoft Bing Maps API.
 *
 * PHP Version 5
 *  
 * @category  Msft
 * @package   Msft
 * @author    Mindtree 
 * @copyright 2011 Mindtree Limited
 * @license    GPL v2 License https://github.com/mindtree/BingMapsPHPSDK
 * @link       https://github.com/mindtree/BingMapsPHPSDK
 * @version   SVN: $Id: Map.php 50 2010-11-10 10:00:00Z cal $
 *
 */

/**
 * Include Exception file
 */
require_once dirname(__FILE__) . "/Exception.php";

/**
 * Include Interface file
 */
require_once dirname(__FILE__) . "/IPersistenceHandler.php";

/**
 * Default implementation of Msft_Bing_IPersistenceHandler interface.
 *
 * This class uses MySQL for storing/retrieving data.
 * The Bing Maps SDK uses this class as the default persistence handler.
 *  
 * @category   Msft
 * @package    Msft
 * @subpackage Bing
 * @author     Mindtree 
 * @license    GPL v2 License https://github.com/mindtree/BingMapsPHPSDK
 * @link       https://github.com/mindtree/BingMapsPHPSDK
 */
class Msft_Bing_MySQLPersistenceHandler implements Msft_Bing_IPersistenceHandler
{
	/**
	 * MySQL server host
     * @var string
     */
	protected $host;

	/**
	 * MySQL database name
     * @var string
     */
	protected $database;

	/**
	 * MySQL user name
     * @var string
     */
	protected $user;

	/**
	 * MySQL password
     * @var string
     */
	protected $password;

	/**
	 * MySQL connection string
	 * @var mixed
	 */
	protected $connection;

	/**
	 * Constructor
	 *
	 * @param string $host
	 * @param string $database
	 * @param string $user
	 * @param string $password
	 */
	function __construct($host, $database, $user, $password)
	{	
		$this->host = ($host != null) ? $host : 'localhost';
		$this->database = ($database != null) ? $database : '';
		$this->user = ($user != null) ? $user : '';
		$this->password = ($password != null) ? $password : '';

		// Perform checks
		if (!function_exists('mysql_connect')) 
		{
			throw new Msft_Bing_MapException('Unable to load MySQL library functions');
		}
	}

	/**
	 * Destructor
	 *
	 */
	public function __destruct()
	{
	}
 
    /**
     * Initialize any connections to database, files etc
     *
     * @return boolean
     */ 	
	public function initialize()
	{
		// connect to the MySQL server
		if (!($this->connection = mysql_connect($this->host, $this->user, $this->password, true))) 
		{
			$errorMsg = mysql_error($this->connection);
			throw new Msft_Bing_MapException('Unable to connect to MySQL server '.$this->host.'. '.$errorMsg);			
		}
		
		// change to required database
		if (!mysql_select_db($this->database, $this->connection)) 
		{
			$errorMsg = mysql_error($this->connection);		
			throw new Msft_Bing_MapException('Unable to connect to MySQL database '.$this->database.'. '.$errorMsg);		
		}

		return true;
	}

    /**
     * Close all open connections
     *
     * @return boolean
     */ 
	public function uninitialize()
	{
		if (is_resource($this->connection)) 
		{
			// close connection		
			mysql_close($this->connection);
		}
		
		return true;
	}

    /**
     * Create the given entity
     *
	 * @param Msft_Bing_Entity $ent
     * @return boolean
     */ 	
	public function createEntity($ent)
	{
		if (($ent == null) || ($ent == ''))
		{
			throw new Msft_Bing_MapException('Invalid entity parameter');		
		}
		
		if (!is_resource($this->connection)) 
		{
			throw new Msft_Bing_MapException('Not connected to MySQL server '.$this->host.'. Invoke Initialize() method');
		}	

		// build Create Table statement
		$sqlfmt = 'CREATE TABLE '.$ent->EntityName.' (%s)';    
    if (($ent->Columns != null) && ($ent->Columns != ''))
    {
      $colDefs = array();
      foreach($ent->Columns as $col)
      {
        if (($col->DataType == null) || ($col->DataType == ''))
        {
			    throw new Msft_Bing_MapException('Invalid column definition for column '.$col->ColumnName);			          
        }
        $colDef = $col->ColumnName.' '.$col->DataType;
        if (($col->MaxLength != null) && ($col->MaxLength != '') && (is_numeric($col->MaxLength)))
        {
          $colDef = $colDef.'('.$col->MaxLength.') ';
        }
        if (isset($col->AllowNull))
        {
          $nullVal = ($col->AllowNull == true) ? " NULL " : " NOT NULL ";
          $colDef = $colDef.$nullVal;          
        }
        if (isset($col->PrimaryKey))
        {
          $primary = ($col->PrimaryKey == true) ? " PRIMARY KEY " : "";
          $colDef = $colDef.$primary;          
        }
        if (isset($col->UniqueKey))
        {
          $unique = ($col->UniqueKey == true) ? " UNIQUE KEY " : "";
          $colDef = $colDef.$unique;          
        }        
        if (isset($col->AutoIncrement))
        {
          $increment = ($col->AutoIncrement == true) ? " AUTO_INCREMENT " : "";
          $colDef = $colDef.$increment;
        }
        if (isset($col->DefaultValue))
        {
          $colDef = $colDef." DEFAULT ".$col->DefaultValue." ";
        }
        
        $colDefs[] = $colDef;
      }
    }
    $sql = sprintf($sqlfmt, implode(",", $colDefs));
    
		// execute SQL statement to create given table			
		$cursor = mysql_query($sql, $this->connection);
		if (!$cursor)
		{
			$errorMsg = mysql_error($this->connection);		
			throw new Msft_Bing_MapException('Unable to create table '.$ent->EntityName.'. '.$errorMsg);			
		}		
		
		return true;
	}

    /**
     * Drop the given entity
     *
	 * @param string $entityName
     * @return boolean
     */ 	
	public function dropEntity($entityName)
	{
		if (($entityName == null) || ($entityName == ''))
		{
			throw new Msft_Bing_MapException('Invalid entityName parameter');		
		}
	
		if (!is_resource($this->connection)) 
		{
			throw new Msft_Bing_MapException('Not connected to MySQL server '.$this->host.'. Invoke Initialize() method');
		}

		// execute SQL statement to drop given table			
		$sql = 'DROP TABLE '.$entityName;
		$cursor = mysql_query($sql, $this->connection);
		if (!$cursor)
		{
			$errorMsg = mysql_error($this->connection);		
			throw new Msft_Bing_MapException('Unable to drop table '.$entityName.'. '.$errorMsg);			
		}		
		
		return true;
	}
	
    /**
     * Checks whether the given entity exists or not
     *
	 * @param string $entityName
     * @return boolean
     */ 	
	public function doesEntityExist($entityName)
	{
		if (($entityName == null) || ($entityName == ''))
		{
			throw new Msft_Bing_MapException('Invalid entityName parameter');		
		}
	
		if (!is_resource($this->connection)) 
		{
			throw new Msft_Bing_MapException('Not connected to MySQL server '.$this->host.'. Invoke Initialize() method');
		}

		// select matching rows from the given table
		$sql = "SELECT * FROM information_schema.tables WHERE table_schema = '".$this->database."' AND table_name = '".$entityName."'";
		$cursor = mysql_query($sql, $this->connection);		
		
		if (!$cursor)
		{
			$errorMsg = mysql_error($this->connection);		
			throw new Msft_Bing_MapException('Unable to check for existence of table '.$entityName.'. '.$errorMsg);			
		}		
		
		$rowCount = mysql_num_rows($cursor);
		
		if ($rowCount > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	

    /**
     * Insert an entity row
     *
	 * @param string $entityName
	 * @param Msft_Bing_Row $row
     * @return boolean
     */ 	
	public function insertRow($entityName, $row)
	{
		if (($entityName == null) || ($entityName == '') || ($row == null) || ($row == ''))
		{
			throw new Msft_Bing_MapException('Invalid parameters');		
		}
	
		if (!is_resource($this->connection)) 
		{
			throw new Msft_Bing_MapException('Not connected to MySQL server '.$this->host.'. Invoke Initialize() method');
		}	
		
		// insert row with given values in the given table
		$sqlfmt = 'INSERT INTO '.$entityName.' (%s) VALUES (%s)';
		if (($row->Data != null) && ($row->Data != ''))
		{
			$row->Data = (array)$row->Data;
			// get column names		
			$columnNames = array_keys($row->Data);
			// get column names		
			$values = array_values($row->Data);
		}
		
		$sql = sprintf($sqlfmt, implode(",", $columnNames), implode(",", $values));
			
		$cursor = mysql_query($sql, $this->connection);		
		if (!$cursor)
		{
			$errorMsg = mysql_error($this->connection);		
			throw new Msft_Bing_MapException('Unable to insert into table '.$entityName.'. '.$errorMsg);			
		}		
		
		return true;
	}

	/**
     * Update entity rows which match the given filter.
	 * The column values to be updated are passed as $rowValue
     * If $whereFilter is null or empty, all rows in the entity will be updated 
     *
	 * @param string $entityName
	 * @param string $whereFilter Example: "firstname='Tom' OR (age>25 AND age<=50)"
	 * @param Msft_Bing_Row $rowValue New column values
     * @return boolean
     */ 	
	public function updateRows($entityName, $rowValue, $whereFilter)
	{
		if (($entityName == null) || ($entityName == '') || ($rowValue == null) || ($rowValue == ''))
		{
			throw new Msft_Bing_MapException('Invalid parameters');		
		}
	
		if (!is_resource($this->connection)) 
		{
			throw new Msft_Bing_MapException('Not connected to MySQL server '.$this->host.'. Invoke Initialize() method');
		}	
		
		// update matching rows with given values in the given table
		$sql = 'UPDATE '.$entityName.' SET ';
		// build column values
		if (($rowValue->Data != null) && ($rowValue->Data != ''))
		{
			$rowValue->Data = (array)$rowValue->Data;
			foreach(array_keys($rowValue->Data) as $columnName)
			{
				$sql = $sql.$columnName.'='.$rowValue->Data[$columnName];
				$sql = $sql.',';
			}
			$sql = rtrim($sql, ",");
		}
		if (($whereFilter != null) && ($whereFilter != ''))
		{
			$sql = $sql.' WHERE '.$whereFilter;
		}
			
		$cursor = mysql_query($sql, $this->connection);
		if (!$cursor)
		{
			$errorMsg = mysql_error($this->connection);		
			throw new Msft_Bing_MapException('Unable to update table '.$entityName.'. '.$errorMsg);			
		}		
		
		return true;
	}

    /**
     * Delete entity rows which match the given filter
     * If $whereFilter is null or empty, all rows in the entity will be deleted 
     *
	 * @param string $entityName
	 * @param string $whereFilter Example: "firstname='Tom' OR (age>25 AND age<=50)"
     * @return boolean
     */ 	
	public function deleteRows($entityName, $whereFilter)
	{
		if (($entityName == null) || ($entityName == ''))
		{
			throw new Msft_Bing_MapException('Invalid entityName parameter');		
		}
	
		if (!is_resource($this->connection)) 
		{
			throw new Msft_Bing_MapException('Not connected to MySQL server '.$this->host.'. Invoke Initialize() method');
		}	
		
		// delete matching rows from the given table
		$sql = 'DELETE FROM '.$entityName;
		if (($whereFilter != null) && ($whereFilter != ''))
		{
			$sql = $sql.' WHERE '.$whereFilter;
		}
		
		$sql = $sql.';';
		
		$cursor = mysql_query($sql, $this->connection);
		if (!$cursor)
		{
			$errorMsg = mysql_error($this->connection);		
			throw new Msft_Bing_MapException('Unable to delete from table '.$entityName.'. '.$errorMsg);			
		}		
		
		return true;
	}

    /**
     * Select rows in the given entity which match the given filter
     * If $whereFilter is null or empty, all rows in the entity will returned
     *
	 * @param string $entityName
	 * @param string $whereFilter Example: "firstname='Tom' OR (age>25 AND age<=50)"
     * @return Msft_Bing_Row[]
     */ 	
	public function selectRows($entityName, $whereFilter)
	{
		if (($entityName == null) || ($entityName == ''))
		{
			throw new Msft_Bing_MapException('Invalid entityName parameter');		
		}
	
		if (!is_resource($this->connection)) 
		{
			throw new Msft_Bing_MapException('Not connected to MySQL server '.$this->host.'. Invoke Initialize() method');
		}	
		
		// select matching rows from the given table
		$sql = 'SELECT * FROM '.$entityName;
		if (($whereFilter != null) && ($whereFilter != ''))
		{
			$sql = $sql.' WHERE '.$whereFilter;
		}
		
		$cursor = mysql_query($sql, $this->connection);
		
		if (!$cursor)
		{
			$errorMsg = mysql_error($this->connection);		
			throw new Msft_Bing_MapException('Unable to query table '.$entityName.'. '.$errorMsg);			
		}
		
		$rows = array();
		
		// While a row of data exists, put that row in $row as an associative array
		while ($data = mysql_fetch_assoc($cursor)) 
		{
			$row = new Msft_Bing_Row();
			$row->Data = $data;
			$rows[] = $row;			
		}
		
		return $rows;
	}
}

?>
