<?php
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/nusoap/class.wsdlcache.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/gtfw/response/ResponseIntf.intf.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/gtfw/response/soap/SoapResponse.class.php';

require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/nusoap/class.soap_transport_http.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') .
   'main/lib/nusoap/class.soap_server.php';

// use NuSOAP!!
class WsdlGenerator extends soap_server {
   private static $mrInstance;

   private $mUseWsdlCache;
   private $mWsdlCachePath;
   private $mWsdlCacheLifetime;

   private function __construct() {
      parent::soap_server();
      parent::__construct();

      // instantiate wsdl cache manager
      $this->mUseWsdlCache = (bool) Configuration::Instance()->GetValue('application', 'wsdl_use_cache');
      $this->mWsdlCachePath = file_exists(Configuration::Instance()->GetValue('application', 'wsdl_cache_path')) ? Configuration::Instance()->GetValue('application', 'wsdl_cache_path') : Configuration::Instance()->GetTempDir();
      $this->mWsdlCacheLifetime = Configuration::Instance()->GetValue('application', 'wsdl_cache_lifetime') != '' ? (int) Configuration::Instance()->GetValue('application', 'wsdl_cache_lifetime') : 60 * 60 * 24; // defaults to 1 day

      $this->configureWsdl('WsdlPortal', FALSE,
         Configuration::Instance()->GetValue('application', 'baseaddress') .
         Configuration::Instance()->GetValue('application', 'basedir') . 'wsdl.php?getlist');

      // always registering default service
      $this->RegisterDefaultService();
   }

   private function RegisterServiceByWsdlObject($wsdlObj = NULL) {
      if (!$wsdlObj)
         return FALSE;

      // taken from SoapGatewayBase.class.php
      // adjustment: re-encode URL, since after importing somewhoe it won't be escaped anymore thus yields in invalid xml
      if (count($wsdlObj->ports) >= 1) {
         foreach($wsdlObj->ports as $pName => $attrs) {
            if (preg_match('/(.*)?\?(.*)/', $attrs['location'], $found)) {
               $main_url = $found[1];
               $query_str = $found[2];
               if (!empty($query_str))
                  $main_url .= '?';
            } else {
               $main_url = $attrs;
               $query_str = '';
            }
            $wsdlObj->ports["$pName"]['location'] = $main_url . htmlentities($query_str);
         }
      }

      // might be an ugly hack
      // this will get rid all unqualified elements & attributes
      if (count($wsdlObj->schemas) > 0) {
         foreach ($wsdlObj->schemas as $ns => $content) {
            foreach ($content as $k => $obj) {
               if ($obj->schemaInfo['elementFormDefault'] == 'unqualified')
                  $obj->schemaInfo['elementFormDefault'] == $obj->schemaInfo['targetNamespace'];
               if ($obj->schemaInfo['attributeFormDefault'] == 'unqualified')
                  $obj->schemaInfo['attributeFormDefault'] == $obj->schemaInfo['targetNamespace'];
               foreach ($obj->complexTypes as $type_name => $type) {
                  if (count($type['elements']) > 0)
                     foreach ($type['elements'] as $element_name => $element)
                        if (isset($element['form']) && $element['form'] == 'unqualified')
                           unset($obj->complexTypes[$type_name]['elements'][$element_name]['form']);
                  if (count($type['attrs']) > 0)
                     foreach ($type['attrs'] as $attr_name => $attr)
                        if (isset($attr['form']) && $attr['form'] == 'unqualified')
                           unset($obj->complexTypes[$type_name]['attrs'][$attr_name]['form']);
               }

               if (count($obj->elements) > 0)
                  foreach ($obj->elements as $element_name => $element)
                     if (isset($element['form']) && $element['form'] == 'unqualified')
                        unset($obj->elements[$element_name]['form']);
            }
         }
      }

      // end adjustment

      if (isset($this->wsdl->namespaces)){
         foreach($wsdlObj->namespaces as $alias => $type) {
            if (empty($type))
               continue;
            if (!isset($this->wsdl->namespaces[$alias]))
               $this->wsdl->namespaces[$alias] = $type;
            else
            if (($this->wsdl->namespaces[$alias] != $type)) {
               $alias = substr($type, strrpos($type, '/')+1);
               if (empty($alias)) {
                  $alias = preg_replace('/\b/g', '_', $type);
               }

               $this->wsdl->namespaces[$alias] = $type;
            }
         }
      } else {
         $this->wsdl->namespaces = $wsdlObj->namespaces;
      }

      if (isset($this->wsdl->import)) {
         $this->wsdl->import = $this->wsdl->import + $wsdlObj->import;
      } else {
         $this->wsdl->import = $wsdlObj->import;
      }

      if ($wsdlObj->namespaces['tns'] != $this->wsdl->namespaces['tns']) { // included wsdl
         $alias = substr($wsdlObj->namespaces['tns'], strrpos($wsdlObj->namespaces['tns'], '/') + 1);
         if (empty($alias)) {
            $alias = preg_replace('/\b/g', '_', $wsdlObj->namespaces['tns']);
         }

         foreach ($wsdlObj->schemas as $target_namespace => $content) {
            foreach ($content as $key => $obj) {
               $type = $wsdlObj->namespaces['tns'];
               unset($obj->namespaces['tns']);
               $obj->namespaces[$alias] = $type;
            }
         }

      }

      if (isset($this->wsdl->schemas)) {
         $this->wsdl->schemas = $this->wsdl->schemas + $wsdlObj->schemas;
      } else {
         $this->wsdl->schemas = $wsdlObj->schemas;
      }

      if (isset($this->wsdl->messages)) {
         $this->wsdl->messages = $this->wsdl->messages + $wsdlObj->messages;
      } else {
         $this->wsdl->messages = $wsdlObj->messages;
      }

      if (isset($this->wsdl->bindings)) {
         $this->wsdl->bindings = $this->wsdl->bindings + $wsdlObj->bindings;
      } else {
         $this->wsdl->bindings = $wsdlObj->bindings;
      }

      if (isset($this->wsdl->ports)) {
         $this->wsdl->ports = $this->wsdl->ports + $wsdlObj->ports;
      } else {
         $this->wsdl->ports = $wsdlObj->ports;
      }
   }

