<?php
/**
* Sanitizer, can be used as singleton or as instance.
* Sanitizer recommended to be use as singleton when there's no need to keep
* the passed var as persistent value. Otherwise, Sanitizer as isntance
* must be use as it will persist passed value. Original value can be obtained
* from Sanitizer intance via $ASanitizer->GetRaw().
* Can also be used with sanitizer::Post('post_var')->GetXXX(), which works in non persistent way.
* new value will always override old value so Sanitizer::Post('var_name')->GetRaw() will only
* be valid beore any new call to Sanitizer::Post('other_var_name')
*
* @author Yogatama, Akhmad Fathonih
* @copyright 2006 Gamatechno
*/

class Sanitizer {
   var $mGpcsOn;
   var $mrVariable;
   static private $mInstance;

   static function Instance($var = NULL) {
      if ($var == NULL) {
         if (!isset(self::$mInstance)) {
            self::$mInstance = new Sanitizer(NULL);
         }

         return self::$mInstance;
      } else {

         if ($var instanceof Sanitizer) {
            $var = $var->Raw();
         }

         $result = new Sanitizer($var);
         
         if(Configuration::Instance()->GetValue('application', 'strip_html_tags'))
            $result = $result->StripHtmlTags();
         
         return $result;
      }
   }

   static function Post($var) {
      self::Instance()->mrVariable = $_POST[$var];
      return self::$mInstance;
   }

   static function Get($var) {
      self::Instance()->mrVariable = $_GET[$var];
      return self::$mInstance;
   }

   static function Cookie($var) {
      self::Instance()->mrVariable = $_COOKIE[$var];
      return self::$mInstance;
   }

   static function Request($var) {
      self::Instance()->mrVariable = $_REQUEST[$var];
      return self::$mInstance;
   }

   function __construct($var) {
      if ($var instanceof Sanitizer) {
         $var = $var->Raw();
      }

      $this->mrVariable = $var;
      $this->mGpcsOn = (bool) ini_get('magic_quotes_gpc');
   }

   function __toString() {
      return $this->SqlString()->Raw();
   }

   function AsArray() {
      return FALSE;
   }

   function IsArray() {
      return FALSE;
   }

   function Raw() {
      return $this->mrVariable;
   }

   //---- taken from OWAS.org: owasp-php-filters.zip
   function Utf8Decode() {
      return new Sanitizer(strtr($this->mrVariable,
        "???????���������������������������",
        "SOZsozYYuAAAAAAACEEEEIIIIDNOOOOOOUUUUYsaaaaaaaceeeeiiiionoooooouuuuyy"));
   }

   function AddSlashes() {
      if ($this->mGpcsOn)
         return new Sanitizer($this->mrVariable);
      else
         return new Sanitizer(addslashes($this->mrVariable));
   }

   // paranoid sanitization -- only let the alphanumeric set through
   function AlphaNumeric($min = '', $max = '') {
      $string = preg_replace("/[^a-zA-Z0-9]/", "", $this->mrVariable);
      $len = strlen($string);
      if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
         return FALSE;
      return new Sanitizer($string);
   }

   // sanitize a string in prep for passing a single argument to system() (or similar)
   function SystemString($min = '', $max = '') {
      $pattern = '/(;|\||`|>|<|&|^|"|'."\n|\r|'".'|{|}|[|]|\)|\()/i'; // no piping, passing possible environment variables ($),
                              // seperate commands, nested execution, file redirection,
                              // background processing, special commands (backspace, etc.), quotes
                              // newlines, or some other special characters
      $string = preg_replace($pattern, '', $this->mrVariable);
      $string = '"'.preg_replace('/\$/', '\\\$', $string).'"'; //make sure this is only interpretted as ONE argument
      $len = strlen($string);
      if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
         return FALSE;
      return new Sanitizer($string);
   }

