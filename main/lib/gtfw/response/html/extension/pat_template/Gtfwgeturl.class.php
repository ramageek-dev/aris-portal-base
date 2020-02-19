<?php
class patTemplate_Function_Gtfwgeturl extends patTemplate_Function {
   var $_name = 'Gtfwgeturl';

   function call($params, $content) {
      $parameters = '';
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
      // http get style (via 'params' attribute)
      if (isset($params['params']) && trim($params['params']) != '')
         $parameters .= '&' . trim($params['params']);
      // html tag attribute style
      // ie. not name, module, submodule, action, params
      foreach ($params as $k => $v) {
         if ($k != 'module' && $k != 'submodule' && $k != 'sub' &&
             $k != 'action' && $k != 'params' && $k != 'type' &&
             $k != 'htmlentities') {
            $parameters .= '&' . $k . '=' . urlencode($v);
         }
      }

      //$parameters = urlencode($parameters);
      // preferred to be 'submodule'
      // but for compatibility with older version, 'sub' attribute is still supported
      $submodule = (isset($params['sub'])) ? $params['sub'] : '';
      $submodule = (isset($params['submodule'])) ? $params['submodule'] : $submodule;
      $htmlentities = (isset($params['htmlentities'])) ? $params['htmlentities'] : TRUE;
      if ($htmlentities !== TRUE) {
         if (trim(strtolower($htmlentities)) == 'yes') {
            $htmlentities = TRUE;
         } elseif (trim(strtolower($htmlentities)) == 'no') {
            $htmlentities = FALSE;
         } else {
            $htmlentities = TRUE;
         }
      }

      // always $htmlEntityEncoded, since we want to embed url(s) in an XHTML document
      // but now isn't always $htmlEntityEncoded, since we can embed it in JavaScript :D
      // use htmlentities="yes|no", defaults to "yes"
      $url = Dispatcher::Instance()->GetUrl($params['module'], $submodule, $params['action'], $params['type'], $htmlentities) . htmlentities($parameters);

      if ($htmlentities) {
         $url .= htmlentities($parameters);
      } else {
         $url .= $parameters;
      }

      return $url;
   }
}
?>