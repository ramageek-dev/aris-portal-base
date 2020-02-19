<?php

$sql['get_need_ssl'] = "
SELECT
   NeedSsl
FROM
   `gtfw_module`
WHERE
   Module = '%s' AND SubModule = '%s' AND Action = '%s' AND Type = '%s'
";
?>