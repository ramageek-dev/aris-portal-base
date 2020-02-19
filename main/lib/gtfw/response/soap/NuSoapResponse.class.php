<?php
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.nusoap_base.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.soap_val.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.soap_parser.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.soap_fault.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.soap_transport_http.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.xmlschema.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.wsdl.php';
require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/nusoap/class.soap_server.php';

/**
 * Soap Response
 */
class NuSoapResponse extends soap_server implements ResponseIntf {
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

   // redefine this property if you want to set debug mode
   protected $mDebugMode = FALSE;

   function __construct() {
      // force to set global variable $debug
      // before calling parent constructor
      $GLOBALS['debug'] = $this->mDebugMode;

      parent::soap_server($this->mWsdlFile);

      // to support SoapGatewayBase
      // --------------------------
      if (!$this->mWsdlFile && !empty($this->mRegisteredFunctions)) {
         foreach ($this->mRegisteredFunctions as $func_name => $params) {
            if (is_array($params) && $params != NULL) {
               $this->register($func_name, $params['in'], $params['out'], $params['namespace'],
                  $params['soapaction'], $params['style'], $params['use'], $params['documentation'],
                  $params['encodingStyle']);
            } else {
               $this->register($func_name);
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
            $this->configureWSDL($class_name, FALSE, Configuration::Instance()->GetValue('application', 'baseaddress') .
               Dispatcher::Instance()->GetUrl(Dispatcher::Instance()->mModule, Dispatcher::Instance()->mSubModule,
               Dispatcher::Instance()->mAction, Dispatcher::Instance()->mType, TRUE), $default_style);

            // register functions to be exposed as service
            if (isset($service_description['service']) && is_array($service_description['service'])) {
               foreach ($service_description['service'] as $func_name => $params) {
                  // skip undeclared fuctions
                  if (!method_exists($this, 'Service' . $func_name))
                     continue;
                  if (is_array($params) && $params != NULL) {
                     $this->register($func_name, $params['in'], $params['out'], $params['namespace'],
                        $params['soapaction'], $params['style'], $params['use'], $params['documentation'],
                        $params['encodingStyle']);
                  } else {
                     $this->register($func_name);
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
   }

   // overrided method
   // see original code in nusoap.php: soap_server::invoke_method()
   function invoke_method() {
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

      // does method exist?
      if (!method_exists($this, 'Service' . $this->methodname)) {
         $this->debug("in invoke_method, function '$this->methodname' not found!");
         $this->result = 'fault: method not found';
         $this->fault('Client',"method '$this->methodname' not defined in service");
         return;
      }

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
         $funcCall = "\$this->methodreturn = \$this->Service{$this->methodname}(";
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
         $call_arg = array(&$this, 'Service' . $this->methodname);
         $this->methodreturn = call_user_func_array($call_arg, $this->methodparams);
      }

      $this->debug('in invoke_method, methodreturn:');
      $this->appendDebug($this->varDump($this->methodreturn));
      $this->debug("in invoke_method, called method $this->methodname, received $this->methodreturn of type ".gettype($this->methodreturn));
   }

   function &GetHandler() {
      return $this;
   }

   function Send() {
      global $HTTP_RAW_POST_DATA;
      
      $HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';

      $this->service($HTTP_RAW_POST_DATA);
   }
}
?>