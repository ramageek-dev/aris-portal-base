<?php
class AdodbDatabaseEngine extends DatabaseEngineBase {
	protected $mDebugMessage; // special property for adodb driver
	var $mArrDebugMessage;

	function __construct($dbConfig = NULL) {
		parent::__construct($dbConfig);

		SysLog::Instance()->log("creating AdodbDatabaseEngine", "database");

		if(isset($GLOBALS['ADODB_ASSOC_CASE']))
			define('ADODB_ASSOC_CASE', 2); # use native-case for ADODB_FETCH_ASSOC, for PostgreSQL, etc.

		require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/adodb/adodb.inc.php';

		$GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_ASSOC; // this should be in config, but how?
		SysLog::Instance()->log("AdodbDatabaseEngine::Preparing dbtype:".$this->mDbConfig['db_type'], get_class());
		$this->mrDbConnection = ADONewConnection($this->mDbConfig['db_type']);

		// set debug mode via configuration
		$this->mrDbConnection->debug = isset($this->mDbConfig['db_debug_enabled'])?$this->mDbConfig['db_debug_enabled']:false;

		SysLog::Instance()->log("AdodbDatabaseEngine::Done preparing dbtype:".$this->mDbConfig['db_type'], get_class());
	}

	private function GetParsedSqlHelper($value) {
		if (is_null($value)) {
			return 'NULL';
		} elseif (is_string($value) || is_object($value)) { // note: we have to handle sanitizer instances
			if(is_object($value))
				$value->mrVariable = trim($value->mrVariable);
			else
				$value = trim($value);

			// sorry, no need to add slashes as it's already been added by the sanitizer
			// this also encourages developer to consistently and consciously use the sanitizer
			//return '\'' . addslashes($value) . '\'';
			return '\'' . $value . '\'';
		} else {
			$value = trim($value);

			return "$value";
		}
	}

	protected function GetParsedSqlEx($sql, $params, $varMarker = ':') {
		if (count($params) == 0) {
			if ($this->mrDbConnection->debug) {
				SysLog::Log('SQL Parsed: no param, returning raw sql'.$sql, get_class());
			}
			return $sql;
		}
		// prevent bug from params = array('foo' => ':bar', 'bar'=> 'double replace')
		$unique = mt_rand();
		foreach($params as $key => $value) {
			$sql = preg_replace('/'.$varMarker.$key.'/', '__'.$unique.'___'.$key.'__', $sql);
		}
		if ($this->mrDbConnection->debug)
			SysLog::Log('GetParsedSqlEx step #1: '.$sql, get_class());

		foreach($params as $key=>$value) {
			$sql = preg_replace('/__'.$unique.'___'.$key.'__/', $this->GetParsedSqlHelper($value), $sql);
		}
		if ($this->mrDbConnection->debug)
			SysLog::Log('GetParsedSqlEx step #2: '.$sql, get_class());

		return $sql;
	}

	protected function GetParsedSql($sql, $params) {
		if (count($params) == 0) {
			if ($this->mrDbConnection->debug) {
				SysLog::Log('SQL Parsed: no param, returning raw sql'.$sql, get_class());
			}
			return $sql;
		}

		// processing params
		$params_processed = array();
		foreach ($params as $k => $v) {
			if (is_array($v)) {
				$v_list_string = '';
				foreach ($v as $c => $d) {
					$v_list_string .= $this->GetParsedSqlHelper($d) . ', ';
				}
				$v_list_string = substr($v_list_string, 0, -2);
				$params_processed[$k] = $v_list_string;
			} else {
				$params_processed[$k] = $this->GetParsedSqlHelper($v);
			}
		}

		if ($this->mrDbConnection->debug) {
			SysLog::Log('About to parse: ' . $sql, get_class());
			SysLog::Log('Param: ' . print_r($params, true), get_class());
		}
		$sql = preg_replace('/([^%])(%[bcdufoxX])/', '\1%s', $sql); // only replace single percent (%%) not double percent (%%)
		if ($this->mrDbConnection->debug)
			SysLog::Log('Got prepared sql step #1: ' . $sql, get_class());
		$sql = preg_replace('/\'%s\'/', '%s', $sql);
		if ($this->mrDbConnection->debug)
			SysLog::Log('Got prepared sql step #2: ' . $sql, get_class());

		$sql_parsed = vsprintf($sql, $params_processed);
		if ($this->mrDbConnection->debug) {
			SysLog::Log('Got prepared sql: '.$sql, get_class());
			SysLog::Log('Parsing: $sql_parsed = vsprintf("' . $sql . '", ' . print_r($params_processed, TRUE) .
					');' . "\nto\n" . $sql_parsed, get_class());
		}

		if ($this->mrDbConnection->debug) {
			SysLog::Log('Final: ' . $sql_parsed, get_class());
		}

		return $sql_parsed;
	}

