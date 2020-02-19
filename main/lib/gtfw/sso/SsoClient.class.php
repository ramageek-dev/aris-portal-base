<?php
// SSOCLient
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/nusoap.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/sso/SsoIdAttribute.class.php';
// require_once Configuration::Instance()->GetValue('application', 'gtfw_base').'main/lib/syslog/Syslog.class.php';

/**
* SSO Client Library. Provides friendly interface to get client easily incorporate sso into its system
* @package SsoClient
* @author Akhmad Fathonih <toni@gamatechno.com>
* @version 1.0
* @copyright 2006&copy;Gamatechno
*/

class SsoClient {
   var $mSoapClient;

   function SsoClient() {

      $this->mSoapClient =& new soapclient(Configuration::Instance()->GetValue( 'application', 'baseaddress') .
         Dispatcher::Instance()->GetUrl('ssobroker', 'Broker', 'Sso', 'soap'));
      $this->mSoapClient->getError();
   }

   /**
   * Authenticate SSO user
   * @param string $SsoUsername
   * @param string $SsoPassword
   * @return boolean
   */
   function authenticateSsoUser($SsoUsername, $SsoPassword, $domain) {
      // TODO: makesure it's ecrypted before trasmited over the net
      $result = $this->mSoapClient->call('AuthSsoUser',
                  array('sso_username' => $SsoUsername,
                        'sso_password' => $SsoPassword,
                        'sso_domain' => $domain)
                );

      SysLog::Instance()->log("authenticateSsoUser got SsoAuth: ".print_r($result, true), "SsoClient");

      return $result;
   }

   /**
   * Authenticate SSID. Check wether it's still valid
   * @param string $Ssid
   * @return boolean
   */
   function authenticateSsid($Ssid) {
      // TODO: makesure it's ecrypted before trasmited over the net
      $result = $this->mSoapClient->call('AuthSsoSsid',
                  array('sso_ssid' => $Ssid)
                );

      return $result;
   }

   /**
   * Authenticate and assocaiate SSID
   * @param string $Ssid
   * @param string $domain
   * @return boolean
   */
   function authenticateAndAssocSsid($Ssid, $domain) {
      // TODO: makesure it's ecrypted before trasmited over the net
      $result = $this->mSoapClient->call('AuthAndAssocSsoSsid',
                  array('sso_ssid' => $Ssid, 'sso_domain' => $domain)
                );

      return $result;
   }

   /**
   * Invalidate SSID
   * @param Ssid
   * @return boolean
   */
   function invalidateSsid($Ssid) {
      // TODO: makesure it's ecrypted before trasmited over the net
      $result = $this->mSoapClient->call('InvalidateSsid',
                  array('sso_ssid' => $Ssid)
                );

      return $result;
   }

   /**
   * Request SSID atribute (eg: username, etc) which will then be used to log into local system and run the system as usual (without SSO)
   * @param string $Ssid
   * @param string $domain system id
   * @return array Refer to SsidAttributes class (ssolib.php)
   * @todo make return value an object
   * @see SsidAttributes
   */
   function requestSsidAttributes($Ssid, $domain) {
      // TODO: makesure it's ecrypted before trasmited over the net
      //$this->mSoapClient->debug = true;
      $result = $this->mSoapClient->call('RequestSsidAttributes',
                  array('sso_ssid' => $Ssid, 'sso_systemid' => $domain)
                );

      SysLog::Instance()->log("requestSsidAttributes soaprequest: ".print_r($this->mSoapClient->request, true), "SsoClient");

      SysLog::Instance()->log("requestSsidAttributes soapresponse: ".print_r($this->mSoapClient->response, true), "SsoClient");

      //SysLog::Instance()->log("requestSsidAttributes response data: ".print_r($this->mSoapClient->responseData, true), "SsoClient");

      SysLog::Instance()->log("requestSsidAttributes got SsidAttributes: ".print_r($result, true), "SsoClient");

      //SysLog::Instance()->log("requestSsidAttributes debug: ".$this->mSoapClient->getDebug(), "SsoClient");
      //echo htmlentities($this->mSoapClient->getDebugAsXMLComment());
      //$this->mSoapClient->debug = false;
//       echo "<pre>".htmlentities($this->mSoapClient->Response);
//       var_dump($this->mSoapClient);
//       echo "</pre>";
//       die();

      return $result;
   }

   /**
   * Save SSID to local
   * @param string $Ssid
   * @param string $sso_group
   */
   function saveSsIdToLocal($SsId, $sso_group) {
      setcookie($sso_group, $SsId, time() + 60480, "/");
   }

   /**
   * Remove local SSID
   * @param string $Ssid
   */
   function removeLocalSsId($sso_group) {
      setcookie($sso_group, false, time() - 60480, "/");
      //echo "SsoClient::removeLocalSsId";
   }

   /**
   * Read local SSID
   * @param Ssid
   */
   function getLocalSsId($sso_group) {
      return $_COOKIE[$sso_group];
   }

   /**
   * Query wetehr Sso server is alive and available
   * @return boolean true when server is alive
   */
   function isSsoAlive() {
      $result = $this->mSoapClient->call('IsSsoAlive');

      return $result;
   }

   /**
   * Modify user password
   * @param string $SsoId
   * @param string $Domain
   * @param string $OldPass
   * @param string $NewPass will be passed over craftPassword()
   * @return boolean true on successful modification
   * @see LdapManager::craftPassword()
   */
   function modifyPassword($SsoId, $Domain, $OldPass, $NewPass) {
      $result = $this->mSoapClient->call('IsSsoAlive', array("sso_username" => "$SsoId", "sso_domain" => "$Domain", "old_sso_password" => "$OldPass", "new_sso_password" => "$NewPass"));

      return $result;
   }
}
?>