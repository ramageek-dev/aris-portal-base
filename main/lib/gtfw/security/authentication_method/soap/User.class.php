<?php
// note: user information should be better stored in a class and serialized in session
// but it is like chicken-egg problem, who comes first? user or session?
// another riddle is why should i store user info in a session var when i have
// a force logout feature? when i should read the force logout status if i store user info
// in a session var? in fact, force logout must be read on each request! agree with that?
// i hope :D

class User extends Database {
   var $mUserId;
   var $mRealName;
   var $mUserName;
   var $mPassword;
   var $mNoPassword;
   var $mActive;
   var $mForceLogout;

   // application ID where the user comes in
   var $mApplicationId;

   private $mrUserGroup;
   var $mActiveUserGroupId;
   var $mDefaultUserGroupId;
   var $mDefaultUnitId;

   var $mLoggedIn;

   function User($connectionNumber = 0) {
      $this->mSqlFile = Configuration::Instance()->GetValue('application', 'gtfw_base') .
         'main/lib/gtfw/security/authentication_method/soap/user.sql.php';
      parent::__construct($connectionNumber);
      //$this->SetDebugOn();

      $this->mrUserGroup =& new UserGroup();
   }

   function GetUser() {
      $result = $this->Open($this->mSqlQueries['get_user_info'], array($this->mUserName));
      $this->mUserId = $result[0]['UserId'];
      $this->mRealName = $result[0]['RealName'];
      $this->mPassword = $result[0]['Password'];
      $this->mNoPassword = $result[0]['NoPassword'];
      $this->mActive = $result[0]['Active'];
      $this->mForceLogout = $result[0]['ForceLogout'];

      $this->mApplicationId = Configuration::Instance()->GetValue('application', 'application_id');

      // User group
      $this->mrUserGroup->mUserId = $this->mUserId;
      $this->mrUserGroup->mApplicationId = $this->mApplicationId;
      $this->mrUserGroup->GetUserGroup();
      $this->mActiveUserGroupId = $this->mrUserGroup->GetActiveUserGroupId();
      $this->mDefaultUserGroupId = $this->mrUserGroup->mDefaultUserGroupId;
      $this->mDefaultUnitId = $this->mrUserGroup->mDefaultUnitId;
   }

   function UpdateUser($realName, $password, $noPassword, $active, $forceLogout, $userId) {
      $result = $this->Execute($this->mSqlQueries['update_user'], array($realName,
         $password, $noPassword, $active, $forceLogout, $userId));
      $this->GetUser();

      return $result;
   }

   function AddUser() {
      $result = $this->Execute($this->mSqlQueries['add_user'], array($this->mUserName,
         $this->mRealName, $this->mPassword, $this->mNoPassword, $this->mActive, $this->mForceLogout));
      $this->GetUser();

      return $result;
   }

   function DeleteUser() {
      return $this->Execute($this->mSqlQueries['delete_user'], array($this->mUserId));
   }

   function ForceLogout() {
      return $this->Execute($this->mSqlQueries['force_logout'], array($this->mUserId));
   }

   function ResetForceLogout() {
      return $this->Execute($this->mSqlQueries['reset_force_logout'], array($this->mUserId));
   }
}
?>