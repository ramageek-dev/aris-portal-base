<?php

// helper function
function GtfwDispt() {
	return Dispatcher::Instance();
}

//

class Dispatcher implements ConfigurationHookIntf {

	private static $mrInstance;
	static $objRestClient;
	static $objRpcClient;
	var $mStartKey;
	var $mMultKey;
	var $mAddKey;
	// current requested:
	var $mModule;
	var $mSubModule;
	var $mAction;
	var $mType;
	// this may be a dirty hack
	// holds main response, ie. reponse that directly requested by client
	// it can be html, ajax, soap, etc
	// for intermodule communication purpose
	var $mrMainResponse;
	private $mrDispatcherHelper = NULL;

	private function __construct() {
		if ((!isset($_SESSION['start_key']) || !isset($_SESSION['mult_key']) || !isset($_SESSION['add_key'])) && Session::Instance()->IsStarted()) {
			$_SESSION['start_key'] = mt_rand(1024, mt_getrandmax());
			$_SESSION['mult_key'] = mt_rand(1024, mt_getrandmax());
			$_SESSION['add_key'] = mt_rand(1024, mt_getrandmax());

		} elseif (!Session::Instance()->IsStarted()) { // default key
			$_SESSION['start_key'] = 981;
			$_SESSION['mult_key'] = 12674;
			$_SESSION['add_key'] = 35891;
			
		}

		$this->mStartKey = $_SESSION['start_key'];
		$this->mMultKey = $_SESSION['mult_key'];
		$this->mAddKey = $_SESSION['add_key'];
		
	}

	function getQueryString($queries){
		if(!is_array($queries))
			return false;

		foreach($queries as $key=>$value){
			$param[$key]=$this->Encrypt($value);
		}
		
		if(!empty($param)){
			return urldecode(http_build_query($param));
		}else{
			return "";
		}
	}

	function restClient($service=0){
		require_once Configuration::Instance()->GetValue('application', 'gtfw_base').'main/lib/gtfw/rest/RestClient.class.php';
		if (!isset(self::$objRestClient)){
			self::$objRestClient = new RestClient();
		}
		if(!is_numeric($service)){
			$url=$service;
		}else{
			$restConf = Configuration::Instance()->GetValue('application', 'rest');
		
			if(!empty($restConf)){
				if(isset($restConf[$service]['path'])){
					$url=$restConf[$service]['path'];
				}
			}
		}
		
		if(!empty($url)){
			self::$objRestClient->SetPath($url);
		}
		else
			return die('path service rest server not found @ Configuration');

		return self::$objRestClient;
	}

	function jsonRpcClient($service=0){
		require_once Configuration::Instance()->GetValue('application', 'gtfw_base').'main/lib/gtfw/rpc/JsonRPCClient.class.php';
		if (!isset(self::$objRpcClient)){
			if(is_numeric($service)){
				$rpcConf = Configuration::Instance()->GetValue('application', 'rpc');
				if(!empty($rpcConf)){
					if(isset($rpcConf[$service]['path'])){
						$url=$rpcConf[$service]['path'];
					}
				}
			}else
				$url=$service;
				
			if(!empty($url))
				self::$objRpcClient = new JsonRpcClient($url);
			else
				return die('path service json-rpc server not found @ Configuration');
		}
		return self::$objRpcClient;
	}

	// $htmlEntityEncoded should be TRUE when there is a need to embed url(s) in an XML/XHTML document

	function GetUrlFriendly($moduleName, $pageName, $pageAct = '', $type = '') {
		$url_to_load = Configuration::Instance()->GetValue('application', 'basedir') . $pageAct . '/' . $moduleName . '/' . $pageName . '.' . $type;
		if(class_exists('Security')){
			if (!$config_error && Security::Instance()->IsUsingRequestId())
				$url_to_load .= '&_' . Security::Instance()->GetCurrentRequestId() . '=';
		}
		return $url_to_load;
	}

