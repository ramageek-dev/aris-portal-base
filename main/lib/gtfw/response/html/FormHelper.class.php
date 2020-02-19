<?php
/**
 * Manages $_REQUEST fields validation
 */
class FormHelper {
   public static $JsRuleFiles = array();
   private $mValidationRules;
   private $mBindings;
   private $mHadBeenValidated;
   private $mCompound;

   function FormHelper($form_name) {
      $this->mValidationRules = array();
      $this->mBindings = array();
      $this->mError = array();
      $this->mHadBeenValidated = false;
      $this->mFormName = $form_name;
   }

   /**
    Decides on whom this validator should work onto. eg: $_POST, $_GET or other array
    @param array $compound
    */
   public function ApplyTo($compound) {
      $this->mCompound = $compound;
   }

   protected function GetCompound() {
      if (isset($this->mCompound))
         return $this->mCompound;
      else
         return $_REQUEST;
   }

   /**
    * Register a validator
    * @param string $custom_rule rule to assocaite with
    * @param object $obj_validator A ValidationRuleBase class child
    */
   function addValidationRule($custom_rule, $obj_validator) {
      $this->mValidationRules[$custom_rule] = $obj_validator;
   }

   /**
    * shortcut for addValidationRule
    * @param object $obj_validator
    * @see addValidationRule
    */
   function addValidatorObj($obj_validator) {
      $this->mValidationRules[$obj_validator->getRuleName()] = $obj_validator;
   }

   /**
    * validate rule subscribers and set appropriate error (if any)
    */
   function validate() {
      $this->mError = array();
      $compound = $this->GetCompound();
      foreach($this->mBindings as $item) {
         if (isset($this->mValidationRules[$item['rule']])) {
            $validator = $this->mValidationRules[$item['rule']];
            $validator->setElementName($item['element']);
            SysLog::Log('setTestValue: '.$compound[$item['element']], 'formhelper');
            $validator->setTestValue($compound[$item['element']]);
            $validator->setCustomError($item['error_msg']);
            if (!$validator->isValid()) {
               SysLog::Log('Failed on element: '.$item['element']. 'with rule "'.$validator->getRuleName().'"', 'formhelper');
               //var_dump($validator->getAllErrors());
               //die();
               //array_push($this->mError, $validator->getAllErrors());
               // enhaced :: index error berupa field form
               if (isset($this->mError[$item['element']]))
                  $this->mError[$item['element']] = $this->mError[$item['element']] + $validator->getAllErrors();
               else
                  $this->mError[$item['element']] =  $validator->getAllErrors();
            }
         } else {
            //array_push($this->mError, 'Cannot found any validator capable of validating '.$item['element'] . ' using rule "'.$item['rule'].'"');
            // enhaced :: index error berupa field form
            $this->mError[$item['element']] =  'Cannot found any validator capable of validating '.$item['element'] . ' using rule "'.$item['rule'].'"';
         }
      }

      $this->mHadBeenValidated = true;
   }

   /**
    * validate rule subscribers
    * @param bool $force_revalidation Always re-validate flag
    * @return boolean
    */
   function isValid($force_revalidation = false) {
      if ((!$this->mHadBeenValidated) || ($force_revalidation) )
         $this->validate();
      SysLog::Log('FormHelper::isValid: mError count='.count($this->mError), 'formhelper');
      return (empty($this->mError));
   }

   /**
    * subscribe a validation rule. Bing iven field to a validation rule
    * @param string $elm_name field name
    * @param string $validation_rule validation rule to bind with
    * @param string $custom_error_message Use this error message when error happen
    */
   function subscribeValidation($elm_name, $validation_rule, $custom_error_message = "") {
      $this->mBindings[] = array( 'element' => $elm_name, 'rule' => $validation_rule, 'error_msg' => $custom_error_message);
   }

   function getJsCode() {
      $js .=<<<_RAW_HEAD_
         var fh_{$this->mFormName} = new FormHelper("{$this->mFormName}");
_RAW_HEAD_;
      foreach($this->mBindings as $item) {
         if (isset($this->mValidationRules[$item['rule']])) {
            $validator = $this->mValidationRules[$item['rule']];
            $validator->setElementName($item['element']);
            $validator->setParentForm($this->mFormName);
            $validator->setCustomError($item['error_msg']);
            $js.="\n".$validator->getJsCode();
         } else {
            $js.= "\n". '// Cannot found any validator capable of validating '.$item['element'] . ' using rule "'.$item['rule'].'"';
         }
      }
      return $js;
   }

   function getError() {
      $err = array_pop($this->mError);
      return $err;
   }

   function getAllErrors() {
      return $this->mError;
   }
}

if (!defined('FORM_HELPER_EXTENSION_LOADED')):
define('FORM_HELPER_EXTENSION_LOADED', 1);
// start brute includes :))
   $d = dir(Configuration::Instance()->GetValue( 'application', 'gtfw_base').'main/lib/gtfw/response/html/formhelper_rules/');
   SysLog::Log( "Loading formhelper extensions from: " . $d->path, 'formhelper');
   while (false !== ($entry = $d->read())) {
      if (preg_match('/.*?\.rule\.class\.php$/', $entry, $matches)) {
         SysLog::Log( "Loading formhelper rule: " . $entry, 'formhelper');
         require_once  $d->path.'/'.$entry;

         /// TODO: add js lib checking for current validator, autoload it into FormHelper
         if (preg_match('/(.*?)\./', $entry, $matches)) {
            $js_rule = $matches[1];
            if (file_exists(Configuration::Instance()->GetValue( 'application', 'docroot').'js/formhelper_rules/'.$js_rule.'.js')) {
               SysLog::Log( "Loading formhelper js rule: " . $js_rule.'.js', 'formhelper');
               FormHelper::$JsRuleFiles[] = $js_rule;
            } else {
               SysLog::Log( "Unable to find formhelper js rule '" . Configuration::Instance()->GetValue( 'application', 'docroot').'js/formhelper_rules/'.$js_rule.'.js\'. JS validation may be by passed', 'formhelper');
            }
         }
      }
   }
   $d->close();

endif;

?>