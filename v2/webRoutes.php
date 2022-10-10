<?php 
include_once("config.php");
$router = new Router(new Request);
$router->get('/', function($request) use ($db){
return 
<<<HTML
	 	<h1>Profile</h1>	
HTML;
});