	function GetUrl($moduleName, $pageName, $pageAct = '', $type = '', $htmlEntityEncoded = false) {
		if (Configuration::Instance()->GetValue('application', 'url_friendly')) {
			return $this->GetUrlFriendly($moduleName, $pageName, $pageAct, $type);
		}

		$config_error = FALSE;
		if (Configuration::Instance()->GetValue('application', 'url_type') == 'Long') {
			$moduleName = $this->Encrypt($moduleName);
			$pageName = $this->Encrypt($pageName);
			$pageAct = $this->Encrypt($pageAct);
			$type = $this->Encrypt($type);
			$url_to_load = Configuration::Instance()->GetValue('application', 'basedir') . 'index.php?mod=' .
					$moduleName . '&sub=' . $pageName .  '&act=' . $pageAct .  '&typ=' . $type;
		} elseif(Configuration::Instance()->GetValue('application', 'url_type') == 'Short') {
			$module_id = $this->TranslateRequestToShort($moduleName, $pageName, $pageAct, $type);

			if ($module_id !== FALSE) {
				$module_id = $this->Encrypt($module_id);
				$url_to_load = Configuration::Instance()->GetValue('application', 'basedir') . 'index.php?mid=' .
						$module_id;
			}
		} elseif (Configuration::Instance()->GetValue('application', 'url_type') == 'Path') {
			$moduleName = $this->Encrypt($moduleName);
			$pageName = $this->Encrypt($pageName);
			$pageAct = $this->Encrypt($pageAct);
			$type = $this->Encrypt($type);
			$url_to_load = Configuration::Instance()->GetValue('application', 'basedir') . 'index.php/module/' .
					$moduleName . '/subModule/' . $pageName .  '/action/' . $pageAct .  '/type/' . $type . '/?';
		} else {
			$config_error = TRUE;
			$url_to_load = 'javascript:alert(\'You have not configured your URL type. Please, configure it first!\');';
		}

		if(class_exists('Security')){
		// adding request id automatically
			if (!$config_error && Security::Instance()->IsUsingRequestId())
				$url_to_load .= '&_' . Security::Instance()->GetCurrentRequestId() . '=';
		}
		if ($htmlEntityEncoded)
			return htmlentities($url_to_load);

		return $url_to_load;
	}
	function GetCurrentUrl() {
		return $this->GetUrl($this->mModule, $this->mSubModule, $this->mAction, $this->mType);
	}

	function WrapGetParams() {
		return $this->GetUrl($this->mModule, $this->mSubModule, $this->mAction, $this->mType);
	}

	// Obsolete: for awkward compatibility, don't use this method. Use GetUrl() instead!
	function GetWsdlUrl($moduleName, $pageName, $pageAct = '', $type = '') {
		return $this->GetUrl($moduleName, $pageName, $pageAct, $type, TRUE);
	}

	function GetModule($moduleName, $pageName, $pageAct = '', $type = '') {
		
		if (!empty($pageAct)) {
			$class_name = ucfirst($pageAct) . ucfirst($pageName);
			$filename =  $class_name . '.' . $type . '.class.php';
		} else {
			$class_name = ucfirst($pageName);
			$filename =  $class_name . '.' . $type . '.class.php';
		}
		$path_to_load = Configuration::Instance()->GetValue('application', 'docroot') . 'module/' . $moduleName .
		'/response/' . $filename;
		
		SysLog::Instance()->log('path_to_load: '.$path_to_load, 'dispatcher' );

		if (file_exists($path_to_load)) {
			return array($path_to_load, $class_name);
		} else {
			return array(FALSE, $class_name);
		}
	}

	// actually, you can only instantiate DispatcherHelper once in a script lifetime
	// so, in this method $connectionNumber is used exactly once
	// subsequence calls to this method will ignore $connectionNumber
	// and $connectionNumber isn't so useful :D
	function TranslateRequestToLong($moduleId, $connectionNumber = 0) {
		if (!$this->mrDispatcherHelper) {
			require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
			'main/lib/gtfw/dispatcher/DispatcherHelper.class.php';
			$this->mrDispatcherHelper = new DispatcherHelper($connectionNumber);
		}

		return $this->mrDispatcherHelper->TranslateRequestToLong($moduleId);
	}

