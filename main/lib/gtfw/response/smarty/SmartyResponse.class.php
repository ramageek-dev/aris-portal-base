<?php
class SmartyResponse extends Smarty implements ResponseIntf {
   var $mrDispatcher;
   var $mrSecurity;
   var $mrSession;

   var $mAsModule = FALSE;
   var $mRedirected = FALSE;
   var $mBodyAttribute = array();
   var $mMainTemplate;
   var $mContentTemplate;

   // for parent-child module communication
   var $mrMainHtml;

   function SmartyResponse() {
      $this->mrDispatcher =& $GLOBALS['dispt'];
      $this->mrSecurity =& $GLOBALS['sec'];
      $this->mrSession =& $GLOBALS['sess'];

      Configuration::Instance()->Load('smarty.conf.php');

      $this->template_dir = Configuration::Instance()->GetValue( 'application', 'docroot') . 'module/' .
         $this->mrDispatcher->mModule . '/template';
      $this->compile_dir = Configuration::Instance()->GetValue( 'smarty', 'templates_c');
      $this->cache_dir = Configuration::Instance()->GetValue( 'smarty', 'cache');
      $this->config_dir = Configuration::Instance()->GetValue( 'smarty', 'configs');

      // this is the main template
      $this->mMainTemplate = 'file:' . Configuration::Instance()->GetValue( 'application', 'docroot') .
         'main/template/index.tmpl';
   }

   function SetTemplateFile($tmpl) {
      $this->mContentTemplate = $tmpl;
   }

   // this is module specific template
   function TemplateModule() {
      echo 'HtmlResponse->TemplateModule(): This function must be overrided!';
   }

   function SetBodyAttribute($attribute, $value) {
      $this->mrMainHtml->mBodyAttribute[strtolower($attribute)] = array($attribute, $value);
   }

   // this function should be overrided in child class
   function ParseTemplate($data = NULL) {
      echo 'HtmlResponse->ParseTemplate(): This function must be overrided!';
   }

   // this function should be overrided in child class
   function ProcessRequest() {
      echo 'HtmlResponse->ProcessRequest(): This function must be overrided!';
      return NULL;
   }

   function RedirectTo($url) {
      if ($this->mAsModule)
         return;
      header('Location: ' . $url);
      $this->mRedirected = TRUE;
   }

   function Display($asModule = FALSE) {
      $this->mAsModule = $asModule;
      // dirty hack, for parent-child module communication
      $this->mrMainHtml =& $this->mrDispatcher->mrMainResponse;
      //

      $data = $this->ProcessRequest();

      if ($this->mRedirected)
         return;

      $this->ParseTemplate($data);
      $this->TemplateModule();

      // if this response is originated from dispatcher
      // ie. it will return the whole document
      // on the other side, it will return part of document (a.k.a module)
      // when $asModule is set to TRUE
      if (!$asModule) {
         // set body extra, i.e. onload, onclick, etc
         if (!empty($this->mBodyAttribute)) {
            $body_extra = '';
            foreach ($this->mBodyAttribute as $attribute => $value) {
               $body_extra .= ' ' . $value[0] . '=' . $value[1];
            }
            $this->assign('BODY_ATTRIBUTE', $body_extra);
         }
         $content = $this->fetch($this->mContentTemplate);
         $this->assign('CONTENT', $content);
         $content = $this->fetch($this->mMainTemplate);
         echo $content;
         // some how, i can't do this :(
         //$this->display($this->mMainTemplate);
      } else {
         $content = $this->fetch($this->mContentTemplate);
         echo $content;
      }
   }

   function &GetHandler() {
      return $this;
   }

   function Send() {
      $this->Display();
   }
}
?>