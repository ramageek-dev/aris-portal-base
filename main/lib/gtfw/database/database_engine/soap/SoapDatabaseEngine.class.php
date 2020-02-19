<?php
class SoapDatabaseEngine extends DatabaseEngineBase {
   // error message
   protected $mErrorMessage = NULL;

   function __construct($dbConfig = NULL) {
      parent::__construct($dbConfig);

      // set debug mode via configuration
      $GLOBALS['debug'] = (bool) $this->mDbConfig['db_debug_enabled'];

      // preparing wsdl cache configuration
      if (!isset($this->mDbConfig['db_wsdl_cache_enabled']))
         $this->mDbConfig['db_wsdl_cache_enabled'] = TRUE;
      $this->mDbConfig['db_wsdl_cache_lifetime'] = $this->mDbConfig['db_wsdl_cache_lifetime'] != '' ? (int) $this->mDbConfig['db_wsdl_cache_lifetime'] : 60 * 60 * 24; // defaults to 1 day
      $this->mDbConfig['db_wsdl_cache_path'] = file_exists($this->mDbConfig['db_wsdl_cache_path']) ? $this->mDbConfig['db_wsdl_cache_path'] : Configuration::Instance()->GetTempDir();
      
      $this->mDbConfig['db_connection_timeout'] = $this->mDbConfig['db_connection_timeout'] != '' ? (int) $this->mDbConfig['db_connection_timeout'] : 30; // default to 30
      $this->mDbConfig['db_response_timeout'] = $this->mDbConfig['db_response_timeout'] != '' ? (int) $this->mDbConfig['db_response_timeout'] : 30;

      require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.nusoap_base.php';
      require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.soap_val.php';
      require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.soap_parser.php';
      require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.soap_fault.php';
      require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.soap_transport_http.php';
      require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.xmlschema.php';
      require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.wsdl.php';
      require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.soapclient.php';
      require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.wsdlcache.php';

      SysLog::Instance()->log('SoapDatabaseEngine::__construct', 'DatabaseEngine');
   }

   protected function GetCacheIdentifier($sql, $params) {
      preg_match("/^([^\(\)]*)\(([^\(\)]*)\)$/is", $sql, $parts);

      $sql_command = trim($parts[1]);
//       $params_count = trim($parts[2]) != '' ? count(explode(',', $parts[2])) : 0;
//       $params_text = '';
//       for ($i = 0; $i < $params_count; $i++) {
//          $params_text .= "\$params[$i], ";
//       }
//       $params_text = substr($params_text, 0, -2);

      $function_parsed = $sql_command . '(' . print_r($params, true) . ')'; // This is so much more unique than sqlcommand($param[0], $param[1], ...), so it's good for cache identifier

      return $function_parsed;
   }

   public function Connect() {
      // instantiate wsdl cache manager
      if ($this->mDbConfig['db_wsdl_cache_path'] != '' && $this->mDbConfig['db_wsdl_cache_enabled']) {
         $wsdl_cache = new wsdlcache($this->mDbConfig['db_wsdl_cache_path'], $this->mDbConfig['db_wsdl_cache_lifetime']);
      }
      // try to get from cache first...
      $wsdl_url = $this->mDbConfig['db_wsdl_url'];
      if ($this->mDbConfig['db_namespace']) {
         $wsdl_url_query = parse_url($wsdl_url, PHP_URL_QUERY);
         if (!$wsdl_url_query) {
            $wsdl_url .= '?nspace=' . $this->mDbConfig['db_namespace'];
         } else {
            $wsdl_url .= '&nspace=' . $this->mDbConfig['db_namespace'];
         }
      }
      if ($wsdl_cache) {
         $wsdl = $wsdl_cache->get($wsdl_url);
      }

      // cache hit? if not, get it from server and put to cache
      if (!$wsdl) {
         SysLog::Log('Cache MISSED: ' . $wsdl_url, 'SoapDatabaseEngine');
         $wsdl = new wsdl($wsdl_url,
            $this->mDbConfig['db_proxy_host'], $this->mDbConfig['db_proxy_port'],
            $this->mDbConfig['db_proxy_user'], $this->mDbConfig['db_proxy_pass'],
            $this->mDbConfig['db_connection_timeout'], $this->mDbConfig['db_response_timeout']);
         $this->mErrorMessage = $wsdl->getError();
         if ($this->mErrorMessage) {
            SysLog::Log('WSDL error: ' . $this->mErrorMessage, 'DatabaseEngine');

            $this->mErrorMessage = 'An error has occured when instantiating WSDL object (' .
               $this->mDbConfig['db_wsdl_url'] . '&nspace=' . $this->mDbConfig['db_namespace'] .
               '). The error was: "' . $this->mErrorMessage .
               '" Check your WSDL document or database configuration.';
            return FALSE;
         }
         if ($wsdl_cache)
            $wsdl_cache->put($wsdl);
      } else
         SysLog::Log('Cache HIT: '.$this->mDbConfig['db_wsdl_url'] . '&nspace=' . $this->mDbConfig['db_namespace'], 'SoapDatabaseEngine');

      // use it as usual
      $temp = new soapclient($wsdl, TRUE, $this->mDbConfig['db_proxy_host'], $this->mDbConfig['db_proxy_port'],
         $this->mDbConfig['db_proxy_user'], $this->mDbConfig['db_proxy_pass'], $this->mDbConfig['db_connection_timeout'],
         $this->mDbConfig['db_response_timeout']);
      $this->mErrorMessage = $temp->getError();
      if (!$this->mErrorMessage) {
         $this->mrDbConnection = $temp->getProxy();
         if (isset($this->mDbConfig['db_credentials']))
            $this->mrDbConnection->setCredentials($this->mDbConfig['db_credentials']['user'],
               $this->mDbConfig['db_credentials']['pass'], $this->mDbConfig['db_credentials']['type'],
               $this->mDbConfig['db_credentials']['cert']);
         return TRUE;
      } else {
         SysLog::Log('Error in SoapDatabaseEngine: '. $temp->getError(), 'SoapDatabaseEngine');
         return FALSE;
      }
   }

