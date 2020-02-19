<?php
class DbfResponse implements ResponseIntf {
   
   var $mrDbf;
   var $mPath;
   #var $mDestination = 'inline'; // this could be inline or attachment

   function DbfResponse() {
      require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
         'main/lib/dbf/my2dbf.class.php';

      $this->mPath = Configuration::Instance()->GetValue('application', 'docroot') . 'file/laporan_dikti/';
      
      $this->mrDbf =& new My2DBF($this->mPath, $this->GetFileName());
   }

   // default to filename.dbf
   // if you want something else then you should
   // override this method
   function GetFileName() {
      return 'filename.dbf';
   }

   function ProcessRequest() {
      echo 'DbfResponse->ProcessRequest(): This function must be overrided!';
      return NULL;
   }

   function &GetHandler() {
      return $this;
   }

   function Send() {
      $this->ProcessRequest();

      header("Content-type: application/x-download");
      #header('Content-Disposition: ' . $this->mDestination . '; filename=' . $this->GetFileName());
      header('Content-Disposition: attachment; filename=' . $this->GetFileName());
      header("Accept-Ranges: bytes");
      $file = $this->mPath.$this->GetFileName();
      header("Content-Length: ".filesize($file));
      @readfile($file);
      unlink($file);
   }
}
?>
