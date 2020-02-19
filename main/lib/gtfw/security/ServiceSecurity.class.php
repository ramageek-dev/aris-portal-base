<?php

require_once Configuration::Instance()->GetValue('application', 'gtfw_base').'main/lib/gtfw/security/authentication_method/service/Authentication.class.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base').'main/lib/gtfw/security/authorization_method/service/Authorization.class.php';

class ServiceSecurity {
	private static $mrInstance;
	private function __construct(){

	}

	public function AllowedToAccess($module, $submodule, $action, $type){
		if(Authentication::instance()->checkValidationAppClint() && Authorization::instance()->IsAllowedToAccess($module, $submodule, $action, $type, Authentication::instance()->getApplicationId())){
			return true;
		}elseif(!Authentication::instance()->checkValidationAppClint() && Authorization::instance()->IsAllowedToAccess($module, $submodule, $action, $type, Authentication::instance()->getApplicationId()))
		{
			return true;
		}else
		{
			$dbMsg = SysLog::Instance()->getAllError();
			if(!empty($dbMsg)){
				echo "<pre>";
				for ($i=0;$i<count($dbMsg);$i++){
					echo $dbMsg[$i];
				}
				echo "</pre>";
			}
			Log::Instance()->SendLog('Gagal akses, request denied');
			die('You don\'t have permission to access this service');
		}
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