   // sanitize a string for SQL input (simple slash out quotes and slashes)
   function SqlString($min = '', $max = '') {
      $string = $this->AddSlashes($this->mrVariable)->Raw(); //gz
      $pattern = "/;/"; // jp
      $replacement = "";
      $len = strlen($string);
      if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
         return FALSE;

      $newString = preg_replace($pattern, $replacement, $string);
      //SysLog::Log('SqlString('.$this->mrVariable.') create sanitizer('.$newString.')', 'sanitizer');
      $result= new Sanitizer($newString);
      //SysLog::Log('SqlString('.$this->mrVariable.') returning: '.print_r($result, true), 'sanitizer');
      return $result;
   }

   // sanitize a string for SQL input (simple slash out quotes and slashes)
   function LdapString($min = '', $max = '') {
      $pattern = '/(\)|\(|\||&)/';
      $len = strlen($this->mrVariable);
      if ((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
         return FALSE;
      return new Sanitizer(preg_replace($pattern, '', $this->mrVariable));
   }


   // sanitize a string for HTML (make sure nothing gets interpretted!)
   function StripHtmlTags() {
      $pattern[0] = '/\&/';
      $pattern[1] = '/</';
      $pattern[2] = "/>/";
      $pattern[3] = '/\n/';
      $pattern[4] = '/"/';
      $pattern[5] = "/'/";
      $pattern[6] = "/%/";
      $pattern[7] = '/\(/';
      $pattern[8] = '/\)/';
      $pattern[9] = '/\+/';
      $pattern[10] = '/-/';
      $replacement[0] = '&amp;';
      $replacement[1] = '&lt;';
      $replacement[2] = '&gt;';
      $replacement[3] = '<br>';
      $replacement[4] = '&quot;';
      $replacement[5] = '&#39;';
      $replacement[6] = '&#37;';
      $replacement[7] = '&#40;';
      $replacement[8] = '&#41;';
      $replacement[9] = '&#43;';
      $replacement[10] = '&#45;';
      return new Sanitizer(preg_replace($pattern, $replacement, $this->mrVariable));
   }

   // make int int!
   function Integer($min = '', $max = '') {
      $int = intval($this->mrVariable);
      if ((($min != '') && ($int < $min)) || (($max != '') && ($int > $max)))
         return FALSE;
      return new Sanitizer($int);
   }

   // make float float!
   function Float($min = '', $max = '') {
      $float = floatval($this->mrVariable);
      if ((($min != '') && ($float < $min)) || (($max != '') && ($float > $max)))
         return FALSE;
      return new Sanitizer($float);
   }

   function IsAlphaNumeric($min = '', $max = '') {
      if ($this->mrVariable != $this->AlphaNumeric($min, $max))
         return FALSE;
      return TRUE;
   }

   function IsInteger($min = '', $max = '') {
      if (!is_numeric($this->mrVariable))
         return FALSE;
      $x = $this->mrVariable + 0;
      if ($x != $this->Integer($min, $max))
         return FALSE;
      return TRUE;
   }

   function IsFloat($min = '', $max = '') {
      if (!is_numeric($this->mrVariable))
         return FALSE;
      $x = $this->mrVariable + 0;
      if ($x != $this->Float($min, $max))
         return FALSE;
      return TRUE;
   }

   function IsHtmlString($min = '', $max = '') {
      if ($this->mrVariable != $this->StripHtmlTags($min, $max))
          return FALSE;
      return TRUE;
   }

   function IsSqlString($min = '', $max = '') {
      if ($this->mrVariable != $this->SqlString($min, $max))
         return FALSE;
      return TRUE;
   }

   function IsLdapString($min = '', $max = '') {
      if ($this->mrVariable != $this->LdapString($min, $max))
          return FALSE;
      return TRUE;
   }

   function IsSystemString($min = '', $max = '') {
      if ($this->mrVariable != $this->SystemString($min, $max))
          return FALSE;
      return TRUE;
   }
   //-----
}
?>