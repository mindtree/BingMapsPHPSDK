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

// Set these variables to change beginning map location
var beginLatitude = 47.6;
var beginLongitude = -122.33;
var beginZoom = 10;
var beginMapType = 'h';
var bingMapLibPath = '';

// Global variable to initialize VEMap in
var map = null;
// Global variable to track current pinId
var pinid = 0;
// Should we render the map now or wait for call back
var waitingForCallback = false;

// To hold values for call back function
var tmpTitle = new Array();
var tmpDescription = new Array();
var credentialFlag = true;
var gotoLocation = null;
var shapeID;
var shapeIdArr = new Array();

var eObj = null;
var divName;
var moving = '';
var ajaxRequest = null;  // The variable that makes Ajax possible!
var resultTextboxID;

var addId = null;
var defId = null;

function credentialsError()
{
	credentialFlag = false;
	alert("The map credentials are invalid.");
	return false;
}

function credentialsValid()
{
   credentialFlag = true;
   //alert("The map credentials are valid.");
   return true;
}

//Browser Support Code
function initializeAjax()
{
	try{
		// Opera 8.0+, Firefox, Safari
		ajaxRequest = new XMLHttpRequest();
	} catch (e){
		// Internet Explorer Browsers
		try{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e){
				// Something went wrong
				alert("Your browser does not support Ajax!");
				return false;
			}
		}
	}
}


//Initialize map
function InitializeMap(mapdiv, zomLevel, mapwidth, mapheight, mapType)
{	
	if(map == null || map == '') 
	{
		map = new VEMap(mapdiv);
		map.AttachEvent("onmousedown", mouseDownHandler);
		map.AttachEvent("onmouseup", mouseUpHandler);	
		initializeAjax();
	}
			
	if(zomLevel!=null && zomLevel!='')
		beginZoom = zomLevel;
		
	if(mapType!=null && mapType!='')
		beginMapType = mapType;
	
	divName = mapdiv;
	
	//reset the variables
	pinid = 0;
	shapeIdArr=[];
}

function mouseDownHandler(e) 
{
	 if ((e.leftMouseButton) || (e.rightMouseButton))
	 {
		 eObj = e;
		 DeleteControlAdminR();
	     moving = true;		 
	 }
}

function mouseUpHandler(e)
{
	    if (moving && (e.leftMouseButton || e.rightMouseButton)) 
	        moving = false;
}

function DeleteControlAdminR()
{
   var controlExists = document.getElementById("mapAdminControl");

   if (controlExists != null)
      map.DeleteControl(controlExists); 
}

function AddMapControl(content)
{
	if (eObj.rightMouseButton)
	{
       var x = eObj.mapX;
	   var y = eObj.mapY;
	   x = Number(x)+1;
	   y = Number(y)+1;
		
	   DeleteControlAdminR();

	   var ele = document.createElement("div"); 
       ele.id = "mapAdminControl"; 
       ele.style.top = y+"px"; 
       ele.style.left = x+"px";            
       ele.style.border = "1px solid black";
       ele.style.background = "lightcyan";
       ele.innerHTML = content;
        
       map.AddControl(ele);	
       
       moving = true;
	}
}

// Retrieve current latitude longitude on the map pointed by the mouse
function GetCurrentLocation(textboxID)
{
	if ((map != null) && (eObj != null))
	{
		var x = eObj.mapX;
	    var y = eObj.mapY;
	    var pixel = new VEPixel(x, y);
	    var latLon = map.PixelToLatLong(pixel);
	    document.getElementById(textboxID).value = latLon;	   
	    DeleteControlAdminR();
	}
}

//Retrieve latitude longitude corresponding to the given address 
function GetLocation(address, textboxID)
{
	if (map != null)
	{
		resultTextboxID = textboxID;		
		map.Find(null, address, null, null, 0, 1, false, false, true, true, getLocationCallback);  
	}
}

//Call back function for when a map location is found
function getLocationCallback(layer, resultsArray, places, hasMore, veErrorMessage)
{	
	if(map == null || map == '')
		return;

	if(places != null)
	{
	    var latLon = places[0].LatLong;
		document.getElementById(resultTextboxID).value = latLon;
    }
	else
		alert('location not found');
}

