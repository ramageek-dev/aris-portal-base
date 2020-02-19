<?php

$sql['get_module_id_gt'] = "
	SELECT
   		ModuleId as \"ModuleId\"
	FROM
   		gtfw_module
	WHERE
   		Module = '%s' AND SubModule = '%s' AND Action = '%s' AND Type = '%s'
	";

$sql['translate_request_gt'] = 
   " SELECT
            Module as \"Module\", 
            SubModule as \"SubModule\", 
            Action as \"Action\", 
            Type as \"Type\"
     FROM
            gtfw_module
     WHERE
            ModuleId = %d";

/* ugm edit */
$sql['get_module_id'] = "
	SELECT
   			Module_Id as \"ModuleId\"
	FROM
   			table(pkg_gtfw_base_dispacther.get_module_id('%s','%s','%s','%s'))   	
	";

$sql['translate_request'] = 
   " SELECT
            Module as \"Module\", 
            Sub_Module as \"SubModule\", 
            Action as \"Action\", 
            Type as \"Type\"
     FROM
            table(pkg_gtfw_base_dispacther.translate_request(%d))";

$sql['get_module_path']='
	SELECT 
			module_path as "module_path"
	FROM 
			table(pkg_gtfw_base_dispacther.get_module_path(:module,:sub_module,:action,:type))
	';

$sql['get_module_file']='
	SELECT 
			module_file as "module_file"
	FROM 
			table(pkg_gtfw_base_dispacther.get_module_file(:module,:sub_module,:action,:type))
	'
/* end of ugm edit */

?>