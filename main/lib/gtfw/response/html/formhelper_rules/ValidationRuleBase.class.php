<?php

/**
 base for every rules
 */
abstract class ValidationRuleBase {
   protected $mTestValue;
   private $mElementName;

   function __construct() {
      $this->mError = array();
   }

   /**
    * Set test value to validate
    * @param string $testValue
    */
   function setTestValue($testValue) {
      $this->mTestValue = $testValue;
   }

   /**
    * Set fieldname associated with test value
    * @param string $testValue
    */
   function setElementName($testValue) {
      $this->mElementName = $testValue;
   }

   /**
    * Set parent form name associated with test value
    * @param string $form_name
    */
   function setParentForm($form_name) {
      $this->mFormName = 'fh_'.$form_name;
   }

   function setError($err) {
      //array_push($this->mError, $err);
      // enhaced :: index error berupa nama validator
      $this->mError[$this->mRuleStr] = $err;
   }

   function getError() {
      $err = array_pop($this->mError);
      return $err;
   }

   function getAllErrors() {
      return $this->mError;
   }

   final function validate() {
      $test_value = $this->mTestValue;
      SysLog::Log($this->getRuleName().': testing TestValue: "'.print_r($test_value, true).'"', 'formhelper');
      if (is_array($test_value)) {
         foreach($test_value as $key => $value) {
            if (!$this->test($value))
               return false;
         }
         return true;
      } else
         return $this->test($test_value);
   }

   /**
    * Must validate testvalue and set appropriate error
    */
   abstract function test($test_value);

   function setCustomError($error_msg) {
      $this->mCustomError = $error_msg;
   }

   /**
    * can be overrided to provide custom error mesage
    */
   function getCustomError() {
      if (trim($this->mCustomError) != '')
         return $this->mCustomError;
      else
         return 'Field '.$this->mElementName.' harus diisi';
   }

   function isValid() {
      $this->mError = array();
      return $this->validate();
      //return (empty($this->mError));
   }

   /**
    * JS code needed to make the validation rule works. By deafult it only calls FormHelper to subscribe a clietn side validation, and set custom error messag (if any set)
    * @return string Javascript code
    */
   function getJsCode() {
      $result = "\n{$this->mFormName}._addValidation(\"{$this->mElementName}\",\"balloon\");\n{$this->mFormName}._addValidation(\"{$this->mElementName}\", \"{$this->getRuleName()}\");";

      if ($this->getCustomError())
      $result .= "\n {$this->mFormName}.setCustomErrorMessage(\"{$this->mElementName}\", \"{$this->getCustomError()}\");";

      return $result;
   }

   /**
    * @return string rule name, eg: required_input, type_number
    */
   abstract public function getRuleName();
}

?>