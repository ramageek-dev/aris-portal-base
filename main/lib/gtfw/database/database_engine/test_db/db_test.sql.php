<?php
// sample of db test record set

$sql[" SELECT
            `UserId`,
            `RealName`,
            `Password`,
            `NoPassword`,
            `Active`,
            `ForceLogout`
     FROM
            `gtfw_user`
     WHERE
            `UserName` = 'none@kg'"] = array(array('UserId' => 1, 'RealName' => 'Nobody', 'Password' => '', 'NoPassword' => 'Yes', 'Active' => 'Yes', 'ForceLogout' => 'No'));

$sql[" SELECT
            grp.GroupId,
            grp.GroupName,
            gunt.UnitId,
            gunt.UnitName
     FROM
            gtfw_user usr
     INNER JOIN gtfw_user_group ug ON (ug.UserId = usr.UserId)
     INNER JOIN gtfw_group grp ON (grp.GroupId = ug.GroupId)
     INNER JOIN gtfw_unit gunt ON (gunt.UnitId = grp.UnitId)
     INNER JOIN gtfw_unit un ON (grp.UnitId = un.UnitId)
     WHERE
            usr.UserId = 0 AND gunt.ApplicationId = 0"] = array();

$sql[" SELECT a.GroupId, c.UnitId
     FROM
            gtfw_user_def_group a
     INNER JOIN gtfw_group b ON (a.GroupId = b.GroupId)
     INNER JOIN gtfw_unit c ON (a.ApplicationId = c.ApplicationId AND b.UnitId = c.UnitId)
     WHERE
            a.UserId = 0 AND a.ApplicationId = 0"] = array();

$sql[" SELECT DISTINCT
                     gm.MenuName,
                     gm.MenuId,
                     gmod.Module,
                     gmod.SubModule,
                     gmod.Type,
                     gmod.Action,
                     gmod.Description,
                     gm.ParentMenuId
     FROM gtfw_group_menu gm
     LEFT JOIN gtfw_module gmod on (gm.ModuleId = gmod.ModuleId)
     LEFT JOIN gtfw_group grp on (gm.GroupId = grp.GroupId)
     LEFT JOIN gtfw_unit gunt on (grp.UnitId = gunt.UnitId)
     WHERE gm.groupId = 1 AND gunt.ApplicationId = 1 AND gmod.ApplicationId = 1"] = array(array(
         'MenuName' => 'Agama',
         'MenuId' => 10,
         'Module' => 'ref_agama',
         'SubModule' => 'Agama',
         'Type' => 'html',
         'Action' => 'view',
         'Description' => '',
         'ParentMenuId' => NULL)
     );

$sql[" SELECT
            `UserId`,
            `RealName`,
            `Password`,
            `NoPassword`,
            `Active`,
            `ForceLogout`
     FROM
            `gtfw_user`
     WHERE
            `UserName` = 'none@kg'"] = array(array('UserId' => 1, 'RealName' => 'Nobody', 'Password' => '', 'NoPassword' => 'Yes', 'Active' => 'Yes', 'ForceLogout' => 'No'));

$sql[" SELECT
            grp.GroupId,
            grp.GroupName,
            gunt.UnitId,
            gunt.UnitName
     FROM
            gtfw_user usr
     INNER JOIN gtfw_user_group ug ON (ug.UserId = usr.UserId)
     INNER JOIN gtfw_group grp ON (grp.GroupId = ug.GroupId)
     INNER JOIN gtfw_unit gunt ON (gunt.UnitId = grp.UnitId)
     INNER JOIN gtfw_unit un ON (grp.UnitId = un.UnitId)
     WHERE
            usr.UserId = 0 AND gunt.ApplicationId = 0"] = array();

$sql[" SELECT a.GroupId, c.UnitId
     FROM
            gtfw_user_def_group a
     INNER JOIN gtfw_group b ON (a.GroupId = b.GroupId)
     INNER JOIN gtfw_unit c ON (a.ApplicationId = c.ApplicationId AND b.UnitId = c.UnitId)
     WHERE
            a.UserId = 0 AND a.ApplicationId = 0"] = array();

$sql[" SELECT DISTINCT
                     gm.MenuName,
                     gm.MenuId,
                     gmod.Module,
                     gmod.SubModule,
                     gmod.Type,
                     gmod.Action,
                     gmod.Description
     FROM gtfw_user_fav_menu gmf
     LEFT JOIN gtfw_group_menu gm on (gm.MenuId = gmf.MenuId)
     LEFT JOIN gtfw_module gmod on (gm.ModuleId = gmod.ModuleId)
     WHERE gmf.UserId = 1 AND gmf.GroupId = 1"] = array(array(
         'MenuName' => 'Soap Debugger',
         'MenuId' => 1,
         'Module' => 'soapdbg',
         'SubModule' => 'SoapDbg',
         'Type' => 'html',
         'Action' => 'view',
         'Description' => '')
     );
?>