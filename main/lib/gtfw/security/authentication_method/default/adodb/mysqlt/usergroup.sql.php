<?php

   $sql['get_user_group_2'] =
   " SELECT
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
            usr.UserName = %d AND gunt.ApplicationId = %d";
            
$sql['get_user_group'] = " SELECT
            grp.GroupId,
            grp.GroupName,
            un.UnitId,
            un.UnitName
     FROM
            gtfw_user usr
     INNER JOIN gtfw_user_group ug ON (ug.UserId = usr.UserId)
     INNER JOIN gtfw_group grp ON (grp.GroupId = ug.GroupId)
     INNER JOIN gtfw_unit_application gunt ON (gunt.unitappUnitId = grp.UnitappId)
     INNER JOIN gtfw_unit un ON (grp.UnitappId = un.UnitId)
     WHERE
            usr.UserName = %d AND gunt.unitappApplicationId = %d";


$sql['add_user_group'] = " INSERT INTO
                  gtfw_user_group (UserId, GroupId)
     VALUES
            (%d, %d)";

$sql['delete_user_group'] = " DELETE FROM
                  gtfw_user_group
     WHERE
            UserId = %d AND ApplicationId = %d AND GroupId = %d ";

   $sql['add_user_group'] =
   " INSERT INTO
                  gtfw_user_group (UserId, GroupId)
     VALUES
            (%d, %d)";
            

   $sql['delete_user_group'] =
   " DELETE FROM
                  gtfw_user_group
     WHERE
            UserId = %d AND ApplicationId = %d AND GroupId = %d ";

   $sql['get_default_user_group_2'] =
   " SELECT a.GroupId, c.UnitId
     FROM
            gtfw_user_def_group a
     INNER JOIN gtfw_group b ON (a.GroupId = b.GroupId)
     INNER JOIN gtfw_unit c ON (a.ApplicationId = c.ApplicationId AND b.UnitId = c.UnitId)
     JOIN gtfw_user d ON d.userId = a.userId
     WHERE
            d.UserName = %d AND a.ApplicationId = %d";

$sql['get_default_user_group'] = " SELECT a.GroupId, c.unitappUnitId AS UnitId
     FROM
            gtfw_user_def_group a
     INNER JOIN gtfw_group b ON (a.GroupId = b.GroupId)
     JOIN gtfw_unit_application e ON b.UnitappId = e.unitappId
     INNER JOIN gtfw_unit_application c ON (a.ApplicationId = c.unitappApplicationId AND e.unitappUnitId = c.unitappUnitId)
     JOIN gtfw_user d ON d.userId = a.userId
     WHERE
            d.UserName = %d AND a.ApplicationId = %d";

?>
