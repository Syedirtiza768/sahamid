<?php
/* $Id: header.inc 6310 2013-08-29 10:42:50Z daintree $ */

	// Titles and screen header
	// Needs the file config.php loaded where the variables are defined for
	//  $RootPath
	//  $Title - should be defined in the page this file is included with
	if (!isset($RootPath)){
		$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
		if ($RootPath == '/' OR $RootPath == "\\") {
			$RootPath = '';
		}
	}

	$ViewTopic = isset($ViewTopic)?'?ViewTopic=' . $ViewTopic : '';
	$BookMark = isset($BookMark)? '#' . $BookMark : '';
	$StrictXHTML=False;

	if (!headers_sent()){
		if ($StrictXHTML) {
			header('Content-type: application/xhtml+xml; charset=utf-8');
		} else {
			header('Content-type: text/html; charset=utf-8');
		}
	}
	if($Title == _('Copy a BOM to New Item Code')){//solve the cannot modify heaer information in CopyBOM.php scritps
		ob_start();
	}
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

	echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title> S A HAMID ERP</title>';
	//echo '<link rel="shortcut icon" href="'. $RootPath.'/favicon.ico" />';
	//echo '<link rel="icon" href="' . $RootPath.'/favicon.ico" />';
	if ($StrictXHTML) {
		echo '<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />';
	} else {
		echo '<meta http-equiv="Content-Type" content="application/html; charset=utf-8" />';
	}
	echo '<link href="' . $RootPath . '/css/'. $_SESSION['Theme'] .'/default.css" rel="stylesheet" type="text/css" />';
	echo '<link href="' . $RootPath . '/css/'. $_SESSION['Theme'] .'/foundation.css" rel="stylesheet" type="text/css" />';
	echo '<link href="' . $RootPath . '/css/css/menubar.css" rel="stylesheet" type="text/css" />';
	
	echo '<script type="text/javascript" src = "'.$RootPath.'/javascripts/MiscFunctions.js"></script>';
	echo'<script type="text/javascript" type="text/javascript" src="includes/jquery.min.js"></script>';
	echo'<script type="text/javascript" src="includes/jquery.tools.min.js"></script>';

//	echo'<script type="text/javascript" src="scripts/jquery-1.3.2.js"></script>';
//	echo'<script src="http://cdn.jquerytools.org/1.2.7/full/jquery.tools.min.js"></script>';
    echo'<script type="text/javascript" src="scripts/jquery-ui-1.7.2.custom.min.js"></script>';
    echo'<link rel="Stylesheet" type="text/css" href="style/jqueryui/ui-lightness/jquery-ui-1.7.2.custom.css" />';
//	echo'	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>';
//	echo'	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>';
	echo'	<script src="editor.js"></script>';
	
//	echo'	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">';
//	echo'	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">';
//	echo'	<link href="editor.css" type="text/css" rel="stylesheet"/>';
 echo'    <script type="text/javascript" src="textboxio/textboxio.js"></script>';
  echo'  <link rel="stylesheet" type="text/css" href="example.css" />';
echo'  <script>';
     
//echo'       var instantiateTextbox = function () {';
// echo'        textboxio.replaceAll("textarea", {';
//echo'           paste: {';
// echo'            style: "clean"';
// echo'          },';
// echo'          css: {';
// echo'            stylesheets: ["example.css"]';
//  echo'         }';
// echo'        });';
// echo'      };';

//  echo'  </script>';
 
echo'<script type="text/javascript">';

echo'function saveScrollPositions(theForm) {';

echo'if(theForm) {';

echo'var scrolly = typeof window.pageYOffset != "undefined" ? window.pageYOffset : document.documentElement.scrollTop;';

echo'var scrollx = typeof window.pageXOffset != "undefined" ? window.pageXOffset : document.documentElement.scrollLeft;';

echo'theForm.scrollx.value = scrollx;';

echo'theForm.scrolly.value = scrolly;';

echo'}';

echo'}';

echo'</script>';
	
	echo '</head>';
echo'	<script type="text/javascript">';
echo'	<!--';
echo'	cookieName="page_scroll"';
echo'	expdays=365';

// An adaptation of Dorcht's cookie functions.

