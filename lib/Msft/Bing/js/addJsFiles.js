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

function includeJS(jsPath){
  var js = document.createElement("script");
  js.setAttribute("type", "text/javascript");
  js.setAttribute("src", jsPath);
  document.getElementsByTagName("head")[0].appendChild(js);
}

//includeJS("http://dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=6.2");

includeJS("http://ecn.dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=6.3");
includeJS("lib/Msft/Bing/js/jquery-1.2.6.js");
includeJS("lib/Msft/Bing/js/maps.js");