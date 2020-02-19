<?php
class ShtmlResponse implements ResponseIntf {
   var $mrDispatcher;
   var $mrSecurity;
   var $mrSession;

   var $mHtmlFilename;

   function ShtmlResponse() {
      $this->mrDispatcher =& $GLOBALS['dispt'];
      $this->mrSecurity =& $GLOBALS['sec'];
      $this->mrSession =& $GLOBALS['sess'];
   }

   // this is optional, you don't have to override this method
   function ProcessRequest() {
   }

   function Display() {
      $this->ProcessRequest();
      if (file_exists($this->mHtmlFilename)) {
         require_once $this->mHtmlFilename;
      } else {
         echo 'No such file!';
      }
   }

   function &GetHandler() {
      return $this;
   }

   function Send() {
      $this->Display();
   }
}
?>