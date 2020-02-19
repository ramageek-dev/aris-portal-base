<?php
   $sql['get_user_info'] = "gtfwComplex_GetUserInfo(UserName)";

   $sql['update_user'] = "gtfwDefault_UpdateUserInfo(RealName, Password, NoPassword, Active, ForceLogout, UserId)";

   $sql['add_user'] = "gtfwDefault_AddUser(UserName, RealName, Password, NoPassword, Active, ForceLogout)";

   $sql['delete_user'] = "gtfwDefault_DeleteUser(UserId)";

   $sql['force_logout'] = "gtfwDefault_SetForceLogout(UserId)";

   $sql['reset_force_logout'] = "gtfwDefault_ResetForceLogout(UserId)";
?>