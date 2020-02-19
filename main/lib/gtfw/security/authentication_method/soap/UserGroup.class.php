<?php
class UserGroup extends Database {
   var $mUserId;
   var $mApplicationId;
   var $mUserGroup;
   var $mDefaultUserGroupId;
   var $mDefaultUnitId;


   function UserGroup($connectionNumber = 0) {
      $this->mSqlFile = Configuration::Instance()->GetValue('application', 'gtfw_base') .
         'main/lib/gtfw/security/authentication_method/soap/usergroup.sql.php';
      parent::__construct($connectionNumber);
   }

   function GetUserGroup() {
      // fecth user groups
      $this->mUserGroup = array();
      $result = $this->Open($this->mSqlQueries['get_user_group'], array($this->mUserId, $this->mApplicationId));
      if ($result) {
         foreach ($result as $row => $val) {
            $this->mUserGroup[$val['GroupId']] = $val['GroupName'];
         }
      }
      // determine default user group & unit id
      $result = $this->Open($this->mSqlQueries['get_default_user_group'], array($this->mUserId, $this->mApplicationId));
      $this->mDefaultUserGroupId = $result[0]['GroupId'];
      $this->mDefaultUnitId = $result[0]['UnitId'];

      SysLog::Log('Got default GUID: '.$this->mDefaultUserGroupId, get_class());

      if (!isset($_SESSION['active_user_group_id'])) {
         SysLog::Log('SetActiveUserGroupId to DefaultGUID: '.$this->mDefaultUserGroupId, get_class());
         $_SESSION['active_user_group_id'] = $this->mDefaultUserGroupId;
      }
   }

   function GetActiveUserGroupId() {
      return $_SESSION['active_user_group_id'];
   }

   function SetActiveUserGroupId($groupId) {
      $_SESSION['active_user_group_id'] = $groupId;
   }

   function AddUserGroup($groupId) {
      $result = $this->Execute($this->mSqlQueries['add_user_group'], array($this->mUserId, $this->mApplicationId, $groupId));
      $this->GetUserGroup();

      return $result;
   }

   function DeleteUserGroup($groupId) {
      $result = $this->Execute($this->mSqlQueries['delete_user_group'], array($this->mUserId, $this->mApplicationId, $groupId));
      $this->GetUserGroup();

      return $result;
   }
}
?>