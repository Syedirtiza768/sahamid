<?php 
	$AllowAnyone = true;

	interface IRequest{
    	public function getBody();
	}

	class Request implements IRequest {
 	 	function __construct() {
    		$this->bootstrapSelf();
  		}
  	
	  	private function bootstrapSelf(){
	    	foreach($_SERVER as $key => $value){
	      		$this->{$this->toCamelCase($key)} = $value;
	    	}
	  	}

	  	private function toCamelCase($string){
	    	$result = strtolower($string);
	    	preg_match_all('/_[a-z]/', $result, $matches);
	    	
	    	foreach($matches[0] as $match) {
	        	$c = str_replace('_', '', strtoupper($match));
	        	$result = str_replace($match, $c, $result);
	    	}
	   	 	
	   	 	return $result;
	  	}
	  
	  	public function getBody(){
		    if($this->requestMethod === "GET"){
		      	return;
		    }
		    if ($this->requestMethod == "POST"){
	      		$result = array();
	      		foreach($_POST as $key => $value){
	        		$result[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
	      		}
	      		return $result;
	    	}
	    	return $body;
	  	}
	}

	class Router{
	  	private $request;
	  	private $supportedHttpMethods = array(
	    	"GET",
	    	"POST"
	  	);
  
  		function __construct(IRequest $request){
   			$this->request = $request;
  		}
  
  		function __call($name, $args){
    		list($route, $method) = $args;
    		if(!in_array(strtoupper($name), $this->supportedHttpMethods)){
      			$this->invalidMethodHandler();
    		}
    		$this->{strtolower($name)}[$this->formatRoute($route)] = $method;
 		}
 
  		private function formatRoute($route){
    		$result = rtrim($route, '/');
    		if ($result === ''){
      			return '/';
    		}
    		return $result;
  		}

  		private function invalidMethodHandler(){
    		header("{$this->request->serverProtocol} 405 Method Not Allowed");
  		}

  		private function defaultRequestHandler(){
    		header("{$this->request->serverProtocol} 404 Not Found");
  		}
 
  		function resolve(){
    		$methodDictionary = $this->{strtolower($this->request->requestMethod)};
    		$formatedRoute = $this->formatRoute($this->request->requestUri);
    		$formatedRoute = explode("/sahamid/v2/router.php",$formatedRoute)[1];
    		$formatedRoute = $formatedRoute == "" ? "/":$formatedRoute;
    		$method = $methodDictionary[$formatedRoute];
    		if(is_null($method)){
      			$this->defaultRequestHandler();
      			return;
    		}
    		echo call_user_func_array($method, array($this->request));
  		}
  	
  		function __destruct(){
    		$this->resolve();
  		}
	}
	
	require_once('webRoutes.php');