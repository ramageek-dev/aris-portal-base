<?php
// note: user information should be better stored in a class and serialized in session
// but it is like chicken-egg problem, who comes first? user or session?
// another riddle is why should i store user info in a session var when i have
// a force logout feature? when i should read the force logout status if i store user info
// in a session var? in fact, force logout must be read on each request! agree with that?
// i hope :D

class User extends Database
{

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
	var $mUnitId;

	function User($connectionNumber = 0) {
		$type = Configuration::Instance()->GetValue('application', 'db_conn','0','db_type');
		if($type=="mysqli")
			$type="mysqlt";
		
		$this->mSqlFile = Configuration::Instance()->GetValue('application', 'gtfw_base') .
		'main/lib/gtfw/security/authentication_method/default/adodb/'.$type.'/user.sql.php';

		parent::__construct($connectionNumber);
		//$this->SetDebugOn();

		$vartemp = new UserGroup();
		$this->mrUserGroup = &$vartemp;


	}

	function GetUser()
	{
		if(empty($this->mApplicationId))
			$this->mApplicationId = Configuration::Instance()->GetValue('application', 'application_id');
		 
		//userInfo diambil berdasar user dan applikasi yang menggunakan
		#vprintf($this->mSqlQueries['get_user_info'], array($this->mUserName,Configuration::Instance()->GetValue('application', 'application_id')));
		if(Configuration::Instance()->GetValue('app_version', 'version')==3)
			$result = $this->Open($this->mSqlQueries['get_user_info'], array($this->mUserName, $this->mApplicationId));
		else
			$result = $this->Open($this->mSqlQueries['get_user_info_2'], array($this->mApplicationId, $this->mUserName));
		 
		#print_r($this->mApplicationId);
		if (!empty($result)) {
			$this->mUserId = $result[0]['UserId'];
			$this->mRealName = $result[0]['RealName'];
			$this->mPassword = $result[0]['Password'];
			$this->mNoPassword = $result[0]['NoPassword'];
			$this->mActive = $result[0]['Active'];
			$this->mForceLogout = $result[0]['ForceLogout'];
			// $this->mActiveUserGroupId = $result[0]['GROUP_ID'];
			foreach ($result as $row => $val) {
				$this->mrUserGroup->mUserGroup[$val['GroupId']] = $val['GroupName'];
			}
			if(isset($result[0]['unitId']))
				$this->mUnitId = $result[0]['unitId'];
			// $this->mUnitId = null; // karena mUnitId dibutuhkan, untuk menghindari notice maka variabel tetap diset meskipun berisi null
		} else {
			$this->mUnitId = null;
		}


		// User group
		$this->mrUserGroup->mUserId = $this->mUserId;
		$this->mrUserGroup->mApplicationId = $this->mApplicationId;
		 
		$this->mrUserGroup->mUserName=$this->mUserName;
		//no need get from db. Already get from query before. Just set it.
		//$this->mrUserGroup->GetUserGroup();
		
		if(isset($result[0]['defGroupId'])){
			$this->mActiveUserGroupId = $result[0]['defGroupId'];
			$this->mDefaultUserGroupId = $this->mActiveUserGroupId;
		}
		$this->mDefaultUnitId = $this->mUnitId;

// 		$this->mActiveUserGroupId = $this->mrUserGroup->GetActiveUserGroupId();
// 		$this->mDefaultUserGroupId = $this->mrUserGroup->mDefaultUserGroupId;
// 		$this->mDefaultUnitId = $this->mrUserGroup->mDefaultUnitId;

	}

	function UpdateUser($realName, $password, $noPassword, $active, $forceLogout, $userId) {
		$result = $this->ExecuteUpdateQuery($this->mSqlQueries['update_user'], array($realName,
				$password, md5($noPassword), $active, $forceLogout, $userId));
		$this->GetUser();

		return $result;
	}

	function AddUser() {
		$result = $this->ExecuteInsertQuery($this->mSqlQueries['add_user'], array($this->mUserName,
				$this->mRealName, md5($this->mPassword), $this->mNoPassword, $this->mActive, $this->mForceLogout));
		$this->GetUser();

		return $result;
	}

	function DeleteUser() {
		return $this->ExecuteDeleteQuery($this->mSqlQueries['delete_user'], array($this->mUserId));
	}

	function ForceLogout() {
		return $this->ExecuteUpdateQuery($this->mSqlQueries['force_logout'], array($this->mUserId));
	}

	function ResetForceLogout() {
		return $this->ExecuteUpdateQuery($this->mSqlQueries['reset_force_logout'], array($this->mUserId));
	}
}
?>
