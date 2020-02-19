<?php

/**
 * Authentication base
 * @author Akhmad Fathonih (toni@gamatechno.com)
 */
class AuthenticationBase implements Authenticable {
   protected $mUser;

   // used with Security::Login
   protected $mPassword;
   protected $mUserName;
   protected $mPasswordIsHashed;

   // used with Security::LoginEx -- spoiler
   protected $mAuthenticationData;
   ////

   function __construct() {
      $this->mUser = null;
   }

   /**
    * Should return current user isntance(?). TODO: not so sure wether it must be the same for each login method
    */
   function GetCurrentUser() {
      return $this->mUser;
   }

   function SetUserName($value) {
      $this->mUserName = $value;
   }

   function SetPassword($value, $isHashed = false) {
      $this->mPassword = $value;
      $this->mPasswordIsHashed = $isHashed;
   }

   function IsPasswordHashed() {
      return $this->mPasswordIsHashed;
   }

   function IsLoggedIn() {
      return $this->mIsLoggedIn;
   }

   function GetSalt() {
   	
      if (!isset($_SESSION['salt']))
         $_SESSION['salt'] = mt_rand();
      
      return $_SESSION['salt'];
   }

   /**
   * Must assign login status to $this->mIsLoggedIn
   * @return boolean login status
   */
   function DoLogin() {
      $this->mIsLoggedIn = true;
      return $this->mIsLoggedIn;
   }

   /**
   * Must assign login status to $this->mIsLoggedIn, return login status
   * @return boolean login status
   */
   function DoLogout($destroySession = false) {
      $this->mIsLoggedIn = false;
      return true;
   }

   function CheckForceLogout() {
      if ($this->GetCurrentUser()->GetForceLogout())
         $this->DoLogout(true);
   }

   function ForceLogout() {
      return true;
   }

   // used with Security::LoginEx -- spoiler
   function SetAuthenticationData($authData) {
      $this->mAuthenticationData = $authData;
   }
   //////

   function GetLoginPage() {
      $login_page = Configuration::Instance()->GetValue('application', 'login_page');
      $login_page = empty($login_page)?array('mod' => 'login_default', 'sub' => 'login', 'act' => 'view', 'typ' => 'html'):$login_page;
      return Dispatcher::Instance()->GetUrl($login_page['mod'], $login_page['sub'], $login_page['act'], $login_page['typ']) .
         '&login_first=1&back_to=' . urlencode(Configuration::Instance()->GetValue('application', 'baseaddress') .
         $_SERVER['REQUEST_URI']);
   }
}
?>