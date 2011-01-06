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
 * @copyright 2010 Microsoft
 * @license   New BSD License (BSD) http://bingmapsphp.codeplex.com/license
 * @version   SVN: $Id: Map.php 50 2010-11-10 10:00:00Z cal $
 * @link      http://bingmapsphp.codeplex.com
 *
 */

/**
 * Map class for the Bing Maps API.
 * Provides API's for interacting with Bing Maps
 *  
 * @todo Add APIs for supporting other Bing Maps features  
 *  
 * @category   Msft
 * @package    Msft
 * @subpackage Bing
 * @author     Mindtree 
 * @license    New BSD License (BSD) http://bingmapsphp.codeplex.com/license
 * @link       http://bingmapsphp.codeplex.com
 */
class Msft_Bing_Map
{
	/**
	 * ID of HTML element which will hold the map 
	 * @var string
	 */
	protected $divName = 'adminMap';
	
	/**
	 * Bing Portal ID used for validating map operations
	 * @var string
	 */
	protected $bingID;
	
	/**
	 * Properties of the map
	 * @var Msft_Bing_MapProperties
	 */
	protected $properties;
	
	/**
	 * Array of pushpins 
	 * @var array(Msft_Bing_Pushpin)
	 */
	protected $pushpinsToAdd;
	
	/**
	 * Configuration properties
	 * @var array
	 */
	protected $configDetails;
	
	/**
	 * Handle to persistence handler
	 * @var Msft_Bing_IPersisenceHandler  
	 */
	protected $persistenceHandler;
	
	/**
	 * Has map been displayed?
	 * @var boolean
	 */
	private $displaymap;

	/**
	 * Has map been loaded?
	 * @var boolean
	 */
	private $loadMap;
	
	/**
	 * Name of pushpins entity
	 * @var string
	 */
	private $bingPins = 'bing_pins';
	
	/**
	 * Name of configuration entity
	 * @var string 
	 */
	private $bingConfig = 'bing_configuration';
	
	
	private $bingMapLibPath = '';
	
	/**
	 * Include the necessary javascript files for Bing Maps to function
	 */
	private function includeJavaScriptFiles()
	{
		$bingMapLibPath = $this->getBingMapLibPath();		
		$includeJS = '<script type="text/javascript" src="http://ecn.dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=6.3"></script>';
		$includeJS.= '<script type="text/javascript" src="'.$bingMapLibPath.'/Msft/Bing/js/jquery-1.2.6.js"></script>';
		$includeJS.= '<script type="text/javascript" src="'.$bingMapLibPath.'/Msft/Bing/js/maps.js"></script>';
		$includeJS.= '<script type="text/javascript">bingMapLibPath="'.$bingMapLibPath.'";</script>';		
		echo $includeJS;
	}
	
	/**
	 * Initialize the map object in javascript for the map to show up
	 * @param string $divName
	 */
	private function initializeMap($divName)
	{
		echo 'InitializeMap(\''.$divName.'\', '.$this->properties->getzoomLevel().', '.$this->properties->getWidth().', '.$this->properties->getHeight().', \''.$this->properties->getMapType().'\');';
	}
	
	/**
	 * Locates the Bing Maps SDK path
	 */
	private function locateLibPath()
	{

		$libPath = 'lib';
		if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) {
			$https = 's://';
		} else {
			$https = '://';
		}

		$scriptName = $_SERVER['SCRIPT_NAME'];
		$scriptBaseName = basename($_SERVER['SCRIPT_NAME']);
		$scriptPos = strpos($scriptName, $scriptBaseName);
		$scriptPath = substr($scriptName, 0, $scriptPos);
		$scriptFullPath = 'http' . $https . $_SERVER['HTTP_HOST'].$scriptPath;
		
