<?php
// pattempalte
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/pat_template/pat_template.php';

// FormHelper
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/response/html/FormHelper.class.php';

// FormHelperManager
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/response/html/FormHelperManager.class.php';

/**
 * HtmlResponse abstract
 */
abstract class HtmlResponse implements ResponseIntf {

	var $mrTemplate;
	var $mAsModule = FALSE;
	var $mRedirected = FALSE;
	var $mBodyAttribute = array();
	var $mRawHead = array();
	var $mFormHelpers = array();
	var $mComponentName = NULL;
	var $mComponentParameters = NULL;
	// for parent-child module communication
	var $mrMainHtml;

	function HtmlResponse() {

	}

	function SetTemplateBasedir($baseDir) {
		$this->mrTemplate->setBasedir($baseDir);
	}

	function SetTemplateFile($tmpl) {
		$this->mrTemplate->readTemplatesFromFile($tmpl);
	}

	/**
	 * - all pages should "inherit" from this template base
	 * - edit this method everytime you develop a new application
	 * - overide this method if you want to make a specific page, ex. login page
	 */
	function TemplateBase() {
		$this->SetTemplateBasedir(Configuration::Instance()->GetValue('application', 'docroot') . 'main/template/');
		$this->SetTemplateFile('document-common.html');
		$this->SetTemplateFile('layout-common.html');
	}

	function PrepareFormHelperTemplateBase() {
		$this->SetTemplateBasedir(Configuration::Instance()->GetValue('application', 'docroot') . 'main/template/');
		$this->SetTemplateFile('form-helper-common.html');
	}

	/**
	 * this is module specific template
	 */
	function TemplateModule() {
		// override this method and include module's template to obtain a full html page (not component)
	}

	function SetBodyAttribute($attribute, $value) {
		$this->mBodyAttribute[strtolower($attribute)] = array($attribute, $value);
	}

	function AddRawHead($content) {
		$this->mRawHead[] = $content;
	}

	// this function should be overrided in child class for complex template
	function ParseTemplate($data = NULL) {
		// default handler for simple template
		// $data must be:
		//    array(
		//       'template_name1' => array(
		//          array('var_name1' => 'value1', 'var_name2' => 'value2', ...),
		//          array('var_name1' => 'value1', 'var_name2' => 'value2', ...)
		//       ),
		//       'template_name2' => array(
		//          array('var_name1' => 'value1', 'var_name2' => 'value2', ...),
		//          array('var_name1' => 'value1', 'var_name2' => 'value2', ...)
		//       )
		//    )
		if ($data != NULL) {
			foreach ($data as $template => $row) {
				$this->mrTemplate->addRows($template, $row);
			}
		}
	}


	/**
	 * you must implement this method
	 */
	abstract function ProcessRequest();

	function RedirectTo($url, $replace = FALSE, $code = NULL) {
		Redirector::RedirectToUrl($url, $replace, $code);
		$this->mRedirected = TRUE;
	}

	function Display($asModule = FALSE) {
		
		$urlAsModule = ($_REQUEST['ascomponent']->Integer()->Raw() == 1);
		$this->mAsModule = ($asModule || $urlAsModule);
		// dirty hack, for parent-child module communication
		$this->mrMainHtml = Dispatcher::Instance()->mrMainResponse;

		SysLog::Log('Finally, $this->mAsModule: '.$this->mAsModule, 'htmlresponse');

		$data = $this->ProcessRequest();

		if ($this->mRedirected)
			return;

		// instantiate here, for efficiency reason
		//      $vartemp = & new patTemplate();

		$this->mrTemplate = new patTemplate();

		$this->mrTemplate->useTemplateCache( 'File', array(
				'cacheFolder' => './tmplCache',
				'lifetime'    => 10,
				'filemode'    => 0644
		)
		);

		// if this response is originated from dispatcher
		// ie. it will return the whole document
		// on the other side, it will return part of document (a.k.a module)
		// when $asModule is set to TRUE
		if (!$this->mAsModule) {
			$this->TemplateBase();
		} else {
			// use form helper
			SysLog::Log('Preparing FormHelper template', 'formhelper');
			$this->PrepareFormHelperTemplateBase();
		}

		$this->TemplateModule();
		$this->ParseTemplate($data);

		// prepare FormHelperJs

		$fhm = new FormHelperManager($this->mFormHelpers);
		if ($this->mAsModule)
			$this->mrTemplate->addVar('form-helper-common', 'FORM_HELPER', $fhm->GetFormHelperManagerJs(false));

		// set body extra, i.e. onload, onclick, etc
		if (!$this->mAsModule) {
			if ($this->mrTemplate->exists('document')) {
				if (!empty($this->mBodyAttribute)) {
					$body_extra = '';
					foreach ($this->mBodyAttribute as $attribute => $value) {
						$body_extra .= ' ' . $value[0] . '=' . $value[1];
					}
					$this->mrTemplate->addVar('document', 'BODY_ATTRIBUTE', $body_extra); ///TODO: onload attribute will clash with formHelper, so it's better to have special treatment for onload here
				}

				if (!empty($this->mRawHead)) {
					$raw_head = '';
					foreach ($this->mRawHead as $content) {
						$raw_head .= $content;
					}
					$this->mrTemplate->addVar('document', 'RAW_HEAD', $raw_head);
				}

				$this->mrTemplate->addVar('document', 'RAW_HEAD', $fhm->GetFormHelperManagerJs());
			}
		}
		if (Configuration::Instance()->GetValue('application', 'url_friendly')) {
			$this->mrTemplate->addVar('document', 'RAW_BASEURL', Configuration::Instance()->GetValue('application', 'basedir'));
			$this->mrTemplate->addVar('content', 'RAW_BASEURL', Configuration::Instance()->GetValue('application', 'basedir'));
		}

		$delButton = Security::Instance()->ModuleDenied(Dispatcher::Instance()->mModule);

		$script="<script>if(window.ButtonAccess){ var ba = new ButtonAccess(".json_encode($delButton)."); ba.removeButton();}</script>";

		$dbMsg = SysLog::Instance()->getAllError();
		
		$logMessage = "
		<script>
			message=".json_encode($dbMsg).";
			
			for(var msg in message){
				console.log(message[msg]);
			}
		</script>";
		
		$cleanLog="";
		
		if(empty($dbMsg)){
			$cleanLog = "
			<script>
				//if(console.clear != undefined)
				//	console.clear();
				
				//if(window.clear != undefined)
				//		window.clear();
			</script>";
		}

		ob_start();
		$this->mrTemplate->displayParsedTemplate();
		$content = ob_get_contents();
		ob_end_clean();
		
#		$doc = new DOMDocument;
#		
#		$doc->loadHTML($content);
#		
#		$items = $doc->getElementsByTagName("a");
#		print_r($items->item(0)->getAttribute("href"));
#		exit;

		echo $content.$script.$logMessage.$cleanLog;
	}

	function registerFormHelper($objFormHelper) {
		SysLog::Log('new formhelper registered: '.$objFormHelper->mFormName, 'formhelper');
		$this->mFormHelpers[] = $objFormHelper;
	}

	function &GetHandler() {
		return $this;
	}

	function Send() {
		$this->Display();
	}
}
?>
