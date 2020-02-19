<?php

class Authorization extends Database{
	private static $mrInstance;
	
	function __construct($connectionNumber=0){
		
		$type = Configuration::Instance()->GetValue('application', 'db_conn','0','db_type');
		if($type=="mysqli")
			$type="mysqlt";
		
		$this->mSqlFile = Configuration::Instance()->GetValue('application', 'gtfw_base') .
		'main/lib/gtfw/security/authorization_method/service/adodb/'.$type.'/authorization.sql.php';
		parent::__construct($connectionNumber);
	}
	
	public function IsAllowedToAccess($module, $subModule, $action, $type, $applicationId){	
		#gtfwDbOpen
		$result = $this->open($this->mSqlQueries['allowed_to_access'],array($applicationId,$module,$subModule,$action,$type));
		
		if($result['0']['access_right']>0)
			return true;
		else
			return false;
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