		$this->setBingMapLibPath($scriptFullPath.$libPath);		
		
	}
	
	/**
	 * Constructor
	 * Calls appropriate constructor based on the number of parameters
	 * @param string divId
	 * @param string bingId
	 */
	function __construct()
	{
		$num = func_num_args();
		$args = func_get_args();
        if (method_exists($this, $f = '__construct'.$num)) 
        { 
            call_user_func_array(array($this, $f), $args); 
        } 
        
		$this->locateLibPath();		
		$this->includeJavaScriptFiles();
		$this->displayMap = false;
		$this->loadMap = false;
		
	} 
	
	/**
	 * Constructor 1
	 * @param string $divName
	 */
	function __construct1($divName)
	{
		if (isset($divName))
			$this->setDivName($divName);
		$this->properties = new Msft_Bing_MapProperties();		
	}
	
	/**
	 * Constructor 2
	 * @param string $divName
	 * @param string|Msft_Bing_MapProperties $bingIDorProperties 
	 */
	function __construct2($divName, $bingIDorProperties)
	{
		if (isset($divName))
			$this->setDivName($divName);
			
		if (gettype($bingIDorProperties) == 'object')
			$this->setProperties($bingIDorProperties);
		else 
		{
			$this->setBingID($bingIDorProperties);
 		    $this->properties = new Msft_Bing_MapProperties();			
		}
	}
	
	/**
	 * Constructor 3
	 * @param string $divName
	 * @param Msft_Bing_MapProperties $properties
	 * @param string $bingID  
	 */
	function __construct3($divName, $properties, $bingID)
	{
		if (isset($divName))
			$this->setDivName($divName);
			
		$this->setProperties($properties);
			
		$this->setBingID($bingID);

	}

	
	public function setpushpinsToAdd($pushpinsToAdd) 
	{
		$this->pushpinsToAdd = $pushpinsToAdd;
	}
	
	public function getpushpinsToAdd() 
	{
		return $this->pushpinsToAdd;
	}
	
	public function setconfigDetails($configDetails) 
	{
		$this->configDetails = $configDetails;
	}
	
	public function getconfigDetails() 
	{
		return $this->configDetails;
	}
		
    /**
     * Sets the ID of the HTML DIV element which will hold the map
     * 
     * @param string $divName
     */
	public function setDivName($divName) 
	{
		$this->divName = $divName;
	}
	
    /**
     * Gets the ID of the HTML DIV element holding the map
     * 
     * @return string
     */
	public function getDivName() 
	{
		return $this->divName;
	}
	
    /**
     * Sets the Bing Maps portal ID used for validating map operations
     * 
     * @param string $bingid
     */
	public function setBingID($bingid)
	{
		$this->bingID = $bingid;
	}
	
    /**
     * Gets the Bing Maps portal ID
     * 
     * @return string
     */
	public function getBingID()
	{
		return $this->bingID;
	}
	
    /**
     * Sets the properties of the map
     * 
     * @param Msft_Bing_MapProperties $properties
     */
	public function setProperties($properties)
	{
		$this->properties = $properties;
	}
	
    /**
     * Gets the map properties
     * 
     * @return MapProperties
     */
	public function getProperties()
	{
		return $this->properties;
	}
	
 	/**
     * Sets the bing map library path
     * 
     * @param string $bingMapLibPath
     */
	public function setBingMapLibPath($bingMapLibPath)
	{
		$this->bingMapLibPath = $bingMapLibPath;
	}
	
    /**
     * Gets the bing map library path
     * 
     * @return string
     */
	public function getBingMapLibPath()
	{
		return $this->bingMapLibPath;
	}
	
	/**
	 * Register an IPersistanceHandler handler and initialize it
	 * @param Msft_Bing_IPersistenceHandler $handler
	 */
	public function registerPersistenceHandler($handler)	
	{
		if ($handler != null)
		{
			$this->persistenceHandler  = $handler;
			$this->persistenceHandler->initialize();
		}
	}
	
	/**
	 * Unregister the current persistence handler and uninitialize it
	 */
	public function unregisterPersistenceHandler()
	{
		if ($this->persistenceHandler != null)
		{
			$this->persistenceHandler->uninitialize();
			$this->persistenceHandler  = null;
		}
	}
		
	/**
	 * Set current BingID as the map credentials
	 */		
	public function setCredentials()
	{
		// call SetCredentials Javascript function on the map variable
		if ($this->displaymap == true)
		{
			if (isset($this->bingID))
				echo 'ValidateBing(\''.$this->bingID.'\');';
		}
	}
	
	/**
	 * Display the map. Uses the javascript methods to achieve this
	 */
	public function displayMap()
	{
		$this->initializeMap($this->getDivName());
		$this->displaymap = true;
		$this->setCredentials();
		echo 'GetMap();';	
		$this->goToDefaultLocation();
	}
	
	/**
	 * Resets the map to its default view.  
	 * @param string $id
	 */
	public function resetMap()
	{		
		if ($this->displaymap == true)
		{		
			echo 'ResetMap();';	
		}
	}

	/**
	 * Display the pushpins loaded from the database. Internally makes use of the pushpinsToAdd array.
	 */
	public function displayLoadedPushpins()
	{
		if ($this->displaymap == true)
		{
			if ($this->pushpinsToAdd != null && $this->pushpinsToAdd != '')
			{
				for ($no=0;$no<count($this->pushpinsToAdd);$no++)
				{
					echo 'FindAndAddPin(\''.$this->pushpinsToAdd[$no]->id.'\', \''.$this->pushpinsToAdd[$no]->title.'\', \''.$this->pushpinsToAdd[$no]->description.'\', \''.$this->pushpinsToAdd[$no]->location.'\');';
				}
			}
		}
	}
	
	/**
	 * Display the given HTML control on the map.
	 * This can be used for displaying a menu on mouse right click on the map 
	 * @param string $domElement
	 */
	public function addLayer($domElement)
	{
		echo 'AddMapControl(\''.$domElement.'\');';
	}
	
	/**
	 * Makes the title and description available for the given pushpin ID. 
	 * @param int $pinLen
	 */
	public function getPinDetails($pinLen)
	{
		echo 'FetchPinDetails('.$pinLen.');';
	}
	
	/**
	 * Displays text boxes on the map to allow users to specify title and description for a pushpin 
	 * @param string $title
	 * @param string $description
	 */
	public function attachDetails($title, $description)
	{
		echo 'AttachDetails('.$title.', '.$description.');';
	}
	
	/**
	 * Add a pushpin on the map at the specified address
	 * @param string $address
	 */
	public function addPushpinAddress($address)
	{
		if ($this->displaymap == true)
		{
			$cnt = count($this->pushpinsToAdd);
			$pinObj = new Msft_Bing_Pushpin($cnt + 1, 'Pushpin', 'No description only address', $address);	
			$this->pushpinsToAdd[] = $pinObj;		
		
			echo 'FindAndAddPin(\''.$pinObj->id.'\', \''.$pinObj->title.'\', \''.$pinObj->description.'\', \''.$pinObj->location.'\');';
		}
	}

	/**
	 * Add a pushpin on the map at the specified location
	 * @param float $latitude
	 * @param float $longitude
	 * @param string $defaultpin yes|no 
	 */
	public function addPushpinLatLon($latitude, $longitude, $defaultpin)
	{
		// call appropriate Javascript function to display pushpin at location
		// save pushpin details in pushpinsToAdd array
		if ($this->displaymap == true)
		{
			$cnt = count($this->pushpinsToAdd);
			$pinObj = new Msft_Bing_Pushpin($cnt + 1, 'Pushpin', 'No description only LatLon', $latitude, $longitude);	
			$this->pushpinsToAdd[] = $pinObj;		
					
			echo "AddPushpinLatLon(".$latitude.",".$longitude.", '".$defaultpin."');";
		}
	}
	
	/**
	 * Add pushpins from the details available in the Pushpin object
	 * @param Msft_Bing_Pushpin $pinObj
	 */
	public function addPushpinPushpin($pinObj)
	{
		if ($this->displaymap == true)
		{
			if ($this->pushpinsToAdd != null && $this->pushpinsToAdd != '')
			{
				foreach(array_values($this->pushpinsToAdd) as $pin)
				{
					if ($pin->id == $pinObj->id)
					{
						throw new Msft_Bing_MapException('Pushpin with given ID already exists.');					
					}
				}
			}
			$this->pushpinsToAdd[] = $pinObj;
			
			echo 'FindAndAddPin(\''.$pinObj->id.'\', \''.$pinObj->title.'\', \''.$pinObj->description.'\', \''.$pinObj->location.'\');';
		}	
	}
		
	/**
	 * Remove an existing pushpin at the given location from the map
	 * @param float $latitude
	 * @param float $longitude
	 */
	public function removePushpinLatLon($latitude, $longitude)
	{
		// call appropriate Javascript function to hide pushpin at location
		// remove pushpin details in pushpinsToAdd array
	}
	
	/**
	 * Remove an existing pushpin at the given address from the map
	 * @param float $address
	 */	
	public function removePushpinAddress($address)
	{
		// call appropriate Javascript function to hide pushpin at location
		// remove pushpin details in pushpinsToAdd array
	}
	
	/**
	 * Remove an existing pushpin pointed by the mouse
	 */	
	public function deletePinGraphical()
	{
		echo 'GetPinId();';
	}
	
	/**
	 * Remove a specific pushpin identified by the given ID from the map
	 * @param string $id
	 */
	public function removePushpinPinId($id)
	{
		// call appropriate Javascript function to hide pushpin with given ID
		// remove pushpin details in pushpinsToAdd array
		
		if ($this->displaymap == true)
		{
			$pinLocation = -1;
			for($pinLoc = 0; $pinLoc < count($this->pushpinsToAdd); $pinLoc++)
			{
				$pin = $this->pushpinsToAdd[$pinLoc];
				if ($pin->id == $id)
				{
					$pinLocation = $pinLoc;
				}
			}
			if ($pinLocation != -1)
			{
				array_splice($this->pushpinsToAdd, $pinLocation, 1);			
			}
		
			echo 'RemPushpin(\''.$id.'\');';	
		}
	}
	
	/**
	 * Remove the specified pushpin from the map
	 * @param Msft_Bing_Pushpin $pinObj
	 */
	public function removePushpinPushpin($pinObj)
	{
		// call appropriate Javascript function to hide pushpin with given details
		// remove pushpin details in pushpinsToAdd array
	}
	
	/**
	 * Returns a list of currently loaded pushpins in the object
	 * @return array(Msft_Bing_Pushpin)
	 */
	public function listPushpins()
	{	
		return $this->pushpinsToAdd;
	}
	
	/**
	 * Set the map location to the default location
	 * @param string $address
	 */
	public function goToDefaultLocation()
	{
		// call appropriate Javascript function to point map at the default location
		if (($this->displaymap == true) && ($this->properties->getDefaultLocation() != null))
		{		
			echo 'GotoUserLocation(\''.$this->properties->getDefaultLocation().'\');';
		}
	}
		
	/**
	 * Set the map location to the specified address
	 * @param string $address
	 */
	public function goToLocationAddress($address)
	{
		// call appropriate Javascript function to point map at the given location
		if ($this->displaymap == true)
		{
			echo 'GotoUserLocation(\''.$address.'\');';
		}
	}
	
	/**
	 * Set the map location to the specified location (latitude, longitude)
	 * @param float $latitude
	 * @param float $longitude
	 */	
	public function goToLocationLatLon($latitude, $longitude)
	{
		// call appropriate Javascript function to point map at the given location
		if ($this->displaymap == true)
		{
			echo "GotoUserLocation('".$latitude."','".$longitude."');";
		}
	}
		
	/**
	 * Write the current mouse location (latitude and longitude) on the map inside the given textbox
	 * @param string $textboxID
	 */
	public function getCurrentCoordinates($textboxID)
	{	
		// call appropriate Javascript function to get the latitude and longitude for the current map location
		if ($this->displaymap == true)
		{
			echo "GetCurrentLocation('" . $textboxID . "');";
		}
	} 

	/**
	 * Write the location (latitude and longitude) corresponding to the given address inside the given textbox
	 * @param string $address 
	 * @param string $textboxID 
	 */
	public function getCoordinates($address, $textboxID)
	{
		// call appropriate Javascript function to get the latitude and longitude for the given address  on the map
		echo "GetLocation('" . $address . "','" . $textboxID . "');";
	}
	
	/**
	 * Write the address at the current mouse location on the map inside the given textbox
	 * @param string $textboxID 
	 */
	public function getCurrentAddress($textboxID)
	{
		// call appropriate Javascript function to get the address at the current map location
		if ($this->displaymap == true)
		{
			echo "GetCurrentAddress('" . $textboxID . "');";
		}
	}
	
	/**
	 * Write the address corresponding to the given location (latitude and longitude) inside the given textbox
	 * @param string $latitude 
	 * @param string $longitude 
	 * @param string $textboxID 
	 */
	public function getAddress($latitude, $longitude, $textboxID)
	{
		// call appropriate Javascript function to get the address at the given latitude and longitude
		echo "GetAddress('" . $latitude . "','" .  $longitude . "','" . $textboxID . "');";
	}
		
	/**
	 * Show direction on the map between the given from and to locations
	 * @param string $fromAddress
	 * @param string $toAddress
	 */
	public function showDirections($fromAddress, $toAddress)
	{
		// call appropriate Javascript function to display directions between the given addresses
		if ($this->displaymap == true)
		{
			echo 'GetRouteMap(\''.$fromAddress.'\', \''.$toAddress.'\');';
		}
	}
	
	/**
	 * Define Pushpins entity schema and call persitenceHandler.CreateEntity() to create it
	 */
	public function createPushpinsEntity()
	{
		if ($this->persistenceHandler == null)
			throw new Msft_Bing_MapException('Persistence Handler not registered. Invoke RegisterPersistenceHandler() method');
		
		$result = $this->persistenceHandler->doesEntityExist($this->bingPins);
	
		// create table if it does not exist
		if ($result == 0)
		{
			$ent = new Msft_Bing_Entity($this->bingPins);
		
			// define table columns
			$cols = array();
		
			$col = new Msft_Bing_Column('ID');
			$col->DataType = 'int';
			$col->AllowNull = false;
		  	$col->AutoIncrement = true;
		  	$col->PrimaryKey = true;
		  	$cols[] = $col;	  
		  	
			$col = new Msft_Bing_Column('title');
			$col->DataType = 'varchar';
			$col->MaxLength = 50;
			$col->AllowNull = true;
		  	$cols[] = $col;	
		
			$col = new Msft_Bing_Column('description');
			$col->DataType = 'varchar';
			$col->MaxLength = 88;
			$col->AllowNull = true;
			$cols[] = $col;	
			
			$col = new Msft_Bing_Column('location');
			$col->DataType = 'varchar';
			$col->MaxLength = 88;
			$col->AllowNull = true;
			$cols[] = $col;	
		  
			$col = new Msft_Bing_Column('latitude');
			$col->DataType = 'float';
		  	$col->DefaultValue = 99.0;
		  	$col->AllowNull = true;
			$cols[] = $col;	  
		
			$col = new Msft_Bing_Column('longitude');
			$col->DataType = 'float';
			$col->DefaultValue = 99.0;
		  	$col->AllowNull = true;
			$cols[] = $col;	  
		
			$ent->Columns = $cols;
			
			$result = $this->persistenceHandler->createEntity($ent);
		}
	}
	
	/**
	 * Call persitenceHandler.DropEntity() method to drop Pushpins entity
	 */
	public function dropPushpinsEntity()
	{
		$this->persistenceHandler->dropEntity($this->bingPins);
	}
	
	/**
	 * Define Bing Maps Configuration entity schema and call persitenceHandler.CreateEntity() to create it
	 */
	public function createConfigurationEntity()
	{
		if ($this->persistenceHandler == null)
			throw new Msft_Bing_MapException('Persistence Handler not registered. Invoke RegisterPersistenceHandler() method');
		
		$result = $this->persistenceHandler->doesEntityExist($this->bingConfig);
	
		// create table if it does not exist
		if ($result == 0)
		{
			$cEntity = new Msft_Bing_Entity($this->bingConfig);
		
			// define table columns
			$cols = array();
		
			$col = new Msft_Bing_Column('slno');
			$col->DataType = 'int';
			$col->AllowNull = false;
			$col->AutoIncrement = true;
		  	$col->PrimaryKey = true;
		  	$cols[] = $col;	
		
			$col = new Msft_Bing_Column('bing_key');
			$col->DataType = 'varchar';
			$col->MaxLength = 80;
			$col->AllowNull = true;
			$cols[] = $col;	
		  
			$col = new Msft_Bing_Column('positionID');
			$col->DataType = 'int';
		  	$col->AllowNull = true;
			$cols[] = $col;	  
		
			$col = new Msft_Bing_Column('width');
			$col->DataType = 'int';
			$col->AllowNull = true;
			$cols[] = $col;	  
			
			$col = new Msft_Bing_Column('height');
			$col->DataType = 'int';
			$col->AllowNull = true;
			$cols[] = $col;
			
			$col = new Msft_Bing_Column('location');
			$col->DataType = 'varchar';
			$col->MaxLength = 80;
			$col->AllowNull = true;
		  	$cols[] = $col;	  
			
			$col = new Msft_Bing_Column('zoomLevel');
			$col->DataType = 'int';
			$col->AllowNull = true;
			$cols[] = $col;
			
			$col = new Msft_Bing_Column('content_type');
			$col->DataType = 'varchar';
			$col->MaxLength = 80;
			$col->AllowNull = true;
		  	$cols[] = $col;	  
		
			$cEntity->Columns = $cols;
			
			$result = $this->persistenceHandler->createEntity($cEntity);
		}	
	}
	
	/**
	 * Call persitenceHandler.DropEntity() method to drop Configuration entity
	 */
	public function dropConfigurationEntity()
	{
		$this->persistenceHandler->dropEntity($this->bingConfig);
	}
	
	/**
	 * Call persitenceHandler.InsertRow to save current map properties into Configuration entity
	 */
	public function saveConfiguration()
	{
		if ($this->persistenceHandler == null)
			throw new Msft_Bing_MapException('Persistence Handler not registered. Invoke RegisterPersistenceHandler() method');
			
		$mapPro = $this->getProperties();	
	  	$this->createConfigurationEntity();
	  	
	  	$where = 'slno = (select max(slno) from '.$this->bingConfig.');';
		$res = $this->persistenceHandler->selectRows($this->bingConfig, $where);
	  	
	  	$pinsArray = array();
		//Change the object structure as required to be inserterd into database	
		$pinsArray[0]->bing_key = '\''.$this->getBingID().'\'';
		$pinsArray[0]->positionID = '\'1\'';
		$pinsArray[0]->width = '\''.$mapPro->getWidth().'\'';
		$pinsArray[0]->height = '\''.$mapPro->getHeight().'\'';
		$pinsArray[0]->location = '\''.$mapPro->getDefaultLocation().'\'';
		$pinsArray[0]->zoomLevel = '\''.$mapPro->getzoomLevel().'\'';
		$pinsArray[0]->content_type = '\''.$mapPro->getContentType().'\'';
			
		if ($res != null && $res != '')
		{
			$pinsArray[0]->slno = $res[0]->Data['slno'];
			$row = new Msft_Bing_Row();
			$row->Data = $pinsArray[0];
			// do an update as values exist
			$whereFilter = 'slno = '.$res[0]->Data['slno'];
			$result = $this->persistenceHandler->updateRows($this->bingConfig, $row, $whereFilter);
		}
		else
		{
			$pinsArray[0]->slno = '\'\'';
			$row = new Msft_Bing_Row();
			$row->Data = $pinsArray[0];
			//InsertRow($entityName, $row)		 
			$result = $this->persistenceHandler->insertRow($this->bingConfig, $row);
		}
	}
	
	/**
	 * Call persitenceHandler.SelectRows to load map properties from Configuration entity into current object. 
	 */
	public function loadConfiguration()
	{
		if ($this->persistenceHandler == null)
			throw new Msft_Bing_MapException('Persistence Handler not registered. Invoke RegisterPersistenceHandler() method');
			
	  	$this->createConfigurationEntity();
		$result = $this->persistenceHandler->selectRows($this->bingConfig, null);
		if ($result!=null && $result!='')
	  	{
		  	for ($pr=0;$pr<count($result);$pr++)
		  	{
		  		$pinRes[$pr]->slno = $result[$pr]->Data['slno'];
		  		$pinRes[$pr]->bingKey = $result[$pr]->Data['bing_key'];
		  		$pinRes[$pr]->positionID = $result[$pr]->Data['positionID'];
		  		$pinRes[$pr]->width = $result[$pr]->Data['width'];
		  		$pinRes[$pr]->height = $result[$pr]->Data['height'];
		  		$pinRes[$pr]->location = $result[$pr]->Data['location'];
		  		$pinRes[$pr]->zoomLevel = $result[$pr]->Data['zoomLevel'];
		  		$pinRes[$pr]->content_type = $result[$pr]->Data['content_type'];
		  		
		  		$this->setBingID($pinRes[$pr]->bingKey);
		  		$properties = new Msft_Bing_MapProperties($pinRes[$pr]->width, $pinRes[$pr]->height, $pinRes[$pr]->zoomLevel);
		  		$this->setProperties($properties);
		  		$this->properties->setDefaultLocation($pinRes[$pr]->location);
		  	}
		  			
			$this->setconfigDetails($pinRes);
			return true;
	  	}
	  	else 
	  		return false;
	}
	
	/**
	 * Call persitenceHandler.InsertRow to save data from pushpinsToAdd array into Pushpins entity
	 */
	public function savePushpins()
	{
		if ($this->persistenceHandler == null)
			throw new Msft_Bing_MapException('Persistence Handler not registered. Invoke RegisterPersistenceHandler() method');
		
		$this->createPushpinsEntity();  	
		$pinsArray = array();
		
		if ($this->loadMap == true)
			$this->persistenceHandler->deleteRows($this->bingPins, '');
			
		//InsertRow($entityName, $row)
		for ($record = 0; $record < count($this->pushpinsToAdd); $record++)
		{
			//Change the object structure as required to be inserterd into database
			$pinsArray[$record]->id = '\'\'';
			$pinsArray[$record]->title = '\''.$this->pushpinsToAdd[$record]->title.'\'';
			$pinsArray[$record]->description = '\''.$this->pushpinsToAdd[$record]->description.'\'';
			$pinsArray[$record]->location = '\''.$this->pushpinsToAdd[$record]->location.'\'';
			$pinsArray[$record]->latitude = '\'\'';
			$pinsArray[$record]->longitude = '\'\'';
			
			//Add to database
			$row = new Msft_Bing_Row();
			$row->Data = $pinsArray[$record];
			$result = $this->persistenceHandler->insertRow($this->bingPins, $row);
		}
	}
	
	/**
	 * Call persitenceHandler.SelectRows to load pushpins from Pushpins entity into pushpinsToAdd array. 
	 * Also loadMap variable is set to true so that Delete and Insert happens accordingly.
	 */
	public function loadPushpins()
	{
		$this->loadMap = true;
		// 
		if ($this->persistenceHandler == null)
			throw new Msft_Bing_MapException('Persistence Handler not registered. Invoke RegisterPersistenceHandler() method');
			
		$this->createPushpinsEntity();
		$result = $this->persistenceHandler->selectRows($this->bingPins, null);
		if ($result!=null && $result!='')
	  	{
		  	for ($pr = 0; $pr < count($result); $pr++)
		  	{
		  		$pinRes[$pr]->id = $pr+1;
		  		$pinRes[$pr]->title = $result[$pr]->Data['title'];
		  		$pinRes[$pr]->description = $result[$pr]->Data['description'];
		  		$pinRes[$pr]->location = $result[$pr]->Data['location'];
		  		$pinRes[$pr]->latitude = $result[$pr]->Data['latitude'];
		  		$pinRes[$pr]->longitude = $result[$pr]->Data['longitude'];
		  	}
		  	
			$this->pushpinsToAdd = $pinRes;
			return true;
	  	}
	  	else 
	  		return false;
		
	}		
}