	protected function GetCacheIdentifier($sql, $params) {
		return $this->GetParsedSql($sql, $params);
	}

	public function Connect() {
		$port = ($this->mDbConfig['db_port'] != '') ? ':' . $this->mDbConfig['db_port'] : '';
		ob_start();
		$result = $this->mrDbConnection->Connect($this->mDbConfig['db_host'] . $port,
				$this->mDbConfig['db_user'], $this->mDbConfig['db_pass'], $this->mDbConfig['db_name'], true);
		ob_end_clean();
		return $result;
	}

	public function Disconnect() {
		return $this->mrDbConnection->Close();
	}

	public function StartTrans() {
		if ($this->mrDbConnection->debug)
			ob_start();
		$this->mrDbConnection->StartTrans();
		if ($this->mrDbConnection->debug) {
			//$this->mArrDebugMessage = strip_tags(ob_get_contents());
			SysLog::Instance()->addQueryLog(strip_tags(ob_get_contents()));
			SysLog::Log('DebugMessage: '.$this->mDebugMessage, get_class());
			ob_end_clean();
		}
		return TRUE;
	}

	public function EndTrans($condition) {
		if ($this->mrDbConnection->debug)
			ob_start();
		$ret = $this->mrDbConnection->CompleteTrans($condition);
		if ($this->mrDbConnection->debug) {
			SysLog::Instance()->addQueryLog(strip_tags(ob_get_contents()));
			//$this->mArrDebugMessage[] = strip_tags(ob_get_contents());
			SysLog::Log('DebugMessage: '.$this->mDebugMessage, get_class());
			ob_end_clean();
		}
		return $ret;
	}

	public function Open($sql, $params, $varMarker = NULL) {
		if ($this->mrDbConnection->debug) {
			SysLog::Log(get_class().'::Open '.$sql, get_class());
		}
		$this->mDebugMessage = '';
		if ($this->mrDbConnection->debug)
			ob_start();

		if (!empty($varMarker))
			$sql_parsed = $this->GetParsedSqlEx($sql, $params, $varMarker);
		else{
			$sql_parsed = $this->GetParsedSql($sql, $params);
		}
		//print "<pre>".$sql_parsed."</pre>";
		$rs = $this->mrDbConnection->Execute($sql_parsed);

		if ($this->mrDbConnection->debug) {
			SysLog::Instance()->addQueryLog(strip_tags(ob_get_contents()));
			//$this->mArrDebugMessage[] = strip_tags(ob_get_contents());
			SysLog::Log('DebugMessage: '.$this->mDebugMessage, get_class());
			ob_end_clean();
		}
		
		if ($rs) {
			$result = $rs->GetArray();
			return $result;
		} else {
			return FALSE;
		}
	}

	public function Execute($sql, $params, $varMarker = NULL) {
		$this->mDebugMessage = '';
		if ($this->mrDbConnection->debug)
			ob_start();

		if (!empty($varMarker))
			$sql_parsed = $this->GetParsedSqlEx($sql, $params, $varMarker);
		else
			$sql_parsed = $this->GetParsedSql($sql, $params);

		$rs = $this->mrDbConnection->Execute($sql_parsed);
		if ($this->mrDbConnection->debug) {
			SysLog::Instance()->addQueryLog(strip_tags(ob_get_contents()));
			//$this->mArrDebugMessage[] = strip_tags(ob_get_contents());
			ob_end_clean();
		}
		if ($rs) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function AffectedRows() {
		return $this->mrDbConnection->Affected_Rows();
	}

	public function LastInsertId() {
	   if ($this->mrDbConnection->debug)
	      ob_start();
	      
	   $insertId = $this->mrDbConnection->Insert_ID();
	   if ($this->mrDbConnection->debug) {
			SysLog::Instance()->addQueryLog(strip_tags(ob_get_contents()));
			ob_end_clean();
		}
		return $insertId;
	}
	
	public function Insert_ID(){
	   return $this->LastInsertId();
	}

	public function SetDebugOn() {
		$this->mrDbConnection->debug = TRUE;
	}

	public function SetDebugOff() {
		$this->mrDbConnection->debug = FALSE;
	}
	
	public function IsConnected(){
	   return $this->mrDbConnection->IsConnected();
	}

	public function GetLastError() {
		if ($this->mDebugMessage != '') // debug message is always superior than error message
			return $this->mDebugMessage;
		if ($this->mrDbConnection)
			return $this->mrDbConnection->ErrorNo() . ': ' . $this->mrDbConnection->ErrorMsg();
		return 'An error occured when instantiating ' . $this->mDbConfig['db_driv'] . ' driver.';
	}
}
?>
