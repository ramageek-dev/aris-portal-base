<?php
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/gtfw/session/save_handler/SessionSaveHandlerIntf.intf.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/gtfw/session/Session.class.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/gtfw/session/SessionSso.class.php';

Session::Instance()->PrepareSaveHandler();
Session::Instance()->Start();

SessionSso::Instance()->Start();

$connection = Configuration::Instance()->GetValue('application','db_conn');

if(isset($connection[0])){
//getgetConfig From DB must get after session created
	ConfigurationHelper::InstanceClass()->GetAllValues();
}

?>
