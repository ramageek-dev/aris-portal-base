<?php
class NusoapResponse extends soap_server {
   protected $mrDispatcher;
   protected $mrSecurity;
   protected $mrSession;

   // redefine this property if you want to use wsdl
   //var $mWsdl = NULL;

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
   public static $mRegisteredFunctions = array();
   
   // redefine this property with the name of registered types
   // in the form of:
   // array(
   //    'TypeName1' => array(
   //          'type' => 'complexType', // 'simpleType' || 'complexType'
   //          'phptype' => 'scalar', // 'scalar' || 'array' || 'struct', simpleType must be scalar, complexType can be array or struct
   //          'compositor' = '', // 'all' || 'sequence' || 'choice'
   //          'restrictionBase' => 'http://schemas.xmlsoap.org/soap/encoding/:Array', // namespace:name
   //          'elements' => array ( name => array(name=>'',type=>'') ), // or array()
   //          'attrs' => array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:string[]')), // or array()
   //          'arraytype' => 'xsd:string' // namespace:name,
   //          'enumerations' => array() // use only with simple type
   //       ),
   //    'TypeName2' => array(
   //          'type' => 'complexType', // 'simpleType' || 'complexType'
   //          'phptype' => 'scalar', // 'scalar' || 'array' || 'struct', simpleType must be scalar, complexType can be array or struct
   //          'compositor' = '', // 'all' || 'sequence' || 'choice'
   //          'restrictionBase' => 'http://schemas.xmlsoap.org/soap/encoding/:Array', // namespace:name
   //          'elements' => array ( name => array(name=>'',type=>'') ), // or array()
   //          'attrs' => array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:string[]')), // or array()
   //          'arraytype' => 'xsd:string' // namespace:name,
   //          'enumerations' => array() // use only with simple type
   //       ),
   //    ...
   // )
   public static $mRegisteredTypes = array();
   
   // redefine this property to reflect your module
   public static $mEndpoint = array('mod' => '', 'sub' => '', 'act' => '', 'typ' => '');

   // redefine this property if you want to set debug mode
   var $mDebugMode = FALSE;

   function NusoapResponse() {
      // force to set global variable $debug
      // before calling parent constructor
      $GLOBALS['debug'] = $this->mDebugMode;

      parent::soap_server();

      $this->configureWsdl(__CLASS__ . 'Service', FALSE, $this->mEndpoint);

      $this->mrDispatcher = Dispatcher::Instance();
      $this->mrSecurity = Security::Instance();
      $this->mrSession = Session::Instance();

      if (!empty($this->mRegisteredFunctions)) {
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
      if (!empty($this->mRegisteredTypes)) {
         foreach ($this->mRegisteredTypes as $type_name => $params) {
            if (is_array($params) && count($params) > 0) {
               if ($params['type'] == 'complexType' && $params['phptype'] != 'scalar') {
                  $this->wsdl->addComplexType($type_name, $params['type'], $params['phptype'], $params['compositor'],
                     $params['restrictionBase'], $params['elements'], $params['attrs'], $params['arraytype']);
               } else {
                  $this->wsdl->addSimpleType($type_name, $params['type'], $params['phptype'], $params['compositor'],
                     $params['restrictionBase'], $params['elements'], $params['attrs'], $params['arraytype']);
               }
            } else {
               $this->register($func_name);
            }
         }
      }
      $this->wsdl->addComplexType('ListType', 'complexType', 'array');
      $this->wsdl->addComplexType(
         'AgmListType',
         'complexType',
         'array',
         '',
         'SOAP-ENC:Array',
         array(),
         array(array('ref' => 'SOAP-ENC:arrayType', 'wsdl:arrayType' => 'xsd:integer[]')),
         'xsd:integer'
      );


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
      if (!method_exists($this, 'SoapFunc' . $this->methodname)) {
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
         $funcCall = "\$this->methodreturn = \$this->SoapFunc{$this->methodname}(";
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
         $call_arg = array(&$this, 'SoapFunc' . $this->methodname);
         $this->methodreturn = call_user_func_array($call_arg, $this->methodparams);
      }

      $this->debug('in invoke_method, methodreturn:');
      $this->appendDebug($this->varDump($this->methodreturn));
      $this->debug("in invoke_method, called method $this->methodname, received $this->methodreturn of type ".gettype($this->methodreturn));
   }
}
?>
