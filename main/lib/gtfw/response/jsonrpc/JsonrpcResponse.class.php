<?php

require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/response/jsonrpc/JsonRPCError.class.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/response/jsonrpc/JsonRPCExceptions.class.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/response/jsonrpc/RPCResponse.class.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/response/jsonrpc/JsonRPCServer.class.php';


//require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/response/jsonrpc/jsonrpc.class.php';
/*
 * Service Server Only
*
*/
abstract class JsonRPCResponse implements ResponseIntf {

	public function __construct() {

	}
	public function __destruct() {

	}
	public function getCallableMethodNames() {
		$methodNames = array();
		$reflection = new ReflectionClass(ucfirst(Dispatcher::Instance()->mAction).ucfirst(Dispatcher::Instance()->mSubModule));
		foreach($reflection->getMethods() as $method) {
			if($method->class==ucfirst(Dispatcher::Instance()->mAction).ucfirst(Dispatcher::Instance()->mSubModule))
				$methodNames[$method->name] = $method->getParameters();
		}
		return $methodNames;
	}
	public function getCallableMethodParameters($methodName) {
		$reflection = new ReflectionClass($this);
		foreach($reflection->getMethods() as $method) {
			if($method->name == $methodName) {
				return $method->getParameters();
			}
		}
	}


	private function getAnnotationVariables($request,$service) {
		$collectedAnnotations = array();
		$method = new ReflectionMethod(get_class($service),$request->method);
		$methodComment = $method->getDocComment();

		$bracketContentStart = strpos($methodComment,'(');
		$bracketContentEnd = strpos($methodComment,')')-$bracketContentStart-1;

		// () pair not found
		if(!$bracketContentStart || !$bracketContentEnd) {
			return $collectedAnnotations;
		}

		$rawAnnotations = substr($methodComment,$bracketContentStart+1,$bracketContentEnd);
		$annotationsArray = explode(',',$rawAnnotations);
		foreach($annotationsArray as $key => $value) {
			$withoutUnecessaryCharacters = str_replace(
					array(
							' ', # additional space
							'*', # an automate generated * character @ block comments when you hit enter
							'\'',# remove duplicating
							'"'),# remove duplicating
					'',$value);

			$keyValuePair = explode('=',$withoutUnecessaryCharacters);
			if(count($keyValuePair)!=2) {
				return false;
			} else {
				$collectedAnnotations[$keyValuePair[0]] = $keyValuePair[1];
			}
		}
		return $collectedAnnotations;
	}

	function Send(){
		
		
		$objRPCServer = new JsonRpcServer(file_get_contents("php://input"));
		
		eval('$objRPCServer->addService(new '.ucfirst(Dispatcher::Instance()->mAction).ucfirst(Dispatcher::Instance()->mSubModule).'());');
		$objRPCServer->processingRequests();
		
	}

	function &GetHandler() {
		return $this;
	}
	
}


?>
