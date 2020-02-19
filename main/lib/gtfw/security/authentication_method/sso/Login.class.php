<?php

/**
* SSO Login Class. Provides SSO-enabled login mechanism for client
* @package SsoClient
* @author Akhmad Fathonih <toni@gamatechno.com>
* @version 1.0
* @copyright 2006&copy;Gamatechno
*/

class Login extends LoginBase {
   var $mrUser;
   var $mSsoSystemId;

   function __construct() {
      SysLog::Instance()->log('Login(sso)::__construct', "login");
      // make a copy of a user instance
      $this->mUser = Security::Instance()->mrUser;

      $this->mSsoSystemId = Configuration::Instance()->GetValue('application', 'system_id'); /* TODO: make this configurable" */
   }

   function DoLogin() {
      SysLog::Instance()->log('Login(sso)::DoLogin', "login");
      $ssoclient = SsoClient::Instance();
      SysLog::Instance()->log('Login(sso)::DoLogin poke me!!', "login");
//       echo "Is sso alive? ";
//       var_dump($ssoclient->isSsoAlive());
//       die();
      $SsoAuth = $ssoclient->authenticateSsoUser($this->mUsername, $this->mPassword, $this->mSsoSystemId);
//       echo "<pre>";
//       var_dump($SsoAuth);
//       echo "</pre>";
//       die();
      SysLog::Instance()->log("DoLogin got SsoAuth: ".print_r($SsoAuth, true), "login");

      if ($SsoAuth['status'] === true) {
         // save SSID
         $ssoclient->saveSsIdToLocal($SsoAuth['ssid'], Configuration::Instance()->GetValue('application', 'sso_group'));

         // request details on SSID
         $SsoAttr = $ssoclient->requestSsIdAttributes($SsoAuth['ssid'], $this->mSsoSystemId);
         SysLog::Instance()->log("DoLogin got SsoAttr: ".print_r($SsoAttr, true), "login");

         $local_username = $SsoAttr['mSsoLocalUsername'];

         $this->mUser->mUserName = $local_username;
         $this->mUser->GetUser();
      }
//       echo "<pre>";
//       var_dump($SsoAttr);
//       echo "</pre>";
//       die($local_username);
      return $SsoAuth['status'];
   }
}
?>