   private function RegisterServiceBySoapClass($wsdlConfig = NULL) {
      if (!$wsdlConfig)
         return FALSE;

      list($file_name, $class_name) = Dispatcher::Instance()->GetModule($wsdlConfig['module'],
         $wsdlConfig['submodule'], $wsdlConfig['action'], $wsdlConfig['type']);
      if ($file_name !== FALSE && !class_exists($class_name, FALSE)) {
         require_once $file_name;

         eval('$service_description = ' . $class_name . '::$mServiceDescriptions;');
         if (!empty($service_description)) {
            $soap_obj = new soap_server();
            eval('$service_binding_style = ' . $class_name . '::$mServiceBindingStyle;');
            $soap_obj->configureWSDL($class_name, FALSE,
               Configuration::Instance()->GetValue('application', 'baseaddress') .
               Dispatcher::Instance()->GetUrl($wsdlConfig['module'], $wsdlConfig['submodule'],
               $wsdlConfig['action'], $wsdlConfig['type']), $service_binding_style);

            // register functions to be exposed as service
            if (isset($service_description['service']) && is_array($service_description['service'])) {
               $class_methods = get_class_methods($class_name);
               foreach ($service_description['service'] as $func_name => $params) {
                  // skip undeclared functions
                  if (!in_array('Service' . $func_name, $class_methods)) {
                     SysLog::Instance()->log('Undeclared WS operation detected! Operation name of ' . $func_name .
                        ' has not been implemented', 'wsdlgenerator');
                     continue;
                  }
                  if (is_array($params) && $params != NULL) {
                     $soap_obj->register($func_name, $params['in'], $params['out'], $params['namespace'],
                        $params['soapaction'], $params['style'], $params['use'], $params['documentation'],
                        $params['encodingStyle']);
                  } else {
                     $soap_obj->register($func_name);
                  }
               }
            }
            // register types used in services
            if (isset($service_description['type']) && is_array($service_description['type'])) {
               foreach ($service_description['type'] as $type_name => $params) {
                  if (is_array($params) && $params != NULL) {
                     if ($params['typeClass'] == 'complexType' || $params['typeClass'] == 'attribute') {
                        $soap_obj->wsdl->addComplexType($type_name, $params['typeClass'],
                           $params['phpType'], $params['compositor'], $params['restrictionBase'],
                           $params['elements'], $params['attrs'], $params['arrayType']);
                     } else if ($params['typeClass'] == 'simpleType') {
                        // always scalar
                        $soap_obj->wsdl->addSimpleType($type_name, $params['restrictionBase'],
                           $params['typeClass'], 'scalar', $params['enumeration']);
                     }
                  }
               }
            }

            $this->RegisterServiceByWsdlObject($soap_obj->wsdl);
         }
      }
   }

