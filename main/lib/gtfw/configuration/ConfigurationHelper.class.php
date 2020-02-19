<?php
	require_once Configuration::Instance()->GetValue('application', 'gtfw_base').'main/lib/gtfw/configuration/ConfigurationDb.class.php';

	class ConfigurationHelper extends Configuration{
		static $mrInstance;
		function __construct(){}
		
		function GetAllValues(){
			$result = ConfigurationDb::Instance()->GetAllConfig();
			
			if(!empty($result)){
				for($i=0; $i<count($result);$i++){
					Configuration::Instance()->addNew($result[$i]['configName'], $result[$i]['configKode'], $result[$i]['configValue']);
				}
			}
			
			if(ConfigurationDb::Instance()->CheckGtfwMenuTable()){
				Configuration::Instance()->addNew('app_version', 'version', '3');
			}else{
				Configuration::Instance()->addNew('app_version', 'version', '2');
			}
		}
		
		static function InstanceClass() {
			if (!isset(self::$mrInstance))
				self::$mrInstance = new ConfigurationHelper();
		
			return self::$mrInstance;
		}
	}
?>