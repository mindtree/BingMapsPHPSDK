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
 * An interface description for PersistenceHandler classes.
 *
 * The implementation classes will be registered with the Bing Maps SDK 
 * and used for storing/retrieving Bing Maps data.
 *  
 * @category   Msft
 * @package    Msft
 * @subpackage Bing
 * @author     Mindtree 
 * @license    GPL v2 License https://github.com/mindtree/BingMapsPHPSDK
 * @link       https://github.com/mindtree/BingMapsPHPSDK
 */
interface Msft_Bing_IPersistenceHandler
{
 
    /**
     * Initialize any connections to database, files etc
     *
     * @return booleans
     */ 	
	public function initialize();

    /**
     * Close all open connections
     *
     * @return boolean
     */ 
	public function uninitialize();

    /**
     * Create the given entity
     *
	 * @param Msft_Bing_Entity $ent
     * @return boolean
     */ 	
	public function createEntity($ent);

    /**
     * Drop the given entity
     *
	 * @param string $entityName
     * @return boolean
     */ 	
	public function dropEntity($entityName);
	
    /**
     * Checks whether the given entity exists or not
     *
	 * @param string $entityName
     * @return boolean
     */ 	
	public function doesEntityExist($entityName);

    /**
     * Insert an entity row
     *
	 * @param string $entityName
	 * @param Msft_Bing_Row $row 
     * @return boolean
     */ 	
	public function insertRow($entityName, $row);

	/**
     * Update entity rows which match the given filter.
	 * The column values to be updated are passed in $rowValue
     * If $whereFilter is null or empty, all rows in the entity will be updated 
     *
	 * @param string $entityName
	 * @param string $whereFilter Example: "firstname='Tom' OR (age>25 AND age<=50)"
	 * @param Msft_Bing_Row $rowValue New column values
     * @return boolean
     */ 	
	public function updateRows($entityName, $rowValue, $whereFilter);

    /**
     * Delete entity rows which match the given filter
     * If $whereFilter is null or empty, all rows in the entity will be deleted 
     *
	 * @param string $entityName
	 * @param string $whereFilter Example: "firstname='Tom' OR (age>25 AND age<=50)"
     * @return boolean
     */ 	
	public function deleteRows($entityName, $whereFilter);


    /**
     * Select rows in the given entity which match the given filter
     * If $whereFilter is null or empty, all rows in the entity will be returned 
     *
	 * @param string $entityName
	 * @param string $whereFilter Example: "firstname='Tom' OR (age>25 AND age<=50)"
     * @return Row[]
     */ 	
	public function selectRows($entityName, $whereFilter);
}

/**
 * This class represents a generic entity.
 *
 * @package    Msft
 * @subpackage Bing
 */
class Msft_Bing_Entity
{
	/**
	 * Entity name
     * @var string
     */
	public $EntityName;

	/**
	 * Entity columns
     * @var array(Msft_Bing_Column)
     */
	public $Columns;
 
    /**
	 * Constructor
	 *
	 * @param string $entityName
	 */
	function __construct($entityName)
	{	
    	$this->EntityName = $entityName;
  	}
}


/**
 * This class represents a column in an entity..
 * 
 * @package    Msft
 * @subpackage Bing
 */
class Msft_Bing_Column
{
	/**
	 * Column name
     * @var string
     */
	public $ColumnName;

	/**
	 * Column data type
     * @var string
     */
	public $DataType;
	
	/**
	 * Maximum length of column
     * @var string
     */
	public $MaxLength;

	/**
	 * Is nullable column?
     * @var boolean
     */
	public $AllowNull;

    /**
     * Is primary column?
     * @var boolean
     */
	public $PrimaryKey;
  
    /**
     * Is unique key column?
     * @var boolean
     */
	public $UniqueKey;  
  
	/**
	 * Is auto increment column?
     * @var boolean
     */
	public $AutoIncrement;

	/**
	 * Default column value
     * @var any
     */
	public $DefaultValue;
  
    /**
	 * Constructor
	 *
	 * @param string $columnName
	 */
	function __construct($columnName)
	{	
    	$this->ColumnName = $columnName;
  	}  
}

/**
 * This class represents a row in an entity.
 *
 * @package    Msft
 * @subpackage Bing
 */
class Msft_Bing_Row
{
	/**
	 * Row column values
	 * @var array(columName,columnValue) 
	 */
	public $Data;
}

?>
