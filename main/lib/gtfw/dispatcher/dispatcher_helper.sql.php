<?php

$sql['get_module_id'] = "
SELECT
   ModuleId
FROM
   `gtfw_module`
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
            `gtfw_module`
     WHERE
            ModuleId = %d";
?>