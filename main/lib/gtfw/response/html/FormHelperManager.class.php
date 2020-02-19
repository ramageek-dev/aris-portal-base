<?php
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/response/html/FormHelper.class.php';

class FormHelperManager {
   function __construct($FormHelpers) {
      $this->mFormHelpers = $FormHelpers;
   }

   function GetFormHelperManagerJs($standalone = true) {
      $js1 = "";
      if (!empty($this->mFormHelpers)) {
            $js_formhelper .=<<<_FORM_HELPER_
   <script type="text/javascript">
      /* must preceed behaviour Library to get the desired effect: add validation selector and then let the rest handled by the simple_validator */
      function GtfwFormHelper() {
_FORM_HELPER_;

               //
               foreach ($this->mFormHelpers as $item)
                  $js_formhelper .= "\n".$item->getJsCode()."\n";

               $js_formhelper .=<<<_FORM_HELPER_
      }
_FORM_HELPER_;

         if ($standalone) {
            $js_formhelper .= "\nFormHelperManager.addLoadEvent(GtfwFormHelper); /* apply */"; // use loadevent
            SysLog::Log('Preparing formHelper for fullpage request', 'formhelpermanager');
         } else {
            $js_formhelper .= "\nGtfwFormHelper(); /* run immediately */";// run immediately
            SysLog::Log('Preparing formHelper for component requiremnt', 'formhelpermanager');
         }
         $js_formhelper .= '</script>';
      } else {
         SysLog::Log('No FormHelper set', 'formhelpermanager');
         $js_formhelper = '';
      }

      if ($standalone) {
         $js =<<<_RAW_HEAD_
   <script type="text/javascript" src="js/FormHelper.js"></script>
_RAW_HEAD_;

         $js1 .=<<<_RAW_HEAD_
   <script type="text/javascript" src="js/behaviour.js"></script>
   <script type="text/javascript" src="js/balloon.js"></script>
   <script type="text/javascript" src="js/simple_validator.js"></script>
_RAW_HEAD_;

            // load available js rules
            foreach (FormHelper::$JsRuleFiles as $item) {
               SysLog::Log('Loading JsRulesFiles: '.$item.'.js', 'formhelpermanager');
               $js1 .=<<<_RAW_HEAD_
   <script type="text/javascript" src="js/formhelper_rules/$item.js"></script>
_RAW_HEAD_;
            }
      }

      if ($standalone)
         return $js.$js_formhelper.$js1;
      else
         return $js_formhelper;
   }

}

?>