//Retrieve current address on the map pointed by the mouse
function GetCurrentAddress(textboxID)
{
	if ((map != null) && (eObj != null))
	{
		var x = eObj.mapX;
	    var y = eObj.mapY;
	    var pixel = new VEPixel(x, y);
	    var latLon = map.PixelToLatLong(pixel);
	    resultTextboxID = textboxID;
	    map.FindLocations(latLon, getCurrentAddressCallback);
	}
}

//Retrieve current address on the map pointed by the mouse
function GetAddress(latitude, longitude, textboxID)
{
	if (map != null)
	{
	    var latLon = new VELatLong(latitude, longitude);
	    resultTextboxID = textboxID;
	    map.FindLocations(latLon, getAddressCallback);
	}
}


function getCurrentAddressCallback(locations)
{
	if (locations)
	{
		var address;
		if (locations.length > 1)
		{
			   //alert('Ambigious location.');
			   address = locations[0].Name; 
		}
		address = locations[0].Name; 
		document.getElementById(resultTextboxID).value = address;		
	}
}

function getAddressCallback(locations)
{
	if (locations)
	{
		var address;
		if (locations.length > 1)
		{
			   //alert('Ambigious location.');
			   address = locations[0].Name; 
		}
		address = locations[0].Name; 
		document.getElementById(resultTextboxID).value = address;	
	}
}


function ResetMap()
{	
	if(map != null && map != '')
	{
		map.Clear();		
		//map.LoadMap(new VELatLong(beginLatitude, beginLongitude), beginZoom, beginMapType, false);
		//map.SetZoomLevel(beginZoom);
		DeleteControlAdminR();
		pinid = 0;
		addId = null;
		defId = null;
		shapeIdArr=[];
				
	}	
}

// Initialize map
// mapDivName - Div container for map
function GetMap()
{
	if(map == null || map == '')
		return;

	map.LoadMap(new VELatLong(beginLatitude, beginLongitude), beginZoom, beginMapType, false);
    map.SetZoomLevel(beginZoom);
}


//Add pushpin
function AddPushpin(title, description, latLon) 
{
	if(map == null || map == '')
		return;

    var shape = new VEShape(VEShapeType.Pushpin, latLon);
    shape.SetTitle(title);
    shape.SetDescription(description);
   
    map.AddShape(shape);
    
	addId = shape.GetID();
    shapeIdArr[pinid] = shape.GetID();
    pinid++;
}

function AddPushpinLatLon(latitude, longitude, defaultpin)
{
	if(map == null || map == '')
		return;

	var latLon = new VELatLong(latitude, longitude);
    var shape = new VEShape(VEShapeType.Pushpin, latLon);   
    map.AddShape(shape);
    
    if(defaultpin == 'no')
	{
		addId = shape.GetID();
		shapeIdArr[pinid] = shape.GetID();
		pinid++;
	}
    if(defaultpin == 'yes')
    {    	
    	if(typeof(defId) != 'undefined' && defId != null)
    	{
    		var shap = map.GetShapeByID(defId);
    		map.DeleteShape(shap);
    	}
    	defId = shape.GetID();
    }    
}

function getShapeId(title, description)
{
	if(eObj.elementID != null)
	{
		var shap = map.GetShapeByID(eObj.elementID);
		shap.SetTitle(title);
		shap.SetDescription(description);
	}
	else
	{
		alert('Map Object is null');
	}
	
}

function AttachDetails(title, description)
{
	var shap = map.GetShapeByID(addId);
	shap.SetTitle(title);
	shap.SetDescription(description);
	
	DeleteControlAdminR();
}

function FetchPinDetails(num)
{
	
	var shap = map.GetShapeByID(shapeIdArr[num]);
	var tit = shap.GetTitle();
	var desc = shap.GetDescription();
		
	return (tit + ';' + desc);
}