   private function RegisterServiceByWsdlConfig($wsdlConfig = NULL) {
      if (!$wsdlConfig)
         return FALSE;

      if ($wsdlConfig['location'] == 'remote') {
         if ($this->mUseWsdlCache) {
            $wsdl_cache = new wsdlcache($this->mWsdlCachePath, $this->mWsdlCacheLifetime);
         }
         // try to get from cache first...
         if ($wsdl_cache) {
            $wsdl = $wsdl_cache->get($wsdlConfig['address']);
         }
         // cache hit? if not, get it from server and put to cache
         if (!$wsdl) {
            $wsdl = new wsdl($wsdlConfig['address'],
               $wsdlConfig['proxy_host'], $wsdlConfig['proxy_port'],
               $wsdlConfig['proxy_user'], $wsdlConfig['proxy_pass']);
            $error = $wsdl->getError();
            if ($error) {
               SysLog::Log('An error occured when fecthing wsdl from ' .
                  $wsdlConfig['address'] . ': ' . $error, 'wsdlgenerator');
            }
            if ($wsdl_cache && !$error)
               $wsdl_cache->put($wsdl);
         }

         if (!$error) {
            $this->RegisterServiceByWsdlObject($wsdl);
         }
      } elseif ($wsdlConfig['location'] == 'local') {
         $this->RegisterServiceBySoapClass($wsdlConfig);
      }
   }

   private function RegisterDefaultService() {
      $this->wsdl->addComplexType('tlist', 'complexType',
         'array', '', 'SOAP-ENC:Array', array(),
         array(array('ref' => 'SOAP-ENC:arrayType', 'wsdl:arrayType' => 'xsd:string[]')));

      $this->register('WsdlGenerator..GetNamespaces', array(), array('result' => 'tns:tlist'),
         'wsdlportal', 'wsdlportal', 'rpc', FALSE, 'Get all available namespaces',
         'utf8');
   }

   function GetList() {
      global $HTTP_RAW_POST_DATA;

      $this->service($HTTP_RAW_POST_DATA);
   }

   function GenerateWsdl($wsdlConfig = NULL) {
      if ($wsdlConfig){
         foreach ($wsdlConfig as $wsdl_provider) {
            $this->RegisterServiceByWsdlConfig($wsdl_provider);
         }
      }

      global $HTTP_SERVER_VARS, $HTTP_RAW_POST_DATA;

      // hack: always returning wsdl
      if (!isset($_GET['html'])) {
         $_SERVER['QUERY_STRING'] .= '&wsdl';
         $HTTP_SERVER_VARS['QUERY_STRING'] .= '&wsdl';
      }

      $this->service($HTTP_RAW_POST_DATA);
   }

   static function GetNamespaces() {
      $wsdl_config = Configuration::Instance()->GetAllValue('wsdl');
      return array_keys($wsdl_config);
   }

   static function Instance() {
      if (!isset(self::$mrInstance))
         self::$mrInstance = new WsdlGenerator();

      return self::$mrInstance;
   }
}

?>