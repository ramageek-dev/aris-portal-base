<?php
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/gtfw/sobb/PublicServiceProvider.class.php';

require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/nusoap/class.wsdlcache.php';

/**
* SoapGatewayBase. One WSDL multiple endpoint
* @package SoapGateway
* @author Akhmad Fathonih <toni@gamatechno.com>
* @version 1.0
* @copyright 2006&copy;Gamatechno
*/

class SoapGatewayBase extends SoapResponse {
   // business namespace, mainly for performance reason
   // redefine this property with the name of registered business namespace in this way:
   // var $mRegisteredNamespace = array(
   //      'Kota' => array(array('mod' => 'service_ref_kota',
   //         'sub' => 'RefKotaGateway',
   //         'act' => 'Soap',
   //         'typ' => 'soap'), array('mod' => 'service_ref_propinsi',
   //         'sub' => 'RefPropinsiGateway',
   //         'act' => 'Soap',
   //         'typ' => 'soap')),
   //      'Agama' => array(array('address' => 'http://some.address.com/?wsdl',
   //         'proxy_host' => '',
   //         'proxy_port' => '',
   //         'proxy_user' => '',
   //         'proxy_pass' => '')),
   //      'Menu' => array(array('mod' => 'service_menu', file => 'ComplexPublicService.class.php',
   //         'class' => 'ComplexPublicService'))
   //    );
   // caution:
   //    * each business namespace is an array of gtfw's soapresponse module
   //    * with an array, we can mix some soapresponse in one namespace
   //    * you can fetch remote wsdl using 'address' key in your array
   //    * to support soapgateway1, use 'mod', 'file' & 'class' key and make sure
   //      that your class is declared in module/<mod>/business/<file>
   var $mRegisteredNamespace = array();

   var $mRegisteredFunctions = array();

   // array of exposed service provider
   var $mServiceProviders = array();

   var $mFunctionContainer = array();

   // override wsdl binding style
   protected $mCustomBindingStyle = 'rpc';

   function __construct() {
      $this->configureWsdlEvent();

      // collect via public object
      $this->collectServiceObjects();

      parent::__construct();

      // collect via wsdl
      $this->collectServiceWsdl();
   }

   /**
    * Override original configureWsdl to support binding style overriding via outer-world ($_REQUEST var: wsdl_style)
    */
   function configureWSDL($serviceName,$namespace = false,$endpoint = false,$style='rpc', $transport = 'http://schemas.xmlsoap.org/soap/http', $schemaTargetNamespace = false) {
      if (isset($_REQUEST['binding_style']))
         $this->mCustomBindingStyle = $_REQUEST['binding_style'];

      if (($this->mCustomBindingStyle == 'rpc') || ($this->mCustomBindingStyle == 'document'))
            $style = (string) $this->mCustomBindingStyle;
      parent::configureWSDL($serviceName,$namespace,$endpoint,$style, $transport, $schemaTargetNamespace);
   }

   function configureWsdlEvent() {
      $this->configureWsdl('SoapGatewayBase', false, Configuration::Instance()->GetValue( 'application', 'baseaddress').Dispatcher::Instance()->GetWsdlUrl('soapgateway', 'Gateway', 'Soap', 'soap'), $this->mCustomBindingStyle);
   }

   // internal function
   private function ProcessServiceObjects($module) {
      if (isset($module['file'])) { // local service object
         require_once Configuration::Instance()->GetValue('application', 'docroot') .
            'module/' . $module['mod'] . '/business/' . $module['file'];

         if (class_exists($module['class'])) {
            $service_obj = new $module['class']();
            $this->importServices($service_obj);
         }
      }
   }

   /**
   * register service object here
   */
   function collectServiceObjects() {
      if ($_GET['nspace'] != '' && isset($this->mRegisteredNamespace[(string) $_GET['nspace']])) {
         foreach ($this->mRegisteredNamespace[(string) $_GET['nspace']] as $module) {
            $this->ProcessServiceObjects($module);
         }
      } else { // all business
         foreach ($this->mRegisteredNamespace as $namespace) {
            foreach ($namespace as $module) {
               $this->ProcessServiceObjects($module);
            }
         }
      }
   }