/**
 * This class captures the various map properties like the width, height, and so on.
 *  
 * @package    Msft
 * @subpackage Bing
 */
class Msft_Bing_MapProperties
{
	/**
	 * Width of the map
	 * @var int
	 */
	protected $width = 400;
	
	/**
	 * Height of the map
	 * @var int
	 */
	protected $height = 400;
	
	/**
	 * Zoom level of the map
	 * @var int
	 */
	protected $zoomLevel = 8;
	
	/**
	 * Default location of the map
	 * @var string
	 */
	protected $defaultLocation;
	
	/**
	 * Type of map - Road, Aerial, Hybrid, Oblique, Shaded, Birdseye etc.
	 * @var string ('r'|'a'|'h'|'o'|'s'|'b')
	 */
	protected $mapType = 'h';	
		
	protected $content_type;
		
	/**
	 * Constructor
	 * Calls appropriate constructor based on the number of parameters
	 */
	function __construct()
	{
		$num = func_num_args();
		$args = func_get_args();
        if (method_exists($this, $f = '__construct'.$num)) 
        { 
            call_user_func_array(array($this, $f), $args); 
        } 
	} 
	
	/**
	 * Constructor 1
	 * 
	 * @param int $width
	 * @param int $height
	 * @param int $zoomLevel
	 */
	function __construct0()
	{
	}
	
