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
 * @subpackage Bing
 * @author    Mindtree 
 * @copyright 2011 Mindtree Limited
 * @license    GPL v2 License https://github.com/mindtree/BingMapsPHPSDK
 * @link       https://github.com/mindtree/BingMapsPHPSDK
 * @version   SVN: $Id: Map.php 50 2010-11-10 10:00:00Z cal $
 *
 */

if (isset($_GET['CurrentLocation']))
{
	echo $_GET['CurrentLocation'];
}
else if (isset($_GET['CurrentAddress']))
{
	echo $_GET['CurrentAddress'];
}
if (isset($_GET['Location']))
{
	echo $_GET['Location'];
}
else if (isset($_GET['Address']))
{
	echo $_GET['Address'];
}

?>
