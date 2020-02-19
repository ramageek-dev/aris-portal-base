<?php

require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
'main/lib/gtfw/security/authentication_method/service/Client.class.php';

class Authentication{
	private $mAppAddress;
	private $mApplicationId;
	private $mApplicationName;
	private $mDescription;
	private $mApplicationOwner;
	private static $mrInstance;
	
	function __construct(){
		$this->mAppAddress=$_SERVER['REMOTE_ADDR'];
		
		$this->fetchClientInfo();
	}
	
	function fetchClientInfo(){
		$clientInfo = Client::Instance()->GetClientByAddress($_GET['token'],$this->mAppAddress);
		if(!empty($clientInfo)){
			$this->mApplicationId = $clientInfo['ApplicationId'];
			$this->mApplicationName = $clientInfo['ApplicationName'];
			$this->mDescription = $clientInfo['Description'];
			$this->mApplicationOwner = $clientInfo['ApplicationOwner'];
			$this->mStatusService = true;
		}else 
			$this->mStatusService = false;
	}
	
	function getApplicationId(){
// 		if(empty($this->mApplicationId))
// 			return Configuration::Instance()->GetValue('application', 'application_id');
// 		else
			return $this->mApplicationId;
	}
	
	function getApplicationName(){
		return $this->mApplicationName;
	}
	
	function getDescription(){
		return $this->mDescription;
	}
	
	function getApplicationOwner(){
		return $this->mApplicationOwner;
	}
	
	function checkValidationAppClint(){
		return $this->mStatusService;
	}
	
	static function Instance() {
   	if (!isset(self::$mrInstance)){
   		$class_name = __CLASS__;
   		self::$mrInstance = new $class_name();
   	}
   
   	return self::$mrInstance;
   }
}
?>