	// actually, you can only instantiate DispatcherHelper once in a script lifetime
	// so, in this method $connectionNumber is used exactly once
	// subsequence calls to this method will ignore $connectionNumber
	// and $connectionNumber isn't so useful :D
	function TranslateRequestToShort($module, $subModule, $action, $type, $connectionNumber = 0) {
		if (!$this->mrDispatcherHelper) {
			require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
			'main/lib/gtfw/dispatcher/DispatcherHelper.class.php';
			$this->mrDispatcherHelper = new DispatcherHelper($connectionNumber);
		}

		return $this->mrDispatcherHelper->TranslateRequestToShort($module, $subModule, $action, $type);
	}

	function ModuleNotFound() {
		echo 'There is no such page or module!';
	}

	function Encrypt($string) {
	   #hack for now. Stil bugs sometimes
	   //return $string;
		if (!Configuration::Instance()->GetValue('application', 'enable_url_obfuscator'))
			return $string;
		$start_key = $this->mStartKey;
		$encrypted = '';
		$string = "$string"; // stringify string
		for ($i = 0; $i < strlen($string); $i++) {
			$encrypted = $encrypted . chr(ord($string{$i}) ^ ($start_key >> 8));
			$start_key = ((ord($encrypted{$i}) + $start_key) * $this->mMultKey) + $this->mAddKey;
		}
		return urlencode(base64_encode($encrypted));
	}

	function Decrypt($string) {
	   #hack for now. Stil bugs sometimes
	   //return $string;
		if (!Configuration::Instance()->GetValue('application', 'enable_url_obfuscator'))
			return $string;
		$start_key = $this->mStartKey;
		$string = base64_decode(urldecode($string));
		$decrypted = '';
		for ($i = 0; $i < strlen($string); $i++) {
			$decrypted = $decrypted . chr(ord($string{$i}) ^ ($start_key >> 8));
			$start_key = ((ord($string{$i}) + $start_key) * $this->mMultKey) + $this->mAddKey;
		}
		return $decrypted;
	}

