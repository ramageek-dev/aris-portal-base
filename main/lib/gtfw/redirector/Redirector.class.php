<?php
class Redirector {
   private static $mRedirected = FALSE;
   private static $mRedirectedTo = NULL;

   private function __construct() {
   }

   public static function RedirectToUrl($location, $replace = FALSE, $code = NULL, $ascomponent = TRUE) {
      if (self::$mRedirected && !$replace)
         return;
      
      /*
       * modified by galih galih@gmail.com
       * add ascomponent paramater when first request from html send ascomponent parameter too
       */
      if (isset($_GET['ascomponent']) && ($ascomponent))
         $location .= '&ascomponent=1';
      
      self::$mRedirected = TRUE;
      self::$mRedirectedTo = $location;
		
      header('Location: ' . $location, $replace, $code);
   }

   public static function RedirectToModule($module, $subModule, $action, $type, $params = '', $replace = FALSE, $code = NULL) {
      if (self::$mRedirected && !$replace)
         return;

      /*
       * modified by galih galih@gmail.com
       * add ascomponent paramater when first request from html send ascomponent parameter too
       */
      if (isset($_GET['ascomponent']))
         $location .= '&ascomponent=1';

      self::$mRedirected = TRUE;
      self::$mRedirectedTo = Dispatcher::Instance()->GetUrl($module, $subModule, $action, $type) .
         '&' . $params;

      header('Location: ' . self::$mRedirectedTo, $replace, $code);
   }
}
?>