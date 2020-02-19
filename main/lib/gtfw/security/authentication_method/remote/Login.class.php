<?php
class Login extends LoginBase {
   var $mUser;

   function __construct() {
   }

   function DoLogin() {
      SysLog::Instance()->log('login(remote): Starting', 'login');
      $def_svc = new LoginConsumer();

      return $def_svc->service_auth_Login($this->mUsername, $this->mPassword, $this->mPasswordIsHashed);
   }
}

class LoginConsumer extends PublicBusinessConsumer {
   function __construct() {
      parent::__construct();

      // must preceeds importBusiness!!
      $this->forceEndpoint('http://192.168.1.51/~geek/gtfw-login/index.php?mod=soapgateway&sb=Gateway&act=soap&typ=soap&wsdl');

      $this->importBusiness();
   }
}
?>