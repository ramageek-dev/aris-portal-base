<?php
class Log 
{

	public static $mrInstance;

	function __construct ()
	{
		require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
		'main/lib/gtfw/log/Logdb.class.php'; 
	}
	
	private function SendLogDb($user, $ip, $log)
	{
		$objLog = new LogDb();
		$result = $objLog->send($user, $ip, $log);
		return $result;
	}
	
	public function SendLog($log)
	{
	   if (class_exists('Security')){
		   $user = Security::Instance()->mAuthentication->GetCurrentUser()->GetUserName();
		}

		if(empty($user))
			$user = GTFWConfiguration::GetValue('application','default_user');
		$ip = $_SERVER['REMOTE_ADDR'];
		$result = $this->SendLogDb($user, $ip, $log);
		return $result;
	}
	
	static function Instance() {
      if (!isset(self::$mrInstance))
         self::$mrInstance = new Log();

      return self::$mrInstance;
   }

}
?>
