<?php
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.nusoap_base.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.soap_val.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.soap_parser.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.soap_fault.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.xmlschema.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.soap_transport_http.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.wsdl.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/response/soap/SoapResponseDummy.class.php';

/**
 * Soap Response
 */
class PhpResponse extends SoapServer implements ResponseIntf {
   // using wsdl file
   protected $mWsdlFile = FALSE;

   // to support SoapGatewayBase
   // --------------------------
   // redefine this property with the name of registered functions
   // in the form of:
   // array(
   //    'FunctionName1' => array(
   //          'in' => array(),
   //          'out' => array(),
   //          'namespace' = false,
   //          'soapaction' => false,
   //          'style' => false,
   //          'use' => false,
   //          'documentation' => '',
   //          'encodingStyle' => ''
   //       ),
   //    'FunctionName2' => NULL,
   //    ...
   // )
   // set value to NULL if you want to set default or use the default value as in the example above
   // registered functions must exist in inherited class
   // add a 'SoapFunc' prefix in every method you want to register, but ommit it when you
   // call from web service client
   var $mRegisteredFunctions = array();
   //////

   // this static propety is for service description, it contains registered functions
   // exposed as service and registered types used by the services
   // it's formed like this:
   // array(
   //    'service' => array(
   //       'FunctionName1' => array(
   //          'in' => array(),
   //          'out' => array(),
   //          'namespace' = false,
   //          'soapaction' => false,
   //          'style' => false,
   //          'use' => false,
   //          'documentation' => '',
   //          'encodingStyle' => ''
   //       ),
   //       'FunctionName2' => NULL,
   //       ...
   //    ),
   //    'type' => array(
   //       'TypeName1' => array(
   //          'typeClass' => 'complexType', // 'complexType' | 'simpleType' | 'attribute'
   //          'phpType' => 'array', // 'array' | 'struct' | 'scalar' // always scalar when simpleType is used
   //          'compositor' => '', // 'all' | 'sequence' | 'choice'
   //          'restrictionBase' => '', // ex.: 'http://schemas.xmlsoap.org/soap/encoding/:Array'
   //          'elements' => array(), // ex.: array('name' => array('name' => '', 'type' => ''))
   //          'attrs' => array(), // ex.: array(array('ref' => 'SOAP-ENC:arrayType', 'wsdl:arrayType' => 'xsd:string[]'))
   //          'arrayType' => '', // ex.: 'xsd:string'
   //          'enumeration' => array() // used only for simpleType
   //       ),
   //       ...
   //    )
   // )
   // set value to NULL if you want to set default or use the default value for services as in the example above
   // registered functions must exist in inherited class
   // add a 'Service' prefix in every method you want to register, but ommit it when you
   // call from web service client
   // note:
   //   by making this property as static, we could have some advantages in the future
   //   we don't have to instantiate this class just to obtain service description
   //   SoapResponse::$mServiceDescriptions is just a "sample", this won't be used
   //   we use the one declared in the inherited class instead
   public static $mServiceDescriptions = NULL;

   public static $mServiceBindingStyle = 'document';

   // supporting wsdl gateway a.k.a NuSOAP like WSDL generator
   public $wsdl;
   public $operations;

   // redefine this property if you want to set debug mode
   protected $mDebugMode = FALSE;

   function __construct() {
      // to support SoapGatewayBase
      // --------------------------
      if (!$this->mWsdlFile && !empty($this->mRegisteredFunctions)) {
         foreach ($this->mRegisteredFunctions as $func_name => $params) {
            if (is_array($params) && $params != NULL) {
               $this->Register($func_name, $params['in'], $params['out'], $params['namespace'],
                  $params['soapaction'], $params['style'], $params['use'], $params['documentation'],
                  $params['encodingStyle']);
            } else {
               $this->Register($func_name);
            }
         }
      }
      ///////////

      if (!$this->mWsdlFile && empty($this->mRegisteredFunctions)) {
         // doing magic here... avrakedavra!
         // $mServiceDescriptions is a static property, so it won't be inherited
         // as the result, we must do some tricks here. first we find what class is
         // being instantiated (from the dispatcher, of course. mmm.. no, no, use
         // get_class instead. it's definitly more efficient). then we use that
         // information to obtain the actual $mServiceDescriptions
         // see note about $mServiceDescriptions above for another information
         $class_name = get_class($this);
         eval('$service_description = ' . $class_name . '::$mServiceDescriptions;');
         if (!empty($service_description)) {
            // get default binding style
            eval('$default_style = ' . $class_name . '::$mServiceBindingStyle;');
            // setting up wsdl
            $this->ConfigureWsdl($class_name, FALSE, Configuration::Instance()->GetValue('application', 'baseaddress') .
               Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule,
               Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType, TRUE), $default_style);

            // register functions to be exposed as service
            if (isset($service_description['service']) && is_array($service_description['service'])) {
               foreach ($service_description['service'] as $func_name => $params) {
                  // skip undeclared fuctions
                  if (!method_exists($this, 'Service' . $func_name))
                     continue;
                  if (is_array($params) && $params != NULL) {
                     $this->Register($func_name, $params['in'], $params['out'], $params['namespace'],
                        $params['soapaction'], $params['style'], $params['use'], $params['documentation'],
                        $params['encodingStyle']);
                  } else {
                     $this->Register($func_name);
                  }
               }
            }
            // register types used in services
            if (isset($service_description['type']) && is_array($service_description['type'])) {
               foreach ($service_description['type'] as $type_name => $params) {
                  if (is_array($params) && $params != NULL) {
                     if ($params['typeClass'] == 'complexType' || $params['typeClass'] == 'attribute') {
                        $this->wsdl->addComplexType($type_name, $params['typeClass'],
                           $params['phpType'], $params['compositor'], $params['restrictionBase'],
                           $params['elements'], $params['attrs'], $params['arrayType']);
                     } else if ($params['typeClass'] == 'simpleType') {
                        // always scalar
                        $this->wsdl->addSimpleType($type_name, $params['restrictionBase'],
                           $params['typeClass'], 'scalar', $params['enumeration']);
                     }
                  }
               }
            }
         }
      }

