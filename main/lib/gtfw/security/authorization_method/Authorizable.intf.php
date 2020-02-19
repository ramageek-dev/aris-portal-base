<?php

/**
* Authorization Interface
* @package Security
* @author Akhmad Fathonih <toni@gamatechno.com>
* @version 1.0
* @copyright 2006&copy;Gamatechno
*/

interface Authorizable {
   function IsAllowedToAccess($module, $subModule, $action, $type);
}

?>