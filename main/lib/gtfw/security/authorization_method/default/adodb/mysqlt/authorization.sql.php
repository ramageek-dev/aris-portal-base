<?php

	$sql['check_count_group']="
		SELECT 
		   COUNT(*) jumlah 
		FROM
		   gtfw_group_module 
		JOIN gtfw_user_def_group USING (GroupId)
		JOIN gtfw_user USING (UserId)
		WHERE UserName = '%s'
	";
	
   $sql['allowed_to_access_2'] =
   " SELECT
   	md.ModuleId,
      md.Access as Access,
      SUM(usr.UserId) as Allowance
   FROM
      gtfw_module md
      LEFT JOIN gtfw_group_module gm ON (gm.ModuleId = md.ModuleId)
      LEFT JOIN gtfw_group g ON (g.GroupId = gm.GroupId)
      LEFT JOIN gtfw_unit u ON (g.UnitId = u.UnitId AND u.ApplicationId = %d)
      LEFT JOIN gtfw_user_group ug ON (ug.GroupId = gm.GroupId)
      LEFT JOIN gtfw_user usr ON (usr.UserId = ug.UserId AND usr.UserName = %d)
   WHERE
      md.Module = '%s' AND md.SubModule = '%s' AND md.Action = '%s' AND md.Type = '%s'
   GROUP BY Access";
// ApplicationId yang digunakan adalah applicationId milik module karena ini terkait dengan hak akses module
   $sql['allowed_to_access'] =
   "SELECT
   	md.ModuleId,
      md.Access as Access,
      SUM(usr.UserId) as Allowance
   FROM
      gtfw_module md
      LEFT JOIN gtfw_group_module gm ON (gm.ModuleId = md.ModuleId)
      LEFT JOIN gtfw_group g ON (g.GroupId = gm.GroupId)
      LEFT JOIN gtfw_user_group ug ON (ug.GroupId = gm.GroupId)
      LEFT JOIN gtfw_user usr ON (usr.UserId = ug.UserId AND usr.UserName = %d)
   WHERE
      md.Module = '%s' AND md.SubModule = '%s' AND md.Action = '%s' AND md.Type = '%s'
      AND md.ApplicationId = %d
   GROUP BY Access";
   
   $sql['module_denied'] = "
   	SELECT
   	   CONCAT('mod=',gmod.Module,
		   '&sub=',gmod.SubModule,
		   '&act=',gmod.`Action`,
		   '&typ=',gmod.`Type`) AS url,
		   CONCAT(gmod.Module,
		   '|',gmod.SubModule,
		   '|',gmod.`Action`,
		   '|',gmod.`Type`) AS delUrl,
	   	labelAksi,
   		GroupId
	FROM `gtfw_module` gmod
	LEFT JOIN gtfw_group_module ggmod ON (gmod.ModuleId = ggmod.`ModuleId`) AND GroupId = '%s'
	WHERE 
		Module = '%s'
	AND GroupId IS NULL
	AND Access = 'Exclusive'
   ";
   
   $sql['describe_gtfw_module']="
   	DESCRIBE gtfw_module;
   ";
   
   $sql['module_denied_2'] = "
   SELECT
	   CONCAT('mod=',gmod.Module,
	   '&sub=',gmod.SubModule,
	   '&act=',gmod.`Action`,
	   '&typ=',gmod.`Type`) AS url,
	   CONCAT(gmod.Module,
	   '|',gmod.SubModule,
	   '|',gmod.`Action`,
	   '|',gmod.`Type`) AS delUrl,
	   GroupId
   FROM `gtfw_module` gmod
   LEFT JOIN gtfw_group_module ggmod ON (gmod.ModuleId = ggmod.`ModuleId`) AND GroupId = '%s'
   WHERE
   	Module = '%s'
   AND GroupId IS NULL
   AND Access = 'Exclusive'
   ";
?>
