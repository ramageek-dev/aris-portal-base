<?php

$sql['get_module_id'] = "
	SELECT
   		ModuleId as ModuleId
	FROM
   		gtfw_module
	WHERE
   		Module = '%s' AND SubModule = '%s' AND Action = '%s' AND Type = '%s'
	";

$sql['translate_request'] =
   " SELECT
            Module,
            SubModule,
            Action,
            Type
     FROM
            gtfw_module
     WHERE
            ModuleId = %d";

$sql['get_module_path']='
	SELECT
			ModulePath
	FROM
			gtfw_module
   WHERE
      module="%s"
   AND
      subModule="%s"
   AND
      action="%s"
   AND
      type="%s"
	';

$sql['get_module_file']='
	SELECT
			module_file as "module_file"
	FROM
			table(pkg_gtfw_base_dispacther.get_module_file(:module,:sub_module,:action,:type))
	'
/* end of ugm edit */
?>