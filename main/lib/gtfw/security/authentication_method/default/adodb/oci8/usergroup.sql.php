<?php

   $sql['get_user_group'] =
   " SELECT
            grp.GroupId AS \"GroupId\",
            grp.GroupName AS \"GroupName\",
            gunt.UnitId AS \"UnitId\",
            gunt.UnitName AS \"UnitName\"
     FROM
            gtfw_user usr
     INNER JOIN gtfw_user_group ug ON (ug.UserId = usr.UserId)
     INNER JOIN gtfw_group grp ON (grp.GroupId = ug.GroupId)
     INNER JOIN gtfw_unit gunt ON (gunt.UnitId = grp.UnitId)
     INNER JOIN gtfw_unit un ON (grp.UnitId = un.UnitId)
     WHERE
            usr.UserId = %d AND gunt.ApplicationId = %d";


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

   $sql['get_default_user_group'] =
   " SELECT 
         a.GroupId AS \"GroupId\", 
         c.UnitId AS \"UnitId\"
     FROM
            gtfw_user_def_group a
     INNER JOIN gtfw_group b ON (a.GroupId = b.GroupId)
     INNER JOIN gtfw_unit c ON (a.ApplicationId = c.ApplicationId AND b.UnitId = c.UnitId)
     WHERE
            a.UserId = %d AND a.ApplicationId = %d";
?>
