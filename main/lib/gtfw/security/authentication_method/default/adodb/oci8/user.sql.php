<?php

//modified by choirul
   $sql['get_user_info'] = 
   " SELECT 
            UserId AS \"UserId\",
            RealName AS \"RealName\",
            Password AS \"Password\",
            NoPassword AS \"NoPassword\",
            Active AS \"Active\",
            ForceLogout AS \"ForceLogout\",
            gu.UNITID AS \"unitId\"
     FROM 
            gtfw_user u
     LEFT JOIN GTFW_GROUP g ON g.GROUPID = u.GROUPID
     LEFT JOIN GTFW_UNIT gu ON gu.UNITID = g.UNITID
     WHERE 
            UserName = '%s'";

   $sql['update_user'] = 
   " UPDATE
            gtfw_user
     SET
         RealName = '%s',
         Password = '%s',
         NoPassword = '%s',
         Active = '%s',
         ForceLogout = '%s'
     WHERE
            UserId = %d";

   $sql['add_user'] = 
   " INSERT INTO
                  gtfw_user (UserName, RealName, Password, NoPassword, Active, ForceLogout)
     VALUES 
            ('%s', '%s','%s','%s','%s','%s')";

   $sql['delete_user'] = 
   " DELETE FROM
                  gtfw_user
     WHERE
            UserId = %d";

   $sql['force_logout'] = 
   " UPDATE
            gtfw_user
     SET
         ForceLogout = 'Yes'
     WHERE
            UserId = %d";

   $sql['reset_force_logout'] = 
   " UPDATE
            gtfw_user
     SET
         ForceLogout = 'No'
     WHERE
            UserId = %d";
?>