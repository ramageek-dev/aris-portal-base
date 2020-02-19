<?php
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/jpgraph/src/jpgraph.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/jpgraph/src/jpgraph_bar.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/jpgraph/src/jpgraph_line.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/jpgraph/src/jpgraph_pie.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/jpgraph/src/jpgraph_pie3d.php';

class ImgResponse {   
   
   function ImgResponse() {      
   }

   // if you want something else then you should
   // override this method
   function GetFileName() {
      return 'filename.jpg';
   }

   function ProcessRequest() {
      echo 'ImgResponse->ProcessRequest(): This function must be overrided!';
      return NULL;
   }
   
    function &GetHandler() {
      return $this;
   }

   function Send() {      
      $this->ProcessRequest();
   }
} 
?>