	/**
	 * Constructor 2
	 * 
	 * @param int $width
	 * @param int $height
	 * @param int $zoomLevel
	 */
	function __construct3($width, $height, $zoomLevel)
	{
		if (isset($width))
			$this->width = $width;
		if (isset($height))
			$this->height = $height;
		if (isset($zoomLevel))
			$this->zoomLevel = $zoomLevel;
	}
	
    /**
     * Sets the width of the map
     * 
     * @param int $width
     */
	public function setWidth($width)
	{
		$this->width = $width;
	}
	
    /**
     * Sets the height of the map
     * 
     * @param int $height
     */
	public function setHeight($height)
	{
		$this->height = $height;
	}
	
    /**
     * Sets the zoom level of the map
     * 
     * @param int $zoomLevel
     */
	public function setzoomLevel($zoomLevel)
	{
		$this->zoomLevel = $zoomLevel;
	}
	
    /**
     * Gets the width of the map
     * 
     * @return int
     */
	public function getWidth()
	{
		return $this->width;
	}
	
    /**
     * Gets the height of the map
     * 
     * @return int
     */
	public function getHeight()
	{
		return $this->height;
	}
	
    /**
     * Gets the zoom level of the map
     * 
     * @return int
     */
	public function getzoomLevel()
	{
		return $this->zoomLevel;
	}
	
