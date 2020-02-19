<?php

class Authorization extends Database implements Authorizable {
	public $mUserId, $mApplicationId, $mUserName;

	private $mModuleAccess, $mUserAccess, $labelAksi="notset", $moduleDelete=array(), $moduleCount;

	function __construct($connectionNumber = 0) {

		$type = Configuration::Instance()->GetValue('application', 'db_conn','0','db_type');
		if($type=="mysqli")
			$type="mysqlt";
		
		$this->mSqlFile = Configuration::Instance()->GetValue('application', 'gtfw_base') .
		'main/lib/gtfw/security/authorization_method/default/adodb/'.$type.'/authorization.sql.php';
		parent::__construct($connectionNumber);

		if(isset($_SESSION['access_module'])){
			$this->mModuleAccess = json_decode($_SESSION['access_module']['module'],true);
			$this->mUserAccess = json_decode($_SESSION['access_module']['user']);
			if(isset($_SESSION['access_module']['moduleCount']))
				$this->moduleCount = json_decode($_SESSION['access_module']['moduleCount'],true);
			else
				$this->moduleCount=0;
		}

		$this->recheckCountModulAccessSessionDb();

	}

	function recheckCountModulAccessSessionDb(){
		#fixme for now just check count of gtfw_group next must be smarter
		$result =  $this->open($this->mSqlQueries['check_count_group'],array($this->mUserAccess));
		if($result[0]['jumlah']!=$this->moduleCount){
			unset($_SESSION['access_module']);
			$_SESSION['access_module']['moduleCount']=json_encode($result[0]['jumlah']);
		}
	}


	function SetUserId($UserId) {
		$this->mUserId = $UserId;
	}

	function SetUserName($UserName) {
		$this->mUserName = $UserName;
	}

	function SetApplicationId($AppId) {
		$this->mApplicationId = $AppId;
	}

	function checkModuleAccess($module){
		if($this->mUserAccess!=$this->mUserName){
			unset($_SESSION['access_module']);
			return false;
		}


		if(isset($this->mModuleAccess[$module])){
			$result[0]['Allowance']=1;
			$result[0]['Access']="";
			return $result;
		}else
			return false;
	}

	function setModuleAccess($module, $moduleId){
		if($moduleId==null)
			$moduleId='0';
		$this->mModuleAccess[$module]=$moduleId;
		$_SESSION['access_module']['module']=json_encode($this->mModuleAccess);
		$_SESSION['access_module']['user'] = json_encode($this->mUserName);
	}

	function IsAllowedToAccess($module, $subModule, $action, $type) {
		//unset($_SESSION['access_module']);
		$result=$this->checkModuleAccess($module.$subModule.$action.$type.$this->mApplicationId);

		if(empty($result)){
			if(Configuration::Instance()->GetValue('app_version', 'version')==3)
				$result = $this->Open($this->mSqlQueries['allowed_to_access'], array($this->mUserName, $module, $subModule, $action, $type,$this->mApplicationId));
			else
				$result = $this->Open($this->mSqlQueries['allowed_to_access_2'], array($this->mApplicationId, $this->mUserName, $module, $subModule, $action, $type));
		}
		if (!$result)
			return FALSE;

		$allowance = $result[0]['Allowance'];
		$access = $result[0]['Access'];
		if ($allowance > 0 || $access == 'All') {
			$this->setModuleAccess($module.$subModule.$action.$type.$this->mApplicationId,$access = empty($result[0]['ModuleId'])?null:$result[0]['ModuleId']);
			//unset($_SESSION['access_module']);
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function ModuleDenied($module) {
		#gtfwDbOpen
		$groupId = Security::Instance()->mAuthentication->GetCurrentUser()->GetDefaultUserGroupId();

		#gtfwDbOpen
		if($this->labelAksi=="notset"){
		if(!isset($_SESSION['describe_gtfw_module'])){
			$result = $this->open($this->mSqlQueries['describe_gtfw_module'],array());
			$_SESSION['describe_gtfw_module']=json_encode($result);
		}else
			$result = json_decode($_SESSION['describe_gtfw_module'],true);
		 
		for($i=0;$i<count($result);$i++)
			if(strtoupper($result[$i]['Field'])=='LABELAKSI') {
				$this->labelAksi = true;
				break;
			} else
				$this->labelAksi = false;
	}

	if(isset($module->mrVariable)){
		if(empty($this->moduleDelete)){
			
			if(!isset($this->moduleDelete[$module->mrVariable])){
				if($this->labelAksi===true)
					$result = $this->open($this->mSqlQueries['module_denied'],array($groupId,$module));
				else
					$result = $this->open($this->mSqlQueries['module_denied_2'],array($groupId,$module));
	
				$this->moduleDelete[$module->mrVariable]=$result;
			}else
				$result = $this->moduleDelete[$module->mrVariable];
			 
			return $result;
		}else
			return $this->moduleDelete[$module->mrVariable];
		}else
			return false;
	} 

}

?>