	function Dispatch() {

		// send header first
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

		if (isset($_SERVER['HTTP_X_GTFWMODULETYPE']))
			$type = $_SERVER['HTTP_X_GTFWMODULETYPE'];
		else
			$type = null;
		//SysLog::Log("HEADER: " .$_SERVER['X-GtfwModuleType'], 'dispatcher');
		SysLog::Log("HEADER: " .$type, 'dispatcher');
		
		$module = '';
		$submodule = '';
		$action = '';
		$type = '';

		if (Configuration::Instance()->GetValue('application', 'url_type') == 'Long') {
			if ((isset($_REQUEST['mod']) && isset($_REQUEST['sub']) && isset($_REQUEST['act']) && isset($_REQUEST['typ'])) || ($_REQUEST['typ']=="rest")) {
				// hack for requests/responses that don't need obfuscation
				if (in_array($_REQUEST['typ'], Configuration::Instance()->GetValue('application', 'url_obfuscator_exception'))) {
					Configuration::Instance()->RegisterHook($this);
				}
				//

				$module = $this->Decrypt($_REQUEST['mod']);
				$submodule = $this->Decrypt($_REQUEST['sub']);
				$action = $this->Decrypt($_REQUEST['act']);
				$type = $this->Decrypt($_REQUEST['typ']);
				SysLog::Log("Long URL \$_REQUEST", 'dispatcher');
			} else
				SysLog::Log("No \$_REQUEST set for Long URL {$_REQUEST['mod']}", 'dispatcher');
		} elseif (Configuration::Instance()->GetValue('application', 'url_type') == 'Short') {
			/**
			* 
			* Modified by Abdul R. Wahid
			* Prevent error if page loaded without $mid parameter such as initial login page which it was rewritten
			**/
			if (isset($_REQUEST['mid']) && ($_REQUEST['mid'] != "")) {
				$module_id = $this->Decrypt($_REQUEST['mid']);
				$request_translated = $this->TranslateRequestToLong($module_id);
				if (is_array($request_translated)) {
					$module = $request_translated[0];
					$submodule = $request_translated[1];
					$action = $request_translated[2];
					$type = $request_translated[3];
				}
			}
		} elseif (Configuration::Instance()->GetValue('application', 'url_type') == 'Path') {

			list( , , $module, , $submodule, , $action, , $type, ) = explode('/', $_SERVER['PATH_INFO']);
			$module = $this->Decrypt($module);
			$submodule = $this->Decrypt($submodule);
			$action = $this->Decrypt($action);
			$type = $this->Decrypt($type);
		}

		SysLog::Log("Translated request: $module/$submodule/$action/$type from ".print_r($_REQUEST, true), 'dispatcher');


		// default
		if ($module == '' && $submodule == '' && $action == '' && $type == '') {
			$module = Configuration::Instance()->GetValue('application', 'default_module');
			$submodule = Configuration::Instance()->GetValue('application', 'default_submodule');
			$action = Configuration::Instance()->GetValue('application', 'default_action');
			$type = Configuration::Instance()->GetValue('application', 'default_type');
		}

		// hack to overide any typ specified before.
		if (isset($_COOKIE['GtfwModuleType'])) {
			$type = $_COOKIE['GtfwModuleType']->Raw();
			// delete the cookie
			setcookie('GtfwModuleType', '', mktime(5, 0, 0, 7, 26, 1997));
		}
		if (isset($_SERVER['HTTP_X_GTFWMODULETYPE']))
			$type = $_SERVER['HTTP_X_GTFWMODULETYPE'];

		SysLog::Log("Final request: $module/$submodule/$action/$type", 'dispatcher');

		$this->mModule = $module;
		$this->mSubModule = $submodule;
		$this->mAction = $action;
		$this->mType = $type;

		if(class_exists('ServiceSecurity')){
			if(ServiceSecurity::Instance()->AllowedToAccess($module, $submodule, $action, $type)){

				list($file_path, $class_name) = $this->GetModule($module, $submodule, $action, $type );
				if (FALSE === $file_path) {
					$dbMsg = SysLog::Instance()->getAllError();
					if(!empty($dbMsg)){
						echo "<pre>";
						for ($i=0;$i<count($dbMsg);$i++){
							echo $dbMsg[$i];
						}
						echo "</pre>";
					}
					die('Service Not Found');
				} else {
					$this->DispacherSend($type,$file_path,$class_name);
				}
			}
		}else{
			
			SysLog::Instance()->log("Security::Instance()->AllowedToAccess($module, $submodule, $action, $type)", 'sanitizer');
			
			if (Security::Instance()->AllowedToAccess($module, $submodule, $action, $type)) {
				
				list($file_path, $class_name) = $this->GetModule($module, $submodule, $action, $type);

				if (FALSE === $file_path) {
					$this->ModuleNotFound();
				} else {
					
					if (!Security::Instance()->IsProtocolCheckPassed($module, $submodule, $action, $type)) {
						
						// redirect to https or http
						$url = Configuration::Instance()->GetValue('application', 'baseaddress');
						if (!isset($_SERVER['HTTPS'])) {
							$url = preg_replace('/^http:/', 'https:', $url);
						}
						$url .= $this->GetUrl($module, $submodule, $action, $type);
						Redirector::RedirectToUrl($url);
					} else {
						
						$this->DispacherSend($type,$file_path,$class_name);
					}
				}
			} else {
				Security::Instance()->RequestDenied();
			}
		}
	}

	function DispacherSend($type,$file_path,$class_name){

		require_once Configuration::Instance()->GetValue('application', 'gtfw_base').'main/lib/gtfw/response/ResponseIntf.intf.php';
		require_once Configuration::Instance()->GetValue('application', 'gtfw_base').'main/lib/gtfw/response/' .
				$type . '/' . ucfirst($type) . 'Response.class.php';
		
		require_once $file_path;
		
		eval('$response = new '.$class_name.'();');

		// this may be a dirty hack
		$this->mrMainResponse =& $response->GetHandler();

		//
		$response->Send();
	}

	function GetConfigurationHooks() {
		return array('application' => array('enable_url_obfuscator'));
	}

	function RunConfigurationHook($configName, $configKey) {
		if ($configName == 'application' && $configKey == 'enable_url_obfuscator') {
			return FALSE;
		}
	}

	static function Instance() {
		if (!isset(self::$mrInstance))
			self::$mrInstance = new Dispatcher();

		return self::$mrInstance;
	}
}
?>
