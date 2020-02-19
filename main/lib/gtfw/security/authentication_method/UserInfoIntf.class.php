<?php

/**
* UserInfo Interface to facilitate user info fetching amongs various login method
* @package Security
* @author Akhmad Fathonih (toni@gamatechno.com)
*/

interface IUserInfo {
   function GetUserId();
   function GetRealName();
   function GetPassword();
   function GetNoPassword();
   function GetActive();
   function GetForceLogout();
   function GetApplicationId();
   function GetDefaultUnitId();
   function GetActiveUserGroupId();

//    function GetUser(); /// retrieve user info
//    function ResetForceLogout();
   ///TODO: more user info here
}

?>