<?php
// dependencies: Dispatcher, Configuration, Sanitizer

class CliDispatcher extends Dispatcher {
   private static $mInstance = NULL;

   private function __construct() {

   }

   public function Dispatch($module, $submodule, $action, $type) {
      list($file_path, $class_name) = $this->GetModule($module, $submodule, $action, $type);

      if ($file_path) {
         require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
            'main/lib/gtfw/response/ResponseIntf.intf.php';
         require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
            'main/lib/gtfw/response/cli/CliResponse.class.php';
         require_once $file_path;

         $response = new $class_name();
         $response->Send();
      } else {
         echo 'Module not found!';
      }
   }

   public static function Instance() {
      if (!isset(self::$mInstance))
         self::$mInstance = new CliDispatcher();

      return self::$mInstance;
   }
}
?>