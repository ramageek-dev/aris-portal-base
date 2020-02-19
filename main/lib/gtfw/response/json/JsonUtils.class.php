<?php

/**
   JSON utilities, converts php var to JSON
 */
class JsonUtils {
   static private $mInstance;

   static function Instance() {
      if (!isset(self::$mInstance)) {
         self::$mInstance = new JsonUtils();
      }

      return self::$mInstance;
   }

   private function __construct() {
      if (!(class_exists('Services_JSON') || function_exists('json_encode') ))
         trigger_error('JSON support not available', E_ERROR);
   }

   function VarToJSON($value) {
      //echo "converting: ".$value;
      if (class_exists('Services_JSON')) { // JOSN via PEAR
          $json = new Services_JSON();

         // convert a complex value to JSON notation
         $output = $json->encode($value);
         return $output;
      } else
      if (function_exists('json_encode')) { // json.so
         return json_encode($value);
      }
   }
}
?>