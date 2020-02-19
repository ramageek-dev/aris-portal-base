<?php
function smarty_function_gtfwgeturl($params, &$smarty) {
   $dispt =& $GLOBALS['dispt'];

   $parameters = '';
   /*
   // pattemplate content style
   // warning: value is always trimmed!!
   if (trim($content) != '') {
      $temp = explode("\n", $content);
      foreach ($temp as $k => $v) {
         if (trim($v) != '') {
            list($var, $val) = explode(':=', $v);
            $parameters .= '&' . trim($var) . '=' . urlencode(trim($val));
         }
      }
   }
   */
   // http get style (via 'params' attribute)
   if (trim($params['params']) != '')
      $parameters .= '&' . trim($params['params']);
   // html tag attribute style
   // ie. not name, module, submodule, action, params
   foreach ($params as $k => $v) {
      if ($k != 'module' && $k != 'submodule' && $k != 'sub' &&
          $k != 'action' && $k != 'params' && $k != 'type') {
         $parameters .= '&' . $k . '=' . urlencode($v);
      }
   }

   //$parameters = urlencode($parameters);
   // preferred to be 'submodule'
   // but for compatibility with older version, 'sub' attribute is still supported
   $submodule = (isset($params['sub'])) ? $params['sub'] : '';
   $submodule = (isset($params['submodule'])) ? $params['submodule'] : $submodule;

   $url = $dispt->GetUrl($params['module'], $submodule, $params['action'], $params['type']) . $parameters ;

   return $url;
}
?>