    /**
     * Sets the default location of the map
     * 
     * @param string $location
     */
	public function setDefaultLocation($location)
	{
		$this->defaultLocation = $location;
	}
	
    /**
     * Gets the default location of the map
     * 
     * @return string
     */
	public function getDefaultLocation()
	{
		return $this->defaultLocation;
	}
	
    /**
     * Sets the type of the map
     * 
     * @param string $mapType
     */
	public function setMapType($mapType)
	{
		$this->mapType = $mapType;
	}
	
    /**
     * Gets the type of the map
     * 
     * @return string
     */
	public function getMapType()
	{
		return $this->mapType;
	}
	
    /**
     * Sets the content type associated with the map
     * 
     * @param string $content_type
     */
	public function setContentType($content_type)
	{
		$this->content_type = $content_type;
	}
	
    /**
     * Gets the content type associated with the map
     * 
     * @return string
     */
	public function getContentType()
	{
		return $this->content_type;
	}
}

/** 
 * This class represents a pushpin on the map
 * 
 * @package    Msft
 * @subpackage Bing
 */
class Msft_Bing_Pushpin
{
	/**
	 * ID of the pushpin
	 * @var int
	 */
	public $id = 0;
	
	/**
	 * Title of the pushpin
	 * @var string
	 */
	public $title;
	