      $this->mWsdlFile = NULL;
      $soap_server_options = array();
      if (!$this->mWsdlFile && empty($this->mRegisteredFunctions) && empty($service_description)) {
         $soap_server_options['uri'] = Configuration::Instance()->GetValue('application', 'baseaddress') .
            Configuration::Instance()->GetValue('application', 'basedir');
      } else {
         // generate wsdl
         if ($this->wsdl) {
            $this->mWsdlFile = Configuration::Instance()->GetTempDir() . '/php_wsdl-' .
               md5(Configuration::Instance()->GetValue('application', 'baseaddress') .
               Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule,
               Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType, TRUE));

            $wsdl_string = $this->wsdl->serialize($this->mDebugMode);
            $fp = fopen($this->mWsdlFile, 'w');
            fwrite($fp, $wsdl_string);
            fclose($fp);
         }
      }

      parent::__construct($this->mWsdlFile, $soap_server_options);
      $this->setClass('SoapResponseDummy', $this);
   }

   protected function ConfigureWsdl($serviceName,$namespace = false,$endpoint = false,$style='rpc', $transport = 'http://schemas.xmlsoap.org/soap/http', $schemaTargetNamespace = false) {
      global $HTTP_SERVER_VARS;

      if (isset($_SERVER)) {
         $SERVER_NAME = $_SERVER['SERVER_NAME'];
         $SERVER_PORT = $_SERVER['SERVER_PORT'];
         $SCRIPT_NAME = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
         $HTTPS = $_SERVER['HTTPS'];
      } elseif (isset($HTTP_SERVER_VARS)) {
         $SERVER_NAME = $HTTP_SERVER_VARS['SERVER_NAME'];
         $SERVER_PORT = $HTTP_SERVER_VARS['SERVER_PORT'];
         $SCRIPT_NAME = isset($HTTP_SERVER_VARS['PHP_SELF']) ? $HTTP_SERVER_VARS['PHP_SELF'] : $HTTP_SERVER_VARS['SCRIPT_NAME'];
         $HTTPS = $HTTP_SERVER_VARS['HTTPS'];
      } else {
         $this->fault('0000', 'Neither _SERVER nor HTTP_SERVER_VARS is available');
      }

      if ($SERVER_PORT == 80) {
         $SERVER_PORT = '';
      } else {
         $SERVER_PORT = ':' . $SERVER_PORT;
      }

      if (false == $namespace) {
         $namespace = "http://$SERVER_NAME/soap/$serviceName";
      }

      if(false == $endpoint) {
         if ($HTTPS == '1' || $HTTPS == 'on') {
            $SCHEME = 'https';
         } else {
            $SCHEME = 'http';
         }
         $endpoint = "$SCHEME://$SERVER_NAME$SERVER_PORT$SCRIPT_NAME";
      }

      if(false == $schemaTargetNamespace) {
         $schemaTargetNamespace = $namespace;
      }

      $this->wsdl = new wsdl;
      $this->wsdl->serviceName = $serviceName;
      $this->wsdl->endpoint = $endpoint;
      $this->wsdl->namespaces['tns'] = $namespace;
      $this->wsdl->namespaces['soap'] = 'http://schemas.xmlsoap.org/wsdl/soap/';
      $this->wsdl->namespaces['wsdl'] = 'http://schemas.xmlsoap.org/wsdl/';
      if ($schemaTargetNamespace != $namespace) {
         $this->wsdl->namespaces['types'] = $schemaTargetNamespace;
      }
      $this->wsdl->schemas[$schemaTargetNamespace][0] = new xmlschema('', '', $this->wsdl->namespaces);
      $this->wsdl->schemas[$schemaTargetNamespace][0]->schemaTargetNamespace = $schemaTargetNamespace;
      $this->wsdl->schemas[$schemaTargetNamespace][0]->imports['http://schemas.xmlsoap.org/soap/encoding/'][0] = array('location' => '', 'loaded' => true);
      $this->wsdl->schemas[$schemaTargetNamespace][0]->imports['http://schemas.xmlsoap.org/wsdl/'][0] = array('location' => '', 'loaded' => true);
      $this->wsdl->bindings[$serviceName.'Binding'] = array(
         'name'=>$serviceName.'Binding',
         'style'=>$style,
         'transport'=>$transport,
         'portType'=>$serviceName.'PortType');
      $this->wsdl->ports[$serviceName.'Port'] = array(
         'binding'=>$serviceName.'Binding',
         'location'=>$endpoint,
         'bindingType'=>'http://schemas.xmlsoap.org/wsdl/soap/');
   }

   protected function Register($name,$in=array(),$out=array(),$namespace=false,$soapaction=false,$style=false,$use=false,$documentation='',$encodingStyle='') {
      global $HTTP_SERVER_VARS;

      if($this->externalWSDLURL){
         die('You cannot bind to an external WSDL file, and register methods outside of it! Please choose either WSDL or no WSDL.');
      }
      if (! $name) {
         die('You must specify a name when you register an operation');
      }
      if (!is_array($in)) {
         die('You must provide an array for operation inputs');
      }
      if (!is_array($out)) {
         die('You must provide an array for operation outputs');
      }
      if(false == $namespace) {
      }
      if(false == $soapaction) {
         if (isset($_SERVER)) {
            $SERVER_NAME = $_SERVER['SERVER_NAME'];
            $SCRIPT_NAME = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
         } elseif (isset($HTTP_SERVER_VARS)) {
            $SERVER_NAME = $HTTP_SERVER_VARS['SERVER_NAME'];
            $SCRIPT_NAME = isset($HTTP_SERVER_VARS['PHP_SELF']) ? $HTTP_SERVER_VARS['PHP_SELF'] : $HTTP_SERVER_VARS['SCRIPT_NAME'];
         } else {
            $this->fault('0000', "Neither _SERVER nor HTTP_SERVER_VARS is available");
         }
         $soapaction = "http://$SERVER_NAME$SCRIPT_NAME/$name";
      }
      if(false == $style) {
         $style = "rpc";
      }
      if(false == $use) {
         $use = "encoded";
      }
      if ($use == 'encoded' && $encodingStyle = '') {
         $encodingStyle = 'http://schemas.xmlsoap.org/soap/encoding/';
      }

      $this->operations[$name] = array(
         'name' => $name,
         'in' => $in,
         'out' => $out,
         'namespace' => $namespace,
         'soapaction' => $soapaction,
         'style' => $style);
      if($this->wsdl){
         $this->wsdl->addOperation($name,$in,$out,$namespace,$soapaction,$style,$use,$documentation,$encodingStyle);
      }
   }

   function &GetHandler() {
      return $this;
   }

   function Send() {
      global $HTTP_RAW_POST_DATA;

      if (isset($_SERVER['QUERY_STRING'])) {
         $qs = $_SERVER['QUERY_STRING'];
      } elseif (isset($HTTP_SERVER_VARS['QUERY_STRING'])) {
         $qs = $HTTP_SERVER_VARS['QUERY_STRING'];
      } else {
         $qs = '';
      }

      if (preg_match('/wsdl/', $qs)) {
         if ($this->mWsdlFile) {
            if (strpos($this->mWsdlFile, "://") !== FALSE) { // assume URL
               header('Location: ' . $this->mWsdlFile);
            } else { // assume file
               header("Content-Type: text/xml\r\n");
               $fp = fopen($this->mWsdlFile, 'r');
               fpassthru($fp);
            }
         } elseif ($this->wsdl) {
            header("Content-Type: text/xml; charset=ISO-8859-1\r\n");
            print $this->wsdl->serialize($this->mDebugMode);
            /*if ($this->mDebugMode) {
               $this->debug('wsdl:');
               $this->appendDebug($this->varDump($this->wsdl));
               print $this->getDebugAsXMLComment();
            }*/
         } else {
            header("Content-Type: text/html; charset=ISO-8859-1\r\n");
            print "This service does not provide WSDL";
         }
      } elseif (preg_match('/html/', $qs)) {
         print $this->wsdl->webDescription();
      } else {
         $this->handle();
      }
   }
}
?>