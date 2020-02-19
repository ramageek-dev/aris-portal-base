<?php

/**
* SSO Logout class. Provides the mechanisme to logout from SSO network
* @package SsoClient
* @author Akhmad Fathonih <toni@gamatechno.com>
* @version 1.0
* @copyright 2006&copy;Gamatechno
*/

class Logout extends LogoutBase {
   var $mrUser;

   function Logout() {
      // make a copy of a user instance
      $this->mrUser = Security::Instance()->mrUser;
   }

   function DoLogout() {
      SysLog::Instance()->log('DoLogout sso', 'login');

      $ssoclient = SsoClient::Instance();
      $ssid = $ssoclient->getLocalSsId(Configuration::Instance()->GetValue('application', 'sso_group'));

      $result = $ssoclient->invalidateSsId($ssid);

      SysLog::Instance()->log('DoLogout sso invalidate result'. print_r($result, true), 'login');

      $result2 = $ssoclient->removeLocalSsId(Configuration::Instance()->GetValue('application', 'sso_group'));
      SysLog::Instance()->log('DoLogout sso removeLocalSsId result'. print_r($result2, true), 'login');

      return $result;
   }
}
?>