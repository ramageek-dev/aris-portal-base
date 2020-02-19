<?php
class PhpSoapDatabaseEngine extends DatabaseEngineBase {
   protected $mErrorMessage = '';
   protected $mDebugMode = FALSE;

   function __construct($dbConfig = NULL) {
      parent::__construct($dbConfig);

      // preparing wsdl cache configuration
      if (!isset($this->mDbConfig['db_wsdl_cache_enabled']))
         $this->mDbConfig['db_wsdl_cache_enabled'] = TRUE;
      ini_set('soap.wsdl_cache_enabled', (string) intval($this->mDbConfig['db_wsdl_cache_enabled']));
      $this->mDbConfig['db_wsdl_cache_lifetime'] = $this->mDbConfig['db_wsdl_cache_lifetime'] != '' ? (int) $this->mDbConfig['db_wsdl_cache_lifetime'] : 60 * 60 * 24; // defaults to 1 day
      ini_set('soap.wsdl_cache_ttl', $this->mDbConfig['db_wsdl_cache_lifetime']);
      $this->mDbConfig['db_wsdl_cache_path'] = file_exists($this->mDbConfig['db_wsdl_cache_path']) ? $this->mDbConfig['db_wsdl_cache_path'] : Configuration::Instance()->GetTempDir();
      ini_set('soap.wsdl_cache_dir', $this->mDbConfig['db_wsdl_cache_path']);

      $this->mDbConfig['db_connection_timeout'] = $this->mDbConfig['db_connection_timeout'] != '' ? (int) $this->mDbConfig['db_connection_timeout'] : 30; // default to 30

      SysLog::Instance()->Log('PhpSoapDatabaseEngine::__construct', 'DatabaseEngine');
   }

   protected function GetParsedSql($sql, $params) {
      preg_match("/^(.*)\((.*)\)$/is", $sql, $parts);

      $sql_command = trim($parts[1]);
      return $sql_command;
   }

   protected function GetCacheIdentifier($sql, $params) {
      preg_match("/^([^\(\)]*)\(([^\(\)]*)\)$/is", $sql, $parts);

      $sql_command = trim($parts[1]);
      // This is so much more unique than sqlcommand($param[0], $param[1], ...),
      // so it's good for cache identifier
      $function_parsed = $sql_command . '(' . print_r($params, TRUE) . ')';

      return $function_parsed;
   }

   public function Connect() {
      try {
         $wsdl_url = $this->mDbConfig['db_wsdl_url'];
         if ($this->mDbConfig['db_namespace']) {
            $wsdl_url_query = parse_url($wsdl_url, PHP_URL_QUERY);
            if (!$wsdl_url_query) {
               $wsdl_url .= '?nspace=' . $this->mDbConfig['db_namespace'];
            } else {
               $wsdl_url .= '&nspace=' . $this->mDbConfig['db_namespace'];
            }
         }

         // support http basic auth only
         $this->mrDbConnection = new SoapClient($wsdl_url,
            array('trace' => TRUE,
               'proxy_host' => $this->mDbConfig['db_proxy_host'],
               'proxy_port' => (int) $this->mDbConfig['db_proxy_port'],
               'proxy_login' => $this->mDbConfig['db_proxy_user'],
               'proxy_password' => $this->mDbConfig['db_proxy_pass'],
               'login' => $this->mDbConfig['db_credentials']['user'],
               'password' => $this->mDbConfig['db_credentials']['pass'],
               'local_cert' => $this->mDbConfig['db_credentials']['cert'],
               'passphrase' => $this->mDbConfig['db_credentials']['passphrase'],
               'exceptions' => TRUE,
               'classmap' => $this->mDbConfig['db_class_map'],
               'connection_timeout' => $this->mDbConfig['db_connection_timeout']
               ));
      } catch (SoapFault $fault) {
         $this->mErrorMessage = $fault->faultcode . ': ' . $fault->faultstring;
         return FALSE;
      }

      return TRUE;
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
      $this->mErrorMessage = '';
      $id = md5($this->mDbConfig['db_wsdl_url']);
      try {
         if (isset($_SESSION['soap_cookies'][$id]) && !empty($_SESSION['soap_cookies'][$id]))
            foreach ($_SESSION['soap_cookies'][$id] as $name => $value)
               $this->mrDbConnection->__setCookie($name, $value[0]);
         $result = call_user_func_array(array(&$this->mrDbConnection, $this->GetParsedSql($sql, NULL)), $params);
         $_SESSION['soap_cookies'][$id] = $this->mrDbConnection->_cookies;
         // emulates nusoap result
         if (is_array($result) && !isset($this->mDbConfig['db_class_map'])) {
            $res = array();
            foreach ($result as $value) {
               if (is_object($value)) {
                  $res[] = get_object_vars($value);
               } else {
                  $res[] = $value;
               }
            }
         } elseif (is_object($result) && !isset($this->mDbConfig['db_class_map'])) {
            $res = get_object_vars($result);
         } else {
            $res = $result;
         }
         return $res;
      } catch (SoapFault $fault) {
         $_SESSION['soap_cookies'][$id] = $this->mrDbConnection->_cookies;
         $this->mErrorMessage = $fault->faultcode . ': ' . $fault->faultstring;
         return FALSE;
      }
   }

   public function Execute($sql, $params, $varMarker = NULL) {
      $this->mErrorMessage = '';
      $id = md5($this->mDbConfig['db_wsdl_url']);
      try {
         if (isset($_SESSION['soap_cookies'][$id]) && !empty($_SESSION['soap_cookies'][$id]))
            foreach ($_SESSION['soap_cookies'][$id] as $name => $value)
               $this->mrDbConnection->__setCookie($name, $value[0]);
         $result = call_user_func_array(array(&$this->mrDbConnection, $this->GetParsedSql($sql, NULL)), $params);
         $_SESSION['soap_cookies'][$id] = $this->mrDbConnection->_cookies;
         return TRUE;
      } catch (SoapFault $fault) {
         $_SESSION['soap_cookies'][$id] = $this->mrDbConnection->_cookies;
         $this->mErrorMessage = $fault->faultcode . ': ' . $fault->faultstring;
         return FALSE;
      }
   }

   public function AffectedRows() {
      return FALSE;
   }

   public function LastInsertId() {
      return FALSE;
   }

   public function SetDebugOn() {
      $this->mDebugMode = TRUE;
   }

   public function SetDebugOff() {
      $this->mDebugMode = FALSE;
   }

   public function GetLastError() {
      if ($this->mDebugMode)
         $this->mErrorMessage .= "\n\nLast Request:\n" . $this->mrDbConnection->__getLastRequestHeaders() .
            "\n\n" . $this->mrDbConnection->__getLastRequest() . "\n\nLast Response:\n" .
            $this->mrDbConnection->__getLastResponseHeaders() . "\n\n" .
            $this->mrDbConnection->__getLastResponse();

      return $this->mErrorMessage;
   }
}
?>