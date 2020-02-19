<?php
class AjaxExt_ReplaceContent implements AjaxExtIntf {
   function __construct($id, $content) {
      $this->mId = $id;
      $this->mContent = addslashes($content);
   }

   function GetJsCode() {
      //return 'GtfwAjax.replaceContent(\''.$this->mId.'\', \''.$this->mContent.'\')';
   }
}

?>