	/**
	 * Description of the pushpin
	 * @var string
	 */
	public $description;
	
	/**
	 * Location (address) of the pushpin
	 * @var string
	 */
	public $location;
	
	/**
	 * Latitude of the pushpin
	 * @var float
	 */
	public $latitude;
	
	/**
	 * Longitude of the pushpin
	 * @var float
	 */
	public $longitude;
	
	/**
	 * Constructor
	 * Calls appropriate constructor based on the number of parameters
	 */
	function __construct()
	{
		$num = func_num_args();
		$args = func_get_args();
        if (method_exists($this, $f = '__construct'.$num)) 
        { 
            call_user_func_array(array($this, $f), $args); 
        } 
	} 
		
	/**
	 * Constructor 1
	 * @param int $id
	 * @param string $title
	 * @param string $desc
	 * @param string $location
	 */
	function __construct4($id, $title, $desc, $location)
	{
		$this->id = $id;
		$this->title = $title;
		$this->description = $desc;
		$this->location = $location;
	}
	
	/**
	 * Constructor 2
	 * @param int $id
	 * @param string $title
	 * @param string $desc
	 * @param float $latitude
	 * @param float $longitude 
	 */
	function __construct5($id, $title, $desc, $latitude, $longitude)
	{
		$this->id = $id;
		$this->title = $title;
		$this->description = $desc;
		$this->latitude = $latitude;
		$this->longitude = $longitude;		
	}
	
	
    /**
     * Sets the ID of the pushpin
     * 
     * @param int $id
     */
	public function setID($id)
	{
		$this->id = $id;
	}
	
