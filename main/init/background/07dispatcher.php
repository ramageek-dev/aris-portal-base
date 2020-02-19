<?php
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/gtfw/dispatcher/Dispatcher.class.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/gtfw/dispatcher/CliDispatcher.class.php';

CliDispatcher::Instance()->Dispatch($module, $submodule, $action, $type);
?>
