<?php

   $sql['get_user_info'] = 
   " SELECT 
            a.UserId as \"UserId\",
            a.RealName as \"RealName\",
            a.Password as \"Password\",
			CASE WHEN a.NoPassword=true then 'Yes'
			ELSE 'No'
			END as \"NoPassword\",
			CASE WHEN a.Active=true then 'Yes'
			ELSE 'No'
			END as \"Active\",
			CASE WHEN a.ForceLogout=true then 'Yes'
			ELSE 'No'
			END as \"ForceLogout\"
     FROM 
            gtfw_user a
   LEFT JOIN gtfw_user_group b ON a.UserId =  b.UserId
   LEFT JOIN gtfw_group c ON b.GroupId = c.GroupId
   LEFT JOIN gtfw_unit_application d ON c.UnitappId = d.unitappId
     WHERE 
            UserName = '%s'
     AND 
   d.unitappApplicationId = '%s'";

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