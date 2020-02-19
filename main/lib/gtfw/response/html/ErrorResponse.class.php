<?php
class ErrorResponse extends HtmlResponse {
   // - all pages should "inherit" from this template base
   //   edit this method everytime you develop a new application
   // - overide this method if you want to make a specific page, ex. login page
   function TemplateBase() {
      $this->SetTemplateBasedir(Configuration::Instance()->GetValue('application', 'docroot') . 'main/template/');
      //var_dump(Configuration::Instance()->GetValue('application', 'gtfw_base'));
      $this->SetTemplateFile('document-common.html');

      $this->SetTemplateBasedir(Configuration::Instance()->GetValue('application', 'docroot') . 'main/template/');
      $this->SetTemplateFile('layout-common.html');

      $this->mrTemplate->addGlobalVar('CONFIG_BASEDIR', Configuration::Instance()->GetValue('application', 'basedir'));
      $this->mrTemplate->addGlobalVar('CONFIG_BASEADDRESS', Configuration::Instance()->GetValue('application', 'baseaddress'));
   }

   // this is module specific template
   function TemplateModule() {
      $this->SetTemplateFile('error-common.html');
   }

   function ProcessRequest() {
      return array($_GET['error_info'], print_r(debug_backtrace(), true));
   }

   function ParseTemplate($data = NULL) {
      $this->mrTemplate->addVar('content', 'MSG', $data[0]);
      $this->mrTemplate->addVar('content', 'BCK', $data[1]);
   }
}
?>