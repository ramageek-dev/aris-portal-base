<?php
/**
 * Format
 *
 * @package
 * @author Dyan Galih <dyan.galih@gmail.com>
 * @modifier Didi Z <didi.zoel@gmail.com>
 * @copyright Copyright (c) 2012 Gamatechno
 * @version 1.0
 * @access public
 */

require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/format/FormatIntf.intf.php';

class Format implements FormatIntf{
	private static $mrInstance;
	
	function dhtmlx($arrData, $fieldId,$totalRows='',$posStart=''){
		if (empty($arrData)){
			$arr['rows']='';
		}else{
			foreach($arrData as $key => $values){
				$arr['total_count'] = ($totalRows <> '') ? $totalRows : '';
				$arr['pos'] = ($posStart <> '') ? $posStart : 0;
				if(isset($fieldId)) $arr['rows'][$key]['id'] = $values[$fieldId];
				@$arr['rows'][$key]['style']=$values['style'];
				$idx = 0;
				foreach ($values as $value){
					//if ($value <> $values[$fieldId])
						$arr['rows'][$key]['data'][$idx] = (empty($value) || $value == null) ? '' : $value;
					//else
						//$arr['rows'][$key]['data'][$idx] = ''; //bypass id
					$idx++;
				}
			}
		}
		return $arr;
	}
	
	static function Instance() {
		if (!isset(self::$mrInstance))
			self::$mrInstance = new Format();
	
		return self::$mrInstance;
	}	
}
?>
