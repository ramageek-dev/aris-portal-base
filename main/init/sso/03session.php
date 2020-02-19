<?php
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/gtfw/session/SessionSso.class.php';

SessionSso::Instance()->DispatchRequest();
?>
