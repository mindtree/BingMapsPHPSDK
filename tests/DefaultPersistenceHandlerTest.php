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
 * @copyright 2011 Mindtree
 * @license   GPL v2 License https://github.com/mindtree/BingMapsPHPSDK
 * @version   SVN: $Id: Map.php 50 2010-11-10 10:00:00Z cal $
 * @link      https://github.com/mindtree/BingMapsPHPSDK
 *
 */

require_once dirname(__FILE__) . "/lib/Msft/Bing/MySQLPersistenceHandler.php";

// initialize MySQL handler
// change MySQL connection details appropriately
$handler = new Msft_Bing_MySQLPersistenceHandler('HOSTNAME', 'DATABASENAME', 'USERNAME', 'PASSWORD');
$handler->initialize();

// check if table exists
$result = $handler->doesEntityExist('tab1');
echo "\nTable tab1 exists? ";
echo ($result == 1) ? "YES"."\n" : "NO"."\n";

// create table if it does not exist
if ($result == 0)
{
	$ent = new Msft_Bing_Entity('tab1');

	// define table columns
	$cols = array();

	$col = new Msft_Bing_Column('col1');
	$col->DataType = 'int';
	$col->MaxLength = 11;
	$col->AllowNull = false;
  	$col->PrimaryKey = true;
	$cols[] = $col;	

	$col = new Msft_Bing_Column('col2');
	$col->DataType = 'varchar';
	$col->MaxLength = 50;
	$col->AllowNull = true;
	$cols[] = $col;	
  
	$col = new Msft_Bing_Column('col3');
	$col->DataType = 'float';
  	$col->DefaultValue = 99.0;
	$cols[] = $col;	  

	$col = new Msft_Bing_Column('col4');
	$col->DataType = 'int';
  	$col->AutoIncrement = true;
  	$col->UniqueKey = true;
	$cols[] = $col;	  

	$ent->Columns = $cols;

	//var_dump($ent);

	$result = $handler->createEntity($ent);
	echo "\nTable created? ";	
	echo ($result == 1) ? "YES"."\n" : "NO"."\n";
}

// select all rows
$rows = $handler->selectRows('tab1', null);
var_dump($rows);

// insert a few rows
$row = new Msft_Bing_Row();
$row->Data = array("col1" => 1, "col2" => "'one'");
$result = $handler->insertRow('tab1', $row);
echo "\nRow inserted? ";
echo ($result == 1) ? "YES"."\n" : "NO"."\n";
$row = new Msft_Bing_Row();
$row->Data = array("col1" => 2, "col2" => "'two'");
$result = $handler->insertRow('tab1', $row);
echo "Row inserted? ";
echo ($result == 1) ? "YES"."\n" : "NO"."\n";
$row = new Msft_Bing_Row();
$row->Data = array("col1" => 3, "col2" => "'three'");
$result = $handler->insertRow('tab1', $row);
echo "Row inserted? ";
echo ($result == 1) ? "YES"."\n" : "NO"."\n";

// select all rows
$rows = $handler->selectRows('tab1', '');
var_dump($rows);
//$rows = $handler->selectRows('tab1', 'badcolumn=abc');
//var_dump($rows);

// update a particular row
$row = new Msft_Bing_Row();
$row->Data = array("col2" => "'TWO'");
$result = $handler->updateRows('tab1', $row, 'col1=2');
echo "Row updated? ";
echo ($result == 1) ? "YES"."\n" : "NO"."\n";

// select updated row
$rows = $handler->selectRows('tab1', 'col1=2');
var_dump($rows);

// delete a particular row
$result = $handler->deleteRows('tab1', 'col1=3');
echo "Row deleted? ";
echo ($result == 1) ? "YES"."\n" : "NO"."\n";

// select all rows
$rows = $handler->selectRows('tab1', null);
var_dump($rows);

// drop the table
$result = $handler->dropEntity('tab1');
echo "Table dropped? ";
echo ($result == 1) ? "YES"."\n" : "NO"."\n";

// check if table exists
$result = $handler->doesEntityExist('tab1');
echo "Table tab1 exists? ";
echo ($result == 1) ? "YES"."\n" : "NO"."\n";

?>
