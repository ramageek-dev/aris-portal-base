<?php
function smarty_function_gtfwrendermodule($params, &$smarty) {
   $cfg =& $GLOBALS['cfg'];
   $sec =& $GLOBALS['sec'];
   $dispt =& $GLOBALS['dispt'];
   $content_repo =& $GLOBALS['content_repo'];
   $COMPONENT_PARAMETERS =& $GLOBALS['COMPONENT_PARAMETERS'];
   $component_name =& $GLOBALS['component_name'];

   if (!isset($content_repo))
     $content_repo = array();
   if (!isset($component_name))
     $component_name = array();
   if (!isset($COMPONENT_PARAMETERS))
     $COMPONENT_PARAMETERS = array();

   // this caching mechanism isn't suitable for module that needs to be rendered
   // everytime, such as paging navigation
   if (isset($content_repo[$params['name']][$params['module']][$params['submodule']][$params['action']][$params['type']])) {
      $this_content = $content_repo[$params['name']][$params['module']][$params['submodule']][$params['action']][$params['type']];
   } else {
      // catching component's paramaters
      $parameters = array();
      /*
      // pattemplate content style - "not" supported in smarty
      // warning: value is always trimmed!!
      if (trim($content) != '') {
         $temp = explode("\n", $content);
         foreach ($temp as $k => $v) {
            if (trim($v) != '') {
               list($var, $val) = explode(':=', $v);
               $parameters[$var] = trim($val);
            }
         }
      }
      */
      // http get style (via 'params' attribute)
      if (trim($params['params']) != '') {
         parse_str($params['params'], $temp);
         foreach ($temp as $k => $v) {
            $parameters[$k] = $v;
         }
      }
      // html tag attribute style
      // ie. not name, module, submodule, action, params
      foreach ($params as $k => $v) {
         if ($k != 'name' && $k != 'module' && $k != 'submodule' &&
             $k != 'action' && $k != 'type' && $k != 'params') {
            $parameters[$k] = $v;
         }
      }
      // making global
      $COMPONENT_PARAMETERS = array_merge($COMPONENT_PARAMETERS, $parameters);

      ob_start();

      if ($sec->AllowedToAccess($params['module'], $params['submodule'], $params['action'], 'smarty')) {
         list($file_path, $class_name) = $dispt->GetModule($params['module'], $params['submodule'], $params['action'], 'smarty');
         if (FALSE === $file_path) {
            $dispt->ModuleNotFound();
         } else {
            require_once SMARTY_DIR . 'Smarty.class.php';
            require_once $cfg->mApplication['docroot'] . 'main/lib/gtfw/response/smarty/SmartyResponse.class.php';
            require_once $file_path;
            eval('$module =& new '. $class_name . '();');
            // dirty hack
            $module->template_dir = $cfg->mApplication['docroot'] . 'module/' . $params['module'] . '/template';
            // display as module
            $module->Display(TRUE);
         }
      } else {
         $sec->ModuleAccessDenied();
      }

      $this_content = ob_get_contents();
      $content_repo[$params['name']][$params['module']][$params['submodule']][$params['action']][$params['type']] = $this_content;
      ob_end_clean();
   }

   return $this_content;
}
?>