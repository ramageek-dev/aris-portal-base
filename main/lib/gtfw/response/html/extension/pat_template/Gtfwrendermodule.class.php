<?php

class patTemplate_Function_Gtfwrendermodule extends patTemplate_Function {

	var $_name = 'Gtfwrendermodule';

	function call($params, $content) {
		//print_r($params);
		//$name = 'name'; // koreksi code tanpa mengubah code aslinya

		$content_repo =& $GLOBALS['content_repo'];
		$component_name =& $GLOBALS['component_name'];

		if (!isset($content_repo))
			$content_repo = array();
		if (!isset($component_name))
			$component_name = array();

		if (!isset($params['type']))
			$params['type'] = null;

		// checking component's name
		if ($params[$name] != '' && isset($component_name[$params[$name]]) &&
				$component_name[$params[$name]] != "{$params['module']} {$params['submodule']} {$params['action']} {$params['type']}")
			return 'Redeclared component name: ' . $params['name'] . " for module {$params['module']}, submodule {$params['submodule']}, action {$params['action']}, and type {$params['type']}";

		if (!isset($params['type']))
			$params['type'] = '';

		$component_name[$params[$name]] = "{$params['module']} {$params['submodule']} {$params['action']} {$params['type']}";

		// this caching mechanism isn't suitable for module that needs to be rendered
		// everytime, such as paging navigation
		if (isset($content_repo[$params['name']][$params['module']][$params['submodule']][$params['action']][$params['type']])) {
			$this_content = $content_repo[$params['name']][$params['module']][$params['submodule']][$params['action']][$params['type']];
		} else {
			// catching component's paramaters
			$parameters = array();
			// pattemplate content style
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
			// http get style (via 'params' attribute)
			if(isset($params['params'])){
				if (trim($params['params']) != '') {
					parse_str($params['params'], $temp);
					foreach ($temp as $k => $v) {
						$parameters[$k] = $v;
					}
				}
			}
			// html tag attribute style
			// ie. not name, module, submodule, action, params
			foreach ($params as $k => $v) {
				if ($k != 'name' && $k != 'module' && $k != 'submodule' &&
						$k != 'action' && $k != 'params') {
					$parameters[$k] = $v;
				}
			}
			// WARNING: this soon will be obsolete
			// modified by Ageng (is it correct??)
			// catch the parameters that was setted for component
			if(isset($GLOBALS['parameters_set'])) {
				foreach($GLOBALS['parameters_set'] as $key => $value) {
					foreach($parameters as $keyparameters => $valueparameters) {
						if($keyparameters == $key)
							$parameters[$keyparameters] = $value;
					}
				}
			}

			ob_start();

			if (Security::Instance()->AllowedToAccess($params['module'], $params['submodule'], $params['action'], 'html')) {
				list($file_path, $class_name) = Dispatcher::Instance()->GetModule($params['module'], $params['submodule'], $params['action'], 'html');
				if (FALSE === $file_path) {
					Dispatcher::Instance()->ModuleNotFound();
				} else {
					require_once Configuration::Instance()->GetValue( 'application', 'gtfw_base') . 'main/lib/pat_template/pat_template.php';
					require_once Configuration::Instance()->GetValue( 'application', 'gtfw_base') . 'main/lib/gtfw/response/html/HtmlResponse.class.php';
					require_once $file_path;
					if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
						eval('$module = new ' . $class_name . '();');
					} else {
						eval('$module = new '. $class_name . '();');
					}
					// give component a name
					$module->mComponentName = $params['name'];
					// display as module
					// modified by Ageng
					// assign component parameters to their property
					$module->mComponentParameters = $parameters;

					$module->Display(TRUE);
				}
			} else {
				Security::Instance()->ModuleAccessDenied();
			}

			$this_content = ob_get_contents();
			 
			// ===== added by roby =====
			// this code created to make "prepared variable" to prevent patTemplate parsing it in included module that call with gtfwrendermodule function
			// because prepared variable will be parsed in main modul as a normal variable
			// to apply, you can put a param in gtfwrendermodule function like example below:
			// <!-- patTemplate:gtfwrendermodule module="module_name" submodule="sub_module" action="view" name="template_name" prepared_var="var1, var2"  / -->
			// multiple variables can be splited by comma as show above
			// and you must change character { and } with [ and ] in included module
			// e.g.: if you want variable {NAME} not to be parsed, you must change it with [NAME] and code below will change it back to {NAME} after prosessing included module
			if (!empty($params['prepared_var'])) {
				$arr_prepared_var = array();
				$arr_search = array();
				$arr_replace = array();

				$arr_prepared_var =  explode(',', $params['prepared_var']);
				foreach ($arr_prepared_var as $v) {
					$v = trim($v);
					$arr_search[] = "[$v]";
					$arr_replace[] = '{'.$v.'}';
				}
				$this_content = str_replace($arr_search, $arr_replace, $this_content);
			}
			// ===== end =====
			 
			$content_repo[$params['name']][$params['module']][$params['submodule']][$params['action']][$params['type']] = $this_content;
			ob_end_clean();
		}

		return $this_content;
	}
}
?>
