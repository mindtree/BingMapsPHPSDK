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
 * @copyright 2010 Microsoft
 * @license   New BSD License (BSD) http://bingmapsphp.codeplex.com/license
 * @version   SVN: $Id: Map.php 50 2010-11-10 10:00:00Z cal $
 * @link      http://bingmapsphp.codeplex.com
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