   // internal function
   private function ProcessServiceWsdl($module) {
      if (isset($module['typ'])) { // local service
         list($file_name, $class_name) = Dispatcher::Instance()->GetModule($module['mod'], $module['sub'], $module['act'], $module['typ']);
         if ($file_name !== FALSE) {
            require_once $file_name;

            $obj_soapresponse = new $class_name();
            $this->importWsdlFromSoapResponse($obj_soapresponse);
         }
      } elseif (isset($module['address'])) { // remote service
         $this->importWsdlUrl($module['address'], $module['proxy_host'], $module['proxy_port'],
            $module['proxy_user'], $module['proxy_pass']);
      }
   }

   /**
   * register service object here
   */
   function collectServiceWsdl() {
      if ($_GET['nspace'] != '' && isset($this->mRegisteredNamespace[(string) $_GET['nspace']])) {
         foreach ($this->mRegisteredNamespace[(string) $_GET['nspace']] as $module) {
            $this->ProcessServiceWsdl($module);
         }
      } else { // all business
         foreach ($this->mRegisteredNamespace as $namespace) {
            foreach ($namespace as $module) {
               $this->ProcessServiceWsdl($module);
            }
         }
      }
   }

   protected function importWsdlFromSoapResponse($soapresponse_object) {
      if (!isset($soapresponse_object->wsdl))
         return;

      $wsdl =& $soapresponse_object->wsdl;

      $this->importWsdlObject($wsdl);
   }

   protected function importWsdlObject($wsdl) {
      SysLog::Instance()->log('wsdl namespace (before): '.print_r($this->wsdl->namespaces, true), 'soapgateway');


      if (isset($this->wsdl->namespaces))
         $this->importNamespaces($wsdl->namespaces);
      else
         $this->wsdl->namespaces = $wsdl->namespaces;

      if (isset($this->wsdl->import))
         $this->wsdl->import = $this->wsdl->import + $wsdl->import;
      else
         $this->wsdl->import = $wsdl->import;

      if ($wsdl->namespaces['tns'] != $this->wsdl->namespaces['tns']) { // included wsdl
         $alias = substr($wsdl->namespaces['tns'], strrpos($wsdl->namespaces['tns'], '/') + 1);
         if (empty($alias)) {
            $alias = preg_replace('/\b/g', '_', $wsdl->namespaces['tns']);
         }

         foreach ($wsdl->schemas as $target_namespace => $content) {
            foreach ($content as $key => $obj) {
               $type = $wsdl->namespaces['tns'];
               unset($obj->namespaces['tns']);
               $obj->namespaces[$alias] = $type;
            }
         }

      }
      if (isset($this->wsdl->schemas))
         $this->wsdl->schemas = $this->wsdl->schemas + $wsdl->schemas;
      else
         $this->wsdl->schemas = $wsdl->schemas;

      if (isset($this->wsdl->messages))
         $this->wsdl->messages = $this->wsdl->messages + $wsdl->messages;
      else
         $this->wsdl->messages = $wsdl->messages;

      // convert wsdl binding style to custom binding style. FIXME: should we affetc wsdl doc too, would it be problematic when we are trying to invoke them. Imagine and outsider wsdl! So, we need to see first wether described binding in master doc will affect other binding style within the same doc. Is it okay to use master binding style as doc/lit while having some rpc/enc binding style somewhere within the same doc?
//       foreach($wsdl->bindings as $b_key => $binding)
//          if ($binding['style'] != $this->mCustomBindingStyle) {
//             $wsdl->bindings[$b_key]['style'] = $this->mCustomBindingStyle;
//          }

      if (isset($this->wsdl->bindings))
         $this->wsdl->bindings = $this->wsdl->bindings + $wsdl->bindings;
      else
         $this->wsdl->bindings = $wsdl->bindings;

      if (isset($this->wsdl->ports))
         $this->wsdl->ports = $this->wsdl->ports + $wsdl->ports;
      else
         $this->wsdl->ports = $wsdl->ports;

      SysLog::Instance()->log('wsdl namespace (after): '.print_r($this->wsdl->namespaces, true), 'soapgateway');

      SysLog::Instance()->log('wsdl bindings (after): '.print_r($this->wsdl->bindings, true), 'soapgateway');
   }

