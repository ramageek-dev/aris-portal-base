<?php
// SSODumbCLient
require_once Configuration::Instance()->GetValue( 'application', 'docroot') . 'main/lib/gtfw/sso/SsoClient.class.php';

class SsoDumbClient extends SsoClient {

   function SsoDumbClient() {

   }

   /**
   Authenticate SSO user
   @param SsoUsername
   @param SsoPassword
   @return boolean
   */
   function authenticateSsoUser($SsoUsername, $SsoPassword) {
//       // TODO: makesure it's ecrypted before trasmited over the net
//       $result = $soap_client->call('AuthSsoUser',
//                   array('sso_username' => $SsoUsername,
//                         'sso_password' => $SsoPassword)
//                 );

      return array("status" => true, "ssid" => "xxx");
   }

   /**
   Authenticate SSID. Check wether it's still valid
   @param Ssid
   @return boolean
   */
   function authenticateSsid($Ssid) {
//       // TODO: makesure it's ecrypted before trasmited over the net
//       $result = $soap_client->call('AuthSsoSsid',
//                   array('sso_ssid' => $Ssid)
//                 );

      return true;
   }

   /**
   Invalidate SSID
   @param Ssid
   @return boolean
   */
   function invalidateSsid($Ssid) {
//       // TODO: makesure it's ecrypted before trasmited over the net
//       $result = $soap_client->call('InvalidateSsoSsid',
//                   array('sso_ssid' => $Ssid)
//                 );

      return true;
   }

   /**
   Request SSID atribute (eg: username, etc) which will then be used to log into local system and run the system as usual (without SSO)
   @param Ssid
   @return array
   @todo make return value an object
   */
   function requestSsidAttributes($Ssid, $SystemId) {
//       // TODO: makesure it's ecrypted before trasmited over the net
//       $result = $soap_client->call('RequestSsidAttributes',
//                   array('sso_ssid' => $Ssid, 'sso_systemid' => $SystemId)
//                 );
      $attr = new SSOIdAttribute();
      $attr->setLocalUsername('toni');

      return $attr;
   }
}
?>