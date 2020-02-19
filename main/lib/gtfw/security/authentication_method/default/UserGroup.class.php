<?php
class UserGroup extends Database {

	var $mUserId;
	var $mUserName;
	var $mApplicationId;
	var $mUserGroup;
	var $mDefaultUserGroupId;
	var $mDefaultUnitId;


	function __construct($connectionNumber = 0) {
		$type = Configuration::Instance()->GetValue('application', 'db_conn','0','db_type');
		if($type=="mysqli")
			$type="mysqlt";
		$this->mSqlFile = Configuration::Instance()->GetValue('application', 'gtfw_base') .
		'main/lib/gtfw/security/authentication_method/default/adodb/'.$type.'/usergroup.sql.php';
		 
		parent::__construct($connectionNumber);
		//$this->SetDebugOn();
	}

	function GetUserGroup() {
		// fecth user groups
		$this->mUserGroup = array();
		
		if(Configuration::Instance()->GetValue('app_version', 'version')==3)
			$sql=$this->mSqlQueries['get_user_group'];
		else
			$sql=$this->mSqlQueries['get_user_group_2'];
		$result = $this->Open($sql, array($this->mUserName, $this->mApplicationId));
		
		
		if ($result) {
			foreach ($result as $row => $val) {
				$this->mUserGroup[$val['GroupId']] = $val['GroupName'];
			}
		}
		//added by choirul no need. 
		//$this->mActiveUserGroupId = $result[0]['GroupId'];
		// determine default user group & unit id
		
		if(Configuration::Instance()->GetValue('app_version', 'version')==3)
			$sql=$this->mSqlQueries['get_default_user_group'];
		else
			$sql=$this->mSqlQueries['get_default_user_group_2'];
		$result = $this->Open($sql, array($this->mUserName, $this->mApplicationId));
		
		$this->mDefaultUserGroupId = $result[0]['GroupId'];
		$this->mDefaultUnitId = $result[0]['UnitId'];

		SysLog::Log('Got default GUID: '.$this->mDefaultUserGroupId, get_class());

		if (!isset($_SESSION['active_user_group_id'])) {
			SysLog::Log('SetActiveUserGroupId to DefaultGUID: '.$this->mDefaultUserGroupId, get_class());
			$_SESSION['active_user_group_id'] = $this->mDefaultUserGroupId;
			#$_SESSION['active_user_group_id'] = $this->mActiveUserGroupId;
		}else { //added by choirul
			$_SESSION['active_user_group_id'] = $this->mActiveUserGroupId;
		}
	}

	function GetActiveUserGroupId() {
		return $_SESSION['active_user_group_id'];
	}

	function SetActiveUserGroupId($groupId) {
		$_SESSION['active_user_group_id'] = $groupId;
	}

	function AddUserGroup($groupId) {
		$result = $this->ExecuteInsertQuery($this->mSqlQueries['add_user_group'], array($this->mUserId, $this->mApplicationId, $groupId));
		$this->GetUserGroup();

		return $result;
	}

	function DeleteUserGroup($groupId) {
		$result = $this->ExecuteDeleteQuery($this->mSqlQueries['delete_user_group'], array($this->mUserId, $this->mApplicationId, $groupId));
		$this->GetUserGroup();

		return $result;
	}
}
?>
