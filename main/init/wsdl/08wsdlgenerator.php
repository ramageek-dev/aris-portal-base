<?php
require_once  Configuration::Instance()->GetValue('application', 'gtfw_base').'main/lib/gtfw/wsdl_generator/WsdlGenerator.class.php';

if (isset($_GET['getlist'])) {
   WsdlGenerator::Instance()->GetList();
} else {
   WsdlGenerator::Instance()->GenerateWsdl(Configuration::Instance()->GetValue('wsdl', $_GET['nspace']->Raw()));
}
?>
