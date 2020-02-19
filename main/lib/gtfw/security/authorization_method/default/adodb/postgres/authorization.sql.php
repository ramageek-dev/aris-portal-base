<?php
// ApplicationId yang digunakan adalah applicationId milik module karena ini terkait dengan hak akses module
   $sql['allowed_to_access'] =
   "SELECT
      CASE WHEN md.Access=true then 'All'
	   ELSE 'Exclusive'
      END as \"Access\",
      SUM(usr.UserId) as \"Allowance\"
   FROM
      gtfw_module md
      LEFT JOIN gtfw_group_module gm ON (gm.ModuleId = md.ModuleId)
      LEFT JOIN gtfw_group g ON (g.GroupId = gm.GroupId)
      LEFT JOIN gtfw_user_group ug ON (ug.GroupId = gm.GroupId)
      LEFT JOIN gtfw_user usr ON (usr.UserId = ug.UserId AND usr.UserId = %d)
   WHERE
      md.Module = '%s' AND md.SubModule = '%s' AND md.Action = '%s' AND md.Type = '%s'
      AND md.ApplicationId = %d
   GROUP BY Access";
?>
