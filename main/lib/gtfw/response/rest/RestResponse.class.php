<?php

require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/response/rest/Rest.php';

abstract class RestResponse implements ResponseIntf {

	protected $responseStatus;

	public function __construct() {
		
	}

	final public function getResponseStatus() {
		return $this->responseStatus;
	}

	public function checkAuth() {
		return true;
	}

	// @codeCoverageIgnoreStart
	abstract public function get();
	abstract public function post();
	abstract public function put();
	abstract public function delete();
	// @codeCoverageIgnoreEnd
	
	function Send(){
		$rest = new Rest();
		$rest->process();
		
		//$this->Display();
	}
	
	function &GetHandler() {
		return $this;
	}

}

?>