    /**
     * Gets the ID of the pushpin
     * 
     * @return int
     */
	public function getID()
	{
		return $this->id;
	}
	
    /**
     * Sets the title of the pushpin
     * 
     * @param string $title
     */
	public function setTitle($title)
	{
		$this->title = $title;
	}
	
    /**
     * Gets the title of the pushpin
     * 
     * @return string
     */
	public function getTitle()
	{
		return $this->title;
	}
	
    /**
     * Sets the description of the pushpin
     * 
     * @param string $desc
     */
	public function setDescription($desc)
	{
		$this->description = $desc;
	}
	
    /**
     * Gets the description of the pushpin
     * 
     * @return string
     */
	public function getDescription()
	{
		return $this->description;
	}
	
    /**
     * Sets the location (address) of the pushpin
     * 
     * @param string $location
     */
	public function setLocation($location)
	{
		$this->location = $location;
	}
	
    /**
     * Gets the location (address) of the pushpin
     * 
     * @return string
     */
	public function getLocation()
	{
		return $this->location;
	}
	
    /**
     * Sets the latitude of the pushpin
     * 
     * @param float $latitude
     */
	public function setLatitude($latitude)
	{
		$this->latitude = $latitude;
	}
	
    /**
     * Gets the latitude of the pushpin
     * 
     * @return float
     */
	public function getLatitude()
	{
		return $this->latitude;
	}

	/**
     * Sets the longitude of the pushpin
     * 
     * @param float $longitude
     */
	public function setLongitude($longitude)
	{
		$this->longitude = $longitude;
	}
	
    /**
     * Gets the longitude of the pushpin
     * 
     * @return float
     */
	public function getLongitude()
	{
		return $this->longitude;
	}
}

?>