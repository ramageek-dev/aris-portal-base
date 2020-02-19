<?php
require_once GTFW_BASE_DIR . '/main/lib/gtfw/configuration/Configuration.class.php';
require_once GTFW_BASE_DIR . '/main/lib/gtfw/configuration/ConfigurationHookIntf.intf.php';

Configuration::Instance()->SetConfigDirectory(GTFW_APP_DIR . '/config/');
Configuration::Instance()->Load('application.conf.php', 'default');
Configuration::Instance()->Load('organization.conf.ini', 'ini');

// if (Configuration::Instance()->Load('static.conf.php', 'shm') === false) {
//    Configuration::Instance()->Load('static.conf.php', 'default');
//    SharedMemory::Instance()->Set('static.conf.php', Configuration::$mValues['static']);
// }

// warning: these lines below soon will be obsolete!
require_once GTFW_BASE_DIR . '/main/lib/gtfw/configuration/GTFWConfiguration.class.php';

GTFWConfiguration::SetConfigDirectory(GTFW_APP_DIR . '/config/');
GTFWConfiguration::Load('application.conf.php', 'default');
GTFWConfiguration::Load('organization.conf.ini', 'ini');

// if (GTFWConfiguration::Load('static.conf.php', 'shm') === false) {
//    GTFWConfiguration::Load('static.conf.php', 'default');
//    SharedMemory::Instance()->Set('static.conf.php', GTFWConfiguration::$mValues['static']);
// }
?>
