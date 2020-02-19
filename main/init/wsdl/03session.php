<?php
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/gtfw/session/save_handler/SessionSaveHandlerIntf.intf.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/gtfw/session/Session.class.php';

Session::Instance()->PrepareSaveHandler();
Session::Instance()->Start();

//getgetConfig From DB must get after session created
ConfigurationHelper::InstanceClass()->GetAllValues();
?>
