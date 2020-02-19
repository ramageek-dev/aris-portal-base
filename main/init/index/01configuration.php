<?php
require_once GTFW_BASE_DIR . '/main/lib/gtfw/configuration/Configuration.class.php';
require_once GTFW_BASE_DIR . '/main/lib/gtfw/configuration/ConfigurationHookIntf.intf.php';

$timezone = ini_get('date.timezone');

if(empty($timezone)){
	$timezone = Configuration::Instance()->GetValue('application','timezone');
	if(empty($timezone))
		$timezone='Asia/Jakarta';
		ini_set('date.timezone',$timezone);
}

Configuration::Instance()->SetConfigDirectory(GTFW_APP_DIR . '/config/');
Configuration::Instance()->Load('application.conf.php', 'default');

require_once GTFW_BASE_DIR .'/main/lib/gtfw/database/Database.class.php';
require_once GTFW_BASE_DIR . '/main/lib/gtfw/configuration/ConfigurationHelper.class.php';


$config_dir = GTFW_APP_DIR . 'config/*.ini';

foreach (glob($config_dir) as $value) {
	$value = str_replace(GTFW_APP_DIR . 'config/','',$value);
	Configuration::Instance()->Load($value, 'ini');
}

// if (Configuration::Instance()->Load('static.conf.php', 'shm') === false) {
//    Configuration::Instance()->Load('static.conf.php', 'default');
//    SharedMemory::Instance()->Set('static.conf.php', Configuration::$mValues['static']);
// }

// warning: these lines below soon will be obsolete!
 require_once GTFW_BASE_DIR . '/main/lib/gtfw/configuration/GTFWConfiguration.class.php';
 
 #GTFWConfiguration::GetValue('application',array('db_conn',0,'db_type'));


// GTFWConfiguration::SetConfigDirectory(GTFW_APP_DIR . '/config/');
// GTFWConfiguration::Load('application.conf.php', 'default');
// foreach (glob($config_dir) as $value) {
// 	$value = str_replace(GTFW_APP_DIR . 'config/','',$value);
// 	GTFWConfiguration::Load($value, 'ini');
// }


// if (GTFWConfiguration::Load('static.conf.php', 'shm') === false) {
//    GTFWConfiguration::Load('static.conf.php', 'default');
//    SharedMemory::Instance()->Set('static.conf.php', GTFWConfiguration::$mValues['static']);
// }
 
?>
