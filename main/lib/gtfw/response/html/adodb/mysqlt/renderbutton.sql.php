<?php
$sql['get_group_akses_module'] = "
SELECT DISTINCT
	gm1.AksiId
FROM
	gtfw_group_module gmod
LEFT JOIN gtfw_group g ON gmod.groupId = g.groupId
LEFT JOIN gtfw_module gm1 ON gm1.moduleId = gmod.moduleId
LEFT JOIN gtfw_module gm2 ON gm2.MenuId = gm1.MenuId
LEFT JOIN gtfw_aksi ga ON ga.aksiid = gm1.aksiid
LEFT JOIN gtfw_user_group gug ON g.GroupId = gug.GroupId
LEFT JOIN gtfw_user gu ON gu.UserId = gug.UserId
WHERE
	gu.UserName = '%s'
AND gm2.moduleId = '%s'
AND gm1.AksiId IS NOT NULL
ORDER BY
	gm1.AksiId
";

$sql['get_kode_aksi'] = "
SELECT
    AksiKode
FROM 
    gtfw_aksi 
WHERE AksiId IN ('%s')
";

$sql['get_module_id'] = "
SELECT
    ModuleId as \"modId\"
FROM gtfw_module
WHERE Module = '%s' AND SubModule = '%s' AND Action = '%s' AND Type = '%s' AND ApplicationId = '%s'
";

?>
