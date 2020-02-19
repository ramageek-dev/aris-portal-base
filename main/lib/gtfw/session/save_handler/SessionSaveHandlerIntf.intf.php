<?php
interface SessionSaveHandlerIntf {
   // implements session save handler
   // will automatically be called by php
   function SessionOpen($savePath, $sessionName);
   function SessionClose();
   function SessionRead($sessionId);
   function SessionWrite($sessionId, $sessionData);
   function SessionDestroy($sessionId);
   function SessionGc($maxExpireTime);

   // implements session expiration check
   // called by Session::Start to determine whether current session
   // has been expired
   // param: session id
   // return: boolean, true indicating session is valid and vice versa
   function IsSessionExpired($sessionId);
}
?>