   protected function importWsdlUrl($wsdl_url, $proxy_host = '', $proxy_port = '', $proxy_user = '', $proxy_pass = '') {
      SysLog::Instance()->log("importing from $wsdl_url", 'soapgateway');

      // instantiate wsdl cache manager
      $cache_use = (bool) Configuration::Instance()->GetValue('application', 'wsdl_use_cache');
      $cache_path = file_exists(Configuration::Instance()->GetValue('application', 'wsdl_cache_path')) ? Configuration::Instance()->GetValue('application', 'wsdl_cache_path') : Configuration::Instance()->GetValue('application', 'session_save_path');
      // paranoia check, if session_save_path set to NULL
      $cache_path = file_exists($cache_path) ? $cache_path : ini_get('session.save_path');
      //
      $cache_lifetime = Configuration::Instance()->GetValue('application', 'wsdl_cache_lifetime') != '' ? (int) Configuration::Instance()->GetValue('application', 'wsdl_cache_lifetime') : 60 * 60 * 24; // defaults to 1 day

      if ($cache_path != '' && $cache_use) {
         $wsdl_cache = new wsdlcache($cache_path, $cache_lifetime);
      }
      // try to get from cache first...
      if ($wsdl_cache) {
         $wsdl = $wsdl_cache->get($wsdl_url);
      }
      // cache hit? if not, get it from server and put to cache
      if (!$wsdl) {
         $wsdl = new wsdl($wsdl_url, $proxy_host, $proxy_port, $proxy_user, $proxy_pass);
         if ($wsdl_cache && !$wsdl->getError())
            $wsdl_cache->put($wsdl);
      }

      if (is_object($wsdl) && !$wsdl->getError()) {
         // adjustment: re-encode URL, since after importing somewhoe it won't be escaped anymore thus yields in invalid xml
         if (count($wsdl->ports) >= 1) {
            foreach($wsdl->ports as $pName => $attrs) {
               if (preg_match('/(.*)?\?(.*)/', $attrs['location'], $found)) {
                  $main_url = $found[1];
                  $query_str = $found[2];
                  if (!empty($query_str))
                     $main_url .= '?';
               } else {
                  $main_url = $attrs;
                  $query_str = '';
               }
               $wsdl->ports["$pName"]['location'] = $main_url.htmlentities($query_str);
            }
         }
         $this->importWsdlObject($wsdl);
      } else {
         SysLog::Instance()->log("Unable to read wsdl from url: $wsdl_url", 'soapgateway');
      }
   }

   protected function importServices(&$public_obj, $prefix = false) {
      $public_obj->collectServices();
      $services = $public_obj->getServices();

      if (!$prefix)
         $prefix = $public_obj->getServicePrefix();

      if (!empty($services))
         foreach($services as $key=>$value) {
            // override original binding with new binding style and force it to literal when binding style is 'document'
            $value['style'] = $this->mCustomBindingStyle;
            if ($this->mCustomBindingStyle == 'document') {
               $value['use'] = 'literal';
            }
            $this->mRegisteredFunctions[$prefix.'_'.$key] = $value;
            $this->mFunctionContainer[$prefix.'_'.$key] = get_class($public_obj);
         }

      $complexTypes = $public_obj->getComplexTypes();

      if($complexTypes !== false)
         $this->importComplexTypes($complexTypes, $prefix);
   }

   protected function importComplexTypes($complexTypes) {
      foreach($complexTypes as $item)
         $this->wsdl->addComplexType($item[0], $item[1], $item[2], $item[3], $item[4], $item[5], $item[6], $item[7]);
   }