echo'	function setCookie(name, value, expires, path, domain, secure){';
echo'	if (!expires){expires = new Date()}';
echo'	document.cookie = name + "=" + escape(value) + ';
echo'((expires == null) ? "" : "; expires=" + expires.toGMTString()) +';
echo'((path == null) ? "" : "; path=" + path) +';
echo'((domain == null) ? "" : "; domain=" + domain) +';
echo'((secure == null) ? "" : "; secure")';
echo'}';

echo'function getCookie(name) {';
echo'var arg = name + "="';
echo'var alen = arg.length';
echo'var clen = document.cookie.length';
echo'var i = 0';
echo'while (i < clen) {';
echo'var j = i + alen';
echo'if (document.cookie.substring(i, j) == arg){';
echo'return getCookieVal(j)';
echo'}';
echo'i = document.cookie.indexOf(" ", i) + 1';
echo'if (i == 0) break';
echo'}';
echo'return null';
echo'}';

echo'function getCookieVal(offset){';
echo'var endstr = document.cookie.indexOf (";", offset)';
echo'if (endstr == -1)';
echo'endstr = document.cookie.length';
echo'return unescape(document.cookie.substring(offset, endstr))';
echo'}';

echo'function deleteCookie(name,path,domain){';
echo'document.cookie = name + "=" +';
echo'((path == null) ? "" : "; path=" + path) +';
echo'((domain == null) ? "" : "; domain=" + domain) +';
echo'"; expires=Thu, 01-Jan-00 00:00:01 GMT"';
echo'}';

echo'function saveScroll(){ // added function';
echo'var expdate = new Date ()';
echo'expdate.setTime (expdate.getTime() + (expdays*24*60*60*1000)); // expiry date';

echo'var x = (document.pageXOffset?document.pageXOffset:document.body.scrollLeft)';
echo'var y = (document.pageYOffset?document.pageYOffset:document.body.scrollTop)';
echo'Data=x + "_" + y';
echo'setCookie(cookieName,Data,expdate)';
echo'}';

echo'function loadScroll(){ // added function';
echo'inf=getCookie(cookieName)';
echo'if(!inf){return}';
echo'var ar = inf.split("_")';
echo'if(ar.length == 2){';
echo'window.scrollTo(parseInt(ar[0]), parseInt(ar[1]))';
echo'}';

echo'}';

// add onload="loadScroll()" onunload="saveScroll()" to the opening BODY tag

// -->
echo'</script>';
	echo '<body onload="loadScroll()" onunload="saveScroll()">';
	echo '<input type="hidden" name="Lang" id="Lang" value="'.$Lang.'" />';

    echo '<div id="CanvasDiv">';
	echo '<div id="HeaderDiv">';
	echo '<div id="HeaderWrapDiv">';


	if (isset($Title)) {


		echo '<div id="AppInfoDiv">'; //===HJ===
			echo '<div id="AppInfoCompanyDiv">';
				echo '<img src="'.$RootPath.'/css/'.$Theme.'/images/company.png" title="'._('Company').'" alt="'._('Company').'"/>' . stripslashes($_SESSION['CompanyRecord']['coyname']);
			echo '</div>';
			echo '<div id="AppInfoUserDiv">';
				echo '<a href="#"><img src="'.$RootPath.'/css/'.$Theme.'/images/user.png" title="User" alt="'._('User').'"/>' . stripslashes($_SESSION['UsersRealName']) . '</a>';
			echo '</div>';
			echo '<div id="AppInfoModuleDiv">';
				// Make the title text a class, can be set to display:none is some themes
				echo $Title;
			echo '</div>';
		echo '</div>'; // AppInfoDiv


		echo '<div id="QuickMenuDiv" style = "padding-right:250px;"><ul>';

		echo '<li><a href="'.$RootPath.'/index.php">' . _('Main Menu') . '</a></li>'; //take off inline formatting, use CSS instead ===HJ===

		if (count($_SESSION['AllowedPageSecurityTokens'])>1){
			
			echo '<li><a href="'.$RootPath.'/SelectProduct.php">' ._('Items')     . '</a></li>';
			
			
		}

		echo '<li><a href="'.$RootPath.'/Logout.php" onclick="return confirm(\''._('Are you sure you wish to logout?').'\');">' . _('Logout') . '</a></li>';

		echo '</ul></div>'; // QuickMenuDiv
	}
	echo '</div>'; // HeaderWrapDiv
	echo '</div>'; // Headerdiv
	echo '<div id="BodyDiv">';
	echo '<div id="BodyWrapDiv">';

?>
