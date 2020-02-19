<?php

require_once  Configuration::Instance()->GetValue('application', 'gtfw_base').'main/lib/gtfw/dispatcher/Dispatcher.class.php';

Dispatcher::Instance()->Dispatch();
?>