   // overrided method
   // see original code in nusoap.php: soap_server::invoke_method()
   function invoke_method() {
      // debug
      SysLog::Instance()->log('request invocation of: '.$this->methodname.' from: '.$this->methodURI, 'soapgatewaybase');
      $this->debug('in invoke_method, methodname=' . $this->methodname . ' methodURI=' . $this->methodURI . ' SOAPAction=' . $this->SOAPAction);

      if ($this->wsdl) {
         if ($this->opData = $this->wsdl->getOperationData($this->methodname)) {
            $this->debug('in invoke_method, found WSDL operation=' . $this->methodname);
            $this->appendDebug('opData=' . $this->varDump($this->opData));
         } elseif ($this->opData = $this->wsdl->getOperationDataForSoapAction($this->SOAPAction)) {
            // Note: hopefully this case will only be used for doc/lit, since rpc services should have wrapper element
            $this->debug('in invoke_method, found WSDL soapAction=' . $this->SOAPAction . ' for operation=' . $this->opData['name']);
            $this->appendDebug('opData=' . $this->varDump($this->opData));
            $this->methodname = $this->opData['name'];
         } else {
            $this->debug('in invoke_method, no WSDL for operation=' . $this->methodname);
            $this->fault('Client', "Operation '" . $this->methodname . "' is not defined in the WSDL for this service");
            return;
         }
      } else {
         $this->debug('in invoke_method, no WSDL to validate method');
      }

      if (preg_match('/(.*)?\_(.*)/', $this->methodname, $matches)) {
         $prefix = $matches[1];
         $methodname = $matches[2];
         $this->realMethodName = $methodname;
      }

      // does method exist?
      $classInstance = NULL;
      if (isset($this->mFunctionContainer[$this->methodname])) {
         eval("\$classInstance = new {$this->mFunctionContainer[$this->methodname]}();");

         if (method_exists($classInstance, 'providesService')) { // SoapGateway1 support
            $valid_method = $classInstance->providesService($this->realMethodName);
         } else
            $valid_method = method_exists($classInstance, $this->realMethodName);

         if ($invalid_method) {
            $this->debug("in invoke_method, function '$this->methodname' not found!");
            $this->result = 'fault: method not found';
            $this->fault('Client',"method '$this->methodname' not defined in service");
            return;
         }
      } else
         return;

      // evaluate message, getting back parameters
      // verify that request parameters match the method's signature
      if(! $this->verify_method($this->methodname,$this->methodparams)){
         // debug
         $this->debug('ERROR: request not verified against method signature');
         $this->result = 'fault: request failed validation against method signature';
         // return fault
         $this->fault('Client',"Operation '$this->methodname' not defined in service.");
         return;
      }

      // if there are parameters to pass
      $this->debug('in invoke_method, params:');
      $this->appendDebug($this->varDump($this->methodparams));
      $this->debug("in invoke_method, calling '$this->methodname'");
      if (!function_exists('call_user_func_array')) {
         $this->debug('in invoke_method, calling function using eval()');
         $funcCall = "\$this->methodreturn = \$classInstance->{$this->realMethodName}(";
         if ($this->methodparams) {
            foreach ($this->methodparams as $param) {
               if (is_array($param)) {
                  $this->fault('Client', 'NuSOAP does not handle complexType parameters correctly when using eval; call_user_func_array must be available');
                  return;
               }
               $funcCall .= "\"$param\",";
            }
            $funcCall = substr($funcCall, 0, -1);
         }
         $funcCall .= ');';
         $this->debug('in invoke_method, function call: '.$funcCall);
         @eval($funcCall);
      } else {
         $this->debug('in invoke_method, calling function using call_user_func_array()');
         $call_arg = array(&$classInstance, $this->realMethodName);
         $this->methodreturn = call_user_func_array(array(&$classInstance, $this->realMethodName), $this->methodparams);
      }

      $this->debug('in invoke_method, methodreturn:');
      $this->appendDebug($this->varDump($this->methodreturn));
      if (!is_object($this->methodreturn)) {
         $this->debug("in invoke_method, called method {$this->realMethodName}, received $this->methodreturn of type ".gettype($this->methodreturn));
      } else {
         $this->debug("in invoke_method, called method {$this->realMethodName}, received result of type ".gettype($this->methodreturn));
      }
   }

   private function importNamespaces($namespace) {
      foreach($namespace as $alias => $type) {
         if (empty($type))
            continue;
         if (!isset($this->wsdl->namespaces[$alias]))
            $this->wsdl->namespaces[$alias] = $type;
         else
         if (($this->wsdl->namespaces[$alias] != $type)/*  && !empty($this->wsdl->namespaces[$alias])*/) {
            //SysLog::Instance()->log('nsmespace collision on ns='.$alias.' with value1='.$type.' and value2='.$this->wsdl->namespaces[$alias], 'soapgateway');
            $alias = substr($type, strrpos($type, '/')+1);
            if (empty($alias)) {
               $alias = preg_replace('/\b/g', '_', $type);
            }

            $this->wsdl->namespaces[$alias] = $type;
         }
      }
   }
}

?>