<?php
function smarty_function_gtfwgetconfig($params, &$smarty) {
   if (trim($params['config']) == '' || trim($params['name']) == '')
      return 'Incomplete parameters! Expecting config and name.';

   if (Configuration::Instance()->IsExist($params['config'], $params['name'])) {
      return Configuration::Instance()->GetValue($params['config'],$params['name']);
   } else {
      return 'Non-existent configuration!';
   }
}
?>