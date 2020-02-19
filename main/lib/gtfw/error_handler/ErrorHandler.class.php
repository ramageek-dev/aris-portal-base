<?php
class ErrorHandler {
   private static $mrInstance = NULL;

   const Error = E_USER_ERROR;
   const Warning = E_USER_WARNING;
   const Notice = E_USER_NOTICE;

   private $mErrorType = array(ErrorHandler::Error => 'Error',
      ErrorHandler::Warning => 'Warning', ErrorHandler::Notice => 'Notice');

   public function __construct() {

   }

   public function Initialize() {
      //set_error_handler(array($this, 'HandleError'), error_reporting());
      set_error_handler(array($this, 'HandleError'), ErrorHandler::Error |
         ErrorHandler::Warning | ErrorHandler::Notice);
   }

   public function HandleError($errNo, $errStr, $errFile, $errLine, $errContext) {
      if (!isset($_REQUEST['typ']))
         $_REQUEST['typ'] = 'html';

      $debug_str = '<ul>';
      foreach (debug_backtrace() as $debug) {
         if ($debug['class'] != '')
            $debug_str = '<li>' . $debug['file'] . ' (' . $debug['line'] . '): ' .
               $debug['class'] . '::' . $debug['function'] . '()</li>' . $debug_str;
         else
            $debug_str = '<li>' . $debug['file'] . ' (' . $debug['line'] . '): ' .
               $debug['function'] . '()</li>' . $debug_str;
      }
      $debug_str .= '</ul>';

      $errType = $this->mErrorType[$errNo];

      // adding error info into $GLOBALS
      $GLOBALS['error_info'] = array('errtype' => $errType, 'errstr' => $errStr,
         'errfile' => $errFile, 'errline' => $errLine, 'errcontext' => $errContext,
         'debugstr' => $debug_str);

      require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/response/ResponseIntf.class.php';
      require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/response/' .
         $_REQUEST['typ'] . '/' . ucfirst($_REQUEST['typ']) . 'Response.class.php';
      require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/response/' .
         $_REQUEST['typ'] . '/ErrorResponse.class.php';

      $error_response = new ErrorResponse();
      $error_response->Send();
      die();
   }

   public static function &Instance() {
      if (self::$mrInstance == NULL) {
         $class_name = __CLASS__;
         self::$mrInstance = new $class_name();
      }

      return self::$mrInstance;
   }
}

ErrorHandler::Instance()->Initialize();
?>