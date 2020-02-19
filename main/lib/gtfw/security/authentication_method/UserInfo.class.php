<?php

class UserInfo implements IUserInfo {
   public $mUserId, $mUserName, $mRealName, $mPassword, $mNoPassword, $mActive, $mForceLogout, $mApplicationId, $mActiveUserGroupId, $mDefaultUserGroupId, $mDefaultUnitId;


   function GetUserId() {
      return $this->mUserId;
   }

   function GetUserName() {
      return $this->mUserName;
   }

   function GetRealName() {
      return $this->mRealName;
   }

   function GetPassword() {
      return $this->mPassword;
   }

   function GetNoPassword() {
      return $this->mNoPassword;
   }

   function GetActive() {
      return $this->mActive;
   }

   function GetForceLogout() {
      return $this->mForceLogout;
   }

   function GetApplicationId() {
      return $this->mApplicationId;
   }


   function GetUnitId() {
      return $this->mUnitId;
   }

   function GetActiveUserGroupId() {
      return $this->mActiveUserGroupId;
   }

   function GetDefaultUserGroupId() { // bedanya sama active usergroupid apa? :p beda dong, default user group kan buat fallback, sementara active user group adalah user group yan sedang aktif :p
      return $this->mDefaultUserGroupId;
   }

   function GetDefaultUnitId() {
      return $this->mDefaultUnitId;
   }
}

?>