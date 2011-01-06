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

	require_once dirname(__FILE__) . "/lib/Msft/Bing/Map/Map.php";
	require_once dirname(__FILE__) . "/lib/Msft/Bing/MySQLPersistenceHandler.php";
	
	$bingObj = new Msft_Bing_Map('aravind');
	$handler = new Msft_Bing_MySQLPersistenceHandler('localhost', 'mydb', 'root', '$Aztec123$');
  	$bingObj->registerPersistenceHandler($handler);
	
  	$bingObj->createConfigurationEntity();
	$configResult = $bingObj->loadConfiguration();
	
	// check if configuration was loaded in database
	if ($configResult == false)
	{
		$bingObj->setBingID('AjDSyPn4OPBYDEjOjZp19n3dGzLNBFes_W5gb-oOF_jdO8hmPB1pB9z4RLhH3tac');
		$props = new Msft_Bing_MapProperties('600','500','7');
		$props->setDefaultLocation('Bangalore,India');	
		$bingObj->setProperties($props);
	}
	
	$bingObj->createPushpinsEntity();
  	$loadResult = $bingObj->loadPushpins();
	$bingObj->listPushpins();  	
?>
<html>
<head>
</head>
<body>
<?php

// Check if data has been loaded from the database. If not, create sample data.
//if ($loadResult == false)
//{
//}

if (isset($_POST['adminSave']))
{
	$bingObj->saveConfiguration();
}
	
if (isset($_POST['addToDb']))
{  	
	echo '<script type="text/javascript">';
	$bingObj->displayMap();
	if ($bingObj->listPushpins() == null)
	{
		$bingObj->addPushpinAddress('delhi, india'); 
		$bingObj->addPushpinAddress('pune, india');
	}
	echo '</script>';
	$bingObj->savePushpins();
}
if (isset($_POST['listPushpins']))
{  	
	echo '<script type="text/javascript">';
	$bingObj->displayMap();	
	if ($bingObj->listPushpins() == null)
	{
		$bingObj->addPushpinAddress('delhi, india'); 
		$bingObj->addPushpinAddress('pune, india');
	}
	echo '</script>';	
  	$bingObj->listPushpins();
}

?>
<div>
	<div class=head style="width: 1246px; text-align: center; font-weight: bold;">Bing Maps test implementation</div>
	<form name="bingfrm" method="post" action="BingMapsSDKTest.php">
	<div class="left" style="float: left; width: 285px;">
	
		<div style="height: 30px;"> <input type="button" id="displayButton" value="Display Map" onclick="<?php $bingObj->displayMap();?>"/></div>
		<div style="height: 30px;"><input type="button" id="addButton" value="Add Pushpins" 
			onclick="document.getElementById('addButton').disabled=true;<?php $bingObj->addPushpinAddress('delhi, india'); $bingObj->addPushpinAddress('pune, india');?>" /></div>
		
		<div style="height: 30px;"><input type="button" id="remButton" value="Remove Pushpin" onclick="<?php $bingObj->removePushpinPinId('0');?>" /></div>		
		
		<div style="height: 30px;"><input type="button" id="gotoLocation" value="Go to Location" onclick="<?php $bingObj->goToLocationAddress('mumbai, india');?>"/> </div>
		
		<div style="height: 30px;"><input type="button" id="showDirection" value="Show Direction" onclick="<?php $bingObj->showDirections('mangalore, india', 'hubli, india'); $bingObj->goToLocationAddress('mangalore,india');?>"/></div>
		<div style="height: 30px;"><input type="button" id="getCoordinates" value="Get Current Coordinates" onclick="<?php $bingObj->getCurrentCoordinates('resultBox');?>"/> </div>	
		<div style="height: 30px;"><input type="button" id="getAddress" value="Get Current Address" onclick="<?php $bingObj->getCurrentAddress('resultBox');?>"/> </div>			
		
		<div style="height: 60px;"></div>

		<div style="height: 30px;"> <input type="button" id="displayButton" name="displayButton" value="Display pins" onclick="<?php $bingObj->displayLoadedPushpins();?>"/></div>
		  		
		<div style="height: 30px;"><input type="submit" id="listPushpins" value="List Pushpins" name="listPushpins"/></div>		  		
		<div style="height: 30px;"><input type="submit" id="addToDb" value="Save pin details" name="addToDb" /></div>
  		
  		<div style="height: 30px;"><input type="submit" id="adminSave" name="adminSave" value="Save configuration" /> </div>
  	
		<div style="height: 30px;"> <input type="button" id="resetButton" value="Reset" onclick="document.getElementById('addButton').disabled=false;<?php $bingObj->resetMap();?>"/></div>
			
		<input type="text" id="resultBox"/>		
	
	</div>
	</form>
	<div class="right" style="float: left; width: 900px;">
		<div>
			<div id="aravind" style="position:relative; width:775px; height:600px;"></div>			
		</div>
	</div>
</div>

</body>
</html>