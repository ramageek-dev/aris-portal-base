<?php
class Logout extends LogoutBase {

   function Logout() {
   }

   function DoLogout() {
      SysLog::Instance()->log('DoLogout default', 'login');
      return TRUE;
   }
}
?>