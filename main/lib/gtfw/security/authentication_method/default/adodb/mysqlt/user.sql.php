<?php

	$sql['get_user_info_2']="
		SELECT
				usr.`UserId`,
				`RealName`,
				`Password`,
				`NoPassword`,
				`Active`,
				`ForceLogout`,
				grp.GroupId,
				grp.GroupName,
				gunt.UnitId,
				gunt.UnitName,
				IFNULL(defgrp.GroupId,grp.GroupId) AS defGroupId
		FROM
				gtfw_user usr
		LEFT JOIN gtfw_user_group ug ON (ug.UserId = usr.UserId)
		LEFT JOIN gtfw_group grp ON (grp.GroupId = ug.GroupId)
		LEFT JOIN gtfw_unit gunt ON (gunt.UnitId = grp.UnitId)
		LEFT JOIN gtfw_user_def_group defgrp ON (grp.GroupId = defgrp.GroupId) AND defgrp.ApplicationId = '%s'
		WHERE
				usr.UserName = '%s' 
	";

   $sql['get_user_info_2_old'] = 
   " SELECT 
            `UserId`,
            `RealName`,
            `Password`,
            `NoPassword`,
            `Active`,
            `ForceLogout`
     FROM 
            `gtfw_user`
     WHERE 
            `UserName` = '%s'";

$sql['get_user_info'] = " SELECT 
            a.`UserId`,
            a.`RealName`,
            a.`Password`,
            a.`NoPassword`,
            a.`Active`,
            a.`ForceLogout`,
            b.`groupId`,
            unitappUnitId AS unitId,
			   c.GroupId,
				c.GroupName,
				gu.UnitId,
				gu.UnitName,
			   udg.GroupId AS defGroupId
     FROM 
            `gtfw_user` a
   LEFT JOIN gtfw_user_group b ON a.UserId =  b.UserId
   LEFT JOIN gtfw_group c ON b.GroupId = c.GroupId
   LEFT JOIN gtfw_unit_application d ON c.UnitappId = d.unitappId
   LEFT JOIN gtfw_user_def_group udg ON a.`UserId` = udg.`UserId` AND d.`unitappApplicationId` = udg.`ApplicationId`
   LEFT JOIN gtfw_unit gu ON gu.UnitId = d.unitappUnitId
     WHERE 
            `UserName` = '%s'
     AND 
            d.unitappApplicationId = '%s'";

$sql['update_user'] = " UPDATE
            gtfw_user
     SET
         `RealName` = '%s',
         `Password` = '%s',
         `NoPassword` = '%s',
         `Active` = '%s',
         `ForceLogout` = '%s'
     WHERE
            UserId = %d";

$sql['add_user'] = " INSERT INTO
                  gtfw_user (`UserName`, `RealName`, `Password`, `NoPassword`, `Active`, `ForceLogout`)
     VALUES 
            ('%s', '%s','%s','%s','%s','%s')";

$sql['delete_user'] = " DELETE FROM
                  gtfw_user
     WHERE
            UserId = %d";

$sql['force_logout'] = " UPDATE
            gtfw_user
     SET
         ForceLogout = 'Yes'
     WHERE
            UserId = %d";

$sql['reset_force_logout'] = " UPDATE
            gtfw_user
     SET
         ForceLogout = 'No'
     WHERE
            UserId = %d";

?>