   public function Disconnect() {
      $this->mrDbConnection = NULL;
      return TRUE;
   }

   public function StartTrans() {
      return TRUE;
   }

   public function EndTrans($condition) {
      return TRUE;
   }

   public function Open($sql, $params, $varMarker = NULL) {
      $result = $this->RunQuery($this->mrDbConnection, $sql, $params, md5($this->mDbConfig['db_wsdl_url']));

      if ($this->mrDbConnection->fault) {
         return FALSE;
      } else {
         if ($this->mrDbConnection->getError()) {
            return FALSE;
         } else {
            return $result;
         }
      }
   }

   public function Execute($sql, $params, $varMarker = NULL) {
      $result = $this->RunQuery($this->mrDbConnection, $sql, $params, md5($this->mDbConfig['db_wsdl_url']));

      if ($this->mrDbConnection->fault) {
         return FALSE;
      } else {
         if ($this->mrDbConnection->getError()) {
            return FALSE;
         } else {
            return TRUE;
         }
      }
   }

   public function AffectedRows() {
      return FALSE;
   }

   public function LastInsertId() {
      return FALSE;
   }

   public function SetDebugOn() {
      $GLOBALS['debug'] = TRUE;
   }

   public function SetDebugOff() {
      $GLOBALS['debug'] = FALSE;
   }

   public function GetLastError() {
      if ($GLOBALS['debug'] == TRUE) // debug message is always superior than error message
         if ($this->mrDbConnection)
            return "Request:\n" . $this->mrDbConnection->request . "\n\nResponse:\n" .
               $this->mrDbConnection->response . "\n\nDebug info:\n" .
               $this->mrDbConnection->debug_str;
      if ($this->mrDbConnection)
         return $this->mrDbConnection->getError();
      if ($this->mErrorMessage)
         return $this->mErrorMessage;
      return 'An error has occured when instantiating ' . $this->mDbConfig['db_driv'] . ' driver.';
   }

   ///FIXME: what about a GetFoo('default', %s, %s) call?
   private function RunQuery($obj, $sql, $params, $id) {
      preg_match("/^([^\(\)]*)\(([^\(\)]*)\)$/is", $sql, $parts);

      $sql_command = trim($parts[1]);
      $params_count = trim($parts[2]) != '' ? count(explode(',', $parts[2])) : 0;
      $params_text = '';

      for ($i = 0; $i < $params_count; $i++) {
         $params_text .= "\$params[$i], ";
      }
      $params_text = substr($params_text, 0, -2);
      $function_parsed = $sql_command . '(' . $params_text . ')';
      if (empty($sql_command)) {
         SysLog::Log('Unable to understand "'.$sql_command.'". This command resulted in empty sql_command', 'soapproxy');
         $obj->fault = TRUE;
         $obj->setError('Unable to understand "'.$sql_command.'". This command resulted in empty sql_command');
         return false;
      }

      try {
         $reflect = new ReflectionMethod(get_class($obj), $sql_command);
         foreach ($reflect->getParameters() as $i => $param) {
            $foo .= sprintf(
               "\n-- Parameter #%d: %s {\n".
               "  Class: %s\n".
               "  Allows NULL: %s\n".
               "  Passed to by reference: %s\n".
               "  Is optional?: %s\n".
               "}\n",
               $i,
               $param->getName(),
               var_export($param->getClass(), 1),
               var_export($param->allowsNull(), 1),
               var_export($param->isPassedByReference(), 1),
               $param->isOptional() ? 'yes' : 'no'
            );
            $doclit_param[] = "'{$param->getName()}' => '{$params[$i]}'";
         }
         SysLog::Log("Calling $sql_command\n". print_r($params, true)."\n" .$foo, 'soapproxy');

         /* end debug */
         $obj->updateCookies($_SESSION['soap_cookies'][$id]);
         $obj->checkCookies();
         $result = FALSE;
         if (method_exists($obj, $sql_command)) {
            // doc/lit support
            if ( ($obj->operations[$sql_command]['style'] == 'document') && ($obj->operations[$sql_command]['input']['use'] == 'literal') ) {
               $result = call_user_func_array(array(&$obj, $sql_command), array( 'parameters' => $params) );

               SysLog::Log("Using doc/lit \$obj->$sql_command( array( 'parameters' =>". join($doclit_param, ', ')." ))", 'soapproxy');
            } else {
               $result = call_user_func_array(array(&$obj, $sql_command), $params);
               SysLog::Log("Using rpc/enc \$obj->$function_parsed", 'soapproxy');
            }

            $_SESSION['soap_cookies'][$id] = $obj->getCookies();
         } else {
            SysLog::Log('Request to undefined service '.$sql_command, 'soapproxy');
            $obj->fault = TRUE;
            $obj->setError('Request to undefined service '.$sql_command);
         }
      } catch (ReflectionException $e) {
         SysLog::Log('Service "'.$sql_command.'" not found!', 'soapproxy');
         $obj->fault = TRUE;
         $obj->setError('Service "'.$sql_command.'" not found!');
         return false;
      }

      SysLog::Log("Returning:\n".print_r($result, true), 'soapproxy');
      return $result;
   }
}
?>