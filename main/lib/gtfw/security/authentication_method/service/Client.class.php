<?php

class Client extends Database{
	private static $mrInstance;
	function __construct($connectionNumber = 0){
		
		$type = Configuration::Instance()->GetValue('application', 'db_conn','0','db_type');
		if($type=="mysqli")
			$type="mysqlt";
		
		$this->mSqlFile = Configuration::Instance()->GetValue('application', 'gtfw_base') .
		'main/lib/gtfw/security/authentication_method/service/adodb/'.$type.'/client.sql.php';
		
		parent::__construct($connectionNumber);
	}
	
	#gtfwMethodOpen
	public function GetClientByAddress($token,$ip){
 		$privateClientStatus="private";
		
 		$privateClient = Configuration::Instance()->GetValue('application', 'service');
 		if(!empty($privateClient))
 			$privateClientStatus = $privateClient['client_private'];
 		
 		if($privateClientStatus=="publish")
 			return 1;
		
// 		if(!empty($privateClientStatus))
// 			$privateClientStatus="1";		
		
		$result = $this->Open($this->mSqlQueries['get_client_app_by_address'], array($token,$ip));
		if(!empty($result))
			return $result[0];
		else
			return 0;
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