function inArray(needle, bundle)
{
  var len = bundle.length;

  for(var i = 0; i < len; i++) {
        if(i == needle) 
            return true;
    }
  return false;
}

function GetPinId()
{	
	 if(eObj.elementID != null){		 
		 var arrIndex = DeleteShapess(eObj.elementID);
	 }	 
	 return arrIndex;
}

//Remove pushpin
function RemPushpin(id)
{
	if(map == null || map == '')
		return;

	if(inArray(id, shapeIdArr))
	{
		var shap = map.GetShapeByID(shapeIdArr[id]);
		map.DeleteShape(shap);
		
		for(var i = 0; i < shapeIdArr.length; i++)
		{
		 	if(map.GetShapeByID(shapeIdArr[i]) == null )
		 	{
		 		shapeIdArr.splice(i, 1);
		 	} 	
		}		
	}
	else
		alert('Pushin with given ID not found');
}

function getPinNumber()
{
	if(shapeIdArr.length == 0)
        return 0;
    else
	 	return(shapeIdArr.length);
}

function DeleteShapess(PinId)
{       	 
  	 var shap = map.GetShapeByID(PinId);
	 map.DeleteShape(shap);
	 
	 for(var i=0;i<shapeIdArr.length;i++)
	 {
	 	if(map.GetShapeByID(shapeIdArr[i]) == null )
	 	{
	 		shapeIdArr.splice(i,1);	 		
	 		DeleteControlAdminR();
	 		return i;
	 	}
	 }
	 DeleteControlAdminR();
	 return false;
}


// Try to find this location and add a pushpin if successfull
// Location - string value of location to add to map
function FindAndAddPin(id, title, description, location) 
{
	if(map == null || map == '')
		return;

	if(title == null || description == null || location == null || 
	   title.length == 0 || description.length == 0 || location.length == 0) {
	    alert('Title, Description & Location are all required fields');
    }
    else 
    {
        try 
        {        	
        	tmpTitle.push(title);
    	    tmpDescription.push(description);    	    
            map.Find(null, location,null,null,0,1,false,false,true,true,mapFindCallback);   
        }
        catch (e) {
            alert(e.message);
            waitingForCallback = false; 
        }
        
    }
}

function GotoUserLocation(latitude, longitude) 
{
	if(map == null || map == '')
		return;

	var latLon = new VELatLong(latitude, longitude);
	map.SetCenter(latLon);
}

function GotoUserLocation(gotoLoc)
{
	if(map == null || map == '')
		return;	
	gotoLocation = gotoLoc;	
	map.Find(null, gotoLocation,null,null,0,1,false,false,true,true,mapFindCallback);
}

// Call back function for when a map location is found
function mapFindCallback(layer, resultsArray, places, hasMore, veErrorMessage)
{
	if(map == null || map == '')
		return;

	if(places != null)
	{
	    var latLon = places[0].LatLong;
	    
	    if(gotoLocation != null)
	    {
	    	 map.SetCenter(latLon);
	    	 gotoLocation = null;
	    }
	    else
	    {	    	
	    	AddPushpin(tmpTitle.shift(), tmpDescription.shift(), latLon);		    
	    }	
    }
	else
		alert('Location not found');
}

//Display the routemap from 'from' to 'to'
function GetRouteMap(from, to)
{
	if(map == null || map == '')
		return;

   var locations;

   locations = new Array(from, to);
   
   var options = new VERouteOptions;
   options.DrawRoute      = true;

   // So the map doesn't change:
   options.SetBestMapView = false;

   // Call this function when map route is determined:
   //options.RouteCallback  = ShowTurns;

   // Show as miles
   options.DistanceUnit   = VERouteDistanceUnit.Mile;

   // Show the disambiguation dialog
   options.ShowDisambiguation = true;

   map.GetDirections(locations, options);
}

function ValidateBing(bingid)
{
	if(map == null || map == '')
		return;
	
    map.SetCredentials(bingid);
	
	// Attach the credentials events
	map.AttachEvent("oncredentialserror", credentialsError);
	map.AttachEvent("oncredentialsvalid", credentialsValid);
}
