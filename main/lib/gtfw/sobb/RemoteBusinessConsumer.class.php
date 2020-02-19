<?php
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/nusoap.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.wsdlcache.php';

/**
* Class to facilitate seamless remote business consuming
* @author Akhmad Fathonih <toni@gamatechno.com>
* @package SoapCommunication
*/

class RemoteBusinessConsumer {
   public $mTransport;
   private $mEndPoint;
   private $mUsername;
   private $mPassword;
   private $mAuthType;
   private $mCertRequest;

   private $mProxyHost;
   private $mProxyPort;
   private $mProxyUser;
   private $mProxyPass;

   private function __construct() {
   }

   function InitTransport($wsdlUrl) {
      // instantiate wsdl cache manager
      $cache_use = (bool) Configuration::Instance()->GetValue('application', 'wsdl_use_cache');
      $cache_path = file_exists(Configuration::Instance()->GetValue('application', 'wsdl_cache_path')) ? Configuration::Instance()->GetValue('application', 'wsdl_cache_path') : Configuration::Instance()->GetValue('application', 'session_save_path');
      // paranoia check, if session_save_path set to NULL
      $cache_path = file_exists($cache_path) ? $cache_path : ini_get('session.save_path');
      //
      $cache_lifetime = Configuration::Instance()->GetValue('application', 'wsdl_cache_lifetime') != '' ? (int) Configuration::Instance()->GetValue('application', 'wsdl_cache_lifetime') : 60 * 60 * 24; // defaults to 1 day

      if ($cache_path != '' && $cache_use) {
         $wsdl_cache = new wsdlcache($cache_path, $cache_lifetime);
      }
      // try to get from cache first...
      if ($wsdl_cache) {
         $wsdl = $wsdl_cache->get($wsdlUrl);
      }
      // cache hit? if not, get it from server and put to cache
      if (!$wsdl) {
         $wsdl = new wsdl($wsdlUrl, $this->mProxyHost, $this->mProxyPort, $this->mProxyUser, $this->mProxyPass);

         if ($wsdl_cache && !$wsdl->getError())
            $wsdl_cache->put($wsdl);
      }

      $this->mTransport = new soapclient($wsdl, TRUE, $this->mProxyHost, $this->mProxyPort, $this->mProxyUser, $this->mProxyPass);
   }

   function GetDefaultEndpoint() {
      $tmpEndPoint = Configuration::Instance()->GetValue( 'application', 'app_service_endpoint');
      if(isset($tmpEndPoint))
         $endpoint = Configuration::Instance()->GetValue( 'application', 'app_service_endpoint');
      else
         $endpoint = Configuration::Instance()->GetValue( 'application', 'baseaddress').Dispatcher::Instance()->GetUrl('soap_gateway', 'Gateway', 'Soap', 'soap')."&wsdl";


      return $endpoint;
   }

   function ForceEndpoint($endpoint) {
      $this->mEndPoint = $endpoint;
   }

   /**
   set credential to handle REALM
   @param $username username
   @param $password password
   @param $authtype realm type (basic, digest, etc). optional.
   @param $certRequest array of certificate
   @see soapclient::setCredentials
   */
   function SetCredentials($username, $password, $authtype = 'basic', $certRequest = array()) {
      $this->mUsername = $username;
      $this->mPassword = $password;
      $this->mAuthType = $authtype;
      $this->mCertRequest = $certRequest;

      if (isset($this->mTransport))
         $this->mTransport->setCredentials($username, $password, $authtype, $certRequest);
   }

   function SetProxy($proxyHost, $proxyPort, $proxyUser = '', $proxyPass = '') {
      $this->mProxyHost = $proxyHost;
      $this->mProxyPort = $proxyPort;
      $this->mProxyUser = $proxyUser;
      $this->mProxyPass = $proxyPass;

      if (isset($this->mTransport))
         $this->mTransport->setHTTPProxy($proxyHost, $proxyPort, $proxyUser, $proxyPass);
   }

   function SetCookie($cookie) {
      $this->mCookies[] = $cookie;
   }

   function GetCookie() {
      return $this->mCookies;
   }
}

interface IPublicBusinessConsumer {
   function ImportBusiness($endpoint = '');
}

class PublicBusinessConsumer extends RemoteBusinessConsumer implements IPublicBusinessConsumer {
   private $mProxy;

   private function __construct() {
      parent::__construct();
   }

   /**
   initiate proxy class. from this point, proxy class will be available. Cookies should be set before invoking this function, other wise it'll be ignored.
   @param $endpoint (optional)
   @return proxy object
   */
   function ImportBusiness($endpoint = '') {
      if (empty($endpoint)) {
         $this->mEndPoint = $this->getDefaultEndpoint();
         $endpoint = $this->mEndPoint;
      }
      $this->InitTransport($endpoint);
      $this->mProxy =& $this->mTransport->getProxy();
      if (isset($this->mUsername)) {
         $this->mProxy->setCredentials($this->mUsername, $this->mPassword, $this->mAuthType, $this->mCertRequest);
      }

      return $this->mProxy;
   }

   /**
   Interceptor. Detect service call, preceeded with service_. Dispatch accordingly
   */
   function __call($func, $args) {
      if (preg_match("/service_(.*)/", $func, $found)) {
         // what to call
         SysLog::Instance()->log("Invoking: {$found[1]}", 'soapgateway');

         // auto export cookie
         $outgoing_cookies = RemoteCookieManager::GetCookies();
         SysLog::Instance()->log("outgoing cookie: \n".print_r($outgoing_cookies, true), 'cookie');
         if (is_array($outgoing_cookies))
            foreach($outgoing_cookies as $cookie_hash => $cookie)
               $this->SetCookie($cookie);

         if (isset($this->mCookies))
            foreach($this->mCookies as $cookie)
               $this->mProxy->SetCookie($cookie);

         //return call_user_func_array(array(&$this->mProxy, $func, $args ));
         $res = call_user_func_array(array(&$this->mProxy, $found[1]), $args );

         // auto import cookie
         $incoming_cookie = $this->mProxy->getCookies();
         SysLog::Instance()->log("incoming cookie: \n".print_r($incoming_cookie, true), 'cookie');
         if (is_array($incoming_cookie))
            foreach($incoming_cookie as $cookie)
               RemoteCookieManager::SetCookie($cookie);

         return $res;
      } else
         return call_user_func_array(array(&$this, $func), $args ); // recursive?
   }
}

class DefaultServiceConsumer extends PublicBusinessConsumer {
   private static $mInstance;

   private function __construct() {
      parent::__construct();

      $this->ImportBusiness();
   }

   static function Instance() {
      if (!isset(self::$mInstance))
         self::$mInstance = new DefaultServiceConsumer();

      return self::$mInstance;
   }
}

class CookieServiceConsumer extends PublicBusinessConsumer {
   function __construct() {
      parent::__construct();

      // must preceeds ImportBusiness!!
      $this->SetCredentials('foo', 'bar');
      $this->SetCookie('foo-cookie', 'bar-value');

      $this->ImportBusiness();
   }
}

class RemoteCookieManager {
   private static $mCookies;

   private function __construct() {
   }

   static function SetCookie($cookie) {
      ///TODO: must overwrite idetical cookie (same name, domain, and path)
      $_SESSION['soap_cookie'][$cookie['name'].'_1_'.$cookie['domain'].'_2_'.$cookie['path'].'_3_'] = $cookie;
   }

   static function GetCookies() {
      return $_SESSION['soap_cookie'];
   }
}

?>