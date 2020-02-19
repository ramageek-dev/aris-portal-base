<?php

/**
* Authentication Interface
* @package Security
* @author Akhmad Fathonih <toni@gamatechno.com>
* @version 1.0
* @copyright 2006&copy;Gamatechno
*/

interface Authenticable {
   function DoLogin();
   function DoLogout($destroySession = false);
   function GetCurrentUser(); /// must return IUserInfo
   function SetPassword($value, $isHashed = false);
   function IsLoggedIn();
   function CheckForceLogout();
   function ForceLogout();
   function GetLoginPage();
}

?>