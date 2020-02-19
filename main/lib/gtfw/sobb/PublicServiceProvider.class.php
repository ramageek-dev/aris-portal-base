<?php

/**
* Public Service provider interface
* @package SsoBroker
* @author Akhmad Fathonih <toni@gamatechno.com>
* @version 1.0
* @copyright 2006&copy;Gamatechno
*/

interface IPublicServiceProvider {
   /**
   * get services metadata
   * @return array of services, ready to to incorporated into soapgateway
   */
   function getServices();

   /**
   * register service using wsdl metadata
   * @param array $wsdl_metadata metadata describing the service
   */
   function registerService($wsdl_metadata);

   /**
   * register services here
   */
   function collectServices();

   /**
   * specify services unique prefix here
   */
   function getServicePrefix();
}

/**
Soap gateway1 signature
*/

interface ISoapGateway1 {
   function providesService($service);
}

class PublicServiceProvider implements IPublicServiceProvider, ISoapGateway1 {
   var $mServices;

   function PublicServiceProvider() {
      $this->mServices = array();
   }

   /**
   * get services metadata
   * @return array of services, ready to to incorporated into soapgateway
   */
   function getServices() {
      return $this->mServices;
   }

   /**
   * register service using wsdl metadata
   * @param array $wsdl_metadata metadata describing the service
   */
   function registerService($wsdl_metadata) {
      foreach($wsdl_metadata as $key => $value)
         $this->mServices[$key] = $value;
   }

   /**
   * register services here
   */
   function collectServices() {
      die('collectServices::register service here');
   }

   /**
   * specify services unique prefix here
   */
   function getServicePrefix() {
      die('getServicePrefix::specify service prefix here');
   }

   /**
   * return complex type here
   */
   function getComplexTypes() {
      //die('getComplexType::return complex type here');
      return false;
   }
   /**
   * Check wether this class provides such services
   */
   function providesService($service) {
      $service = preg_replace('/^'.$this->getServicePrefix().'_/', $service, $dummy); // remove prefix
      return method_exists($this, $service);
   }

   // A Hook, make SoapGateway1 and SoapGateway2 blends seamlessly
   function __call($func, $args) {
      if (preg_match("/^".$this->getServicePrefix()."_(.*)/", $func, $found)) {
         return call_user_func_array(array(&$this, $found[1]), $args );
      } else
         return call_user_func_array(array(&$this, $func), $args ); // recursive?
   }
}

class DefaultPublicService extends PublicServiceProvider {
   var $mHelloFunctions = array(
      'Hello' => array(
            'in' => array(),
            'out' => array("return" => "xsd:string"),
            'namespace' => 'soapgateway',
            'soapaction' => 'soapgateway',
            'style' => 'rpc',
            'use' => false,
            'documentation' => 'Polls Soap Gateway',
            'encodingStyle' => 'utf8')
         );

   function Hello() {
      $result = 'Hello World!';
      return new soapval("return", "xsd:string", $result); // klo bsia soapval ini dinaikkan dan di wrap sehingga busines classnya menjadi truly free from soap* hassle
   }

   function collectServices() {
      //die('collectServices::register service here');
      $this->registerService($this->mHelloFunctions);
   }

   function getServicePrefix() {
      return 'default';
   }
}

class ComplexPublicService extends PublicServiceProvider {
   var $mHelloFunctions = array(
      'Hello' => array(
            'in' => array(),
            'out' => array("return" => "tns:SomeStruct"),
            'namespace' => 'soapgateway',
            'soapaction' => 'soapgateway',
            'style' => 'rpc',
            'use' => false,
            'documentation' => 'Polls Soap Gateway in Complex mode',
            'encodingStyle' => 'utf8'),
       'HelloArray' => array(
            'in' => array(),
            'out' => array("return" => "tns:ListType"),
            'namespace' => 'soapgateway',
            'soapaction' => 'soapgateway',
            'style' => 'rpc',
            'use' => false,
            'documentation' => 'List type output',
            'encodingStyle' => 'utf8')
         );

   function Hello() {
      $result = array( 'hi' => 'cookie:<pre>'.print_r($_COOKIE, true).'</pre>', 'object'=> 'session_id:'.session_id() );
      return new soapval("return", "tns:SomeStruct", $result); // klo bsia soapval ini dinaikkan dan di wrap sehingga busines classnya menjadi truly free from soap* hassle
   }

   function HelloArray() {
      $result = array( 'hi', 'Hello', 'object', 'World!' );
      return new soapval("return", "tns:ListType", $result); // klo bsia soapval ini dinaikkan dan di wrap sehingga busines classnya menjadi truly free from soap* hassle
   }

   function collectServices() {
      //die('collectServices::register service here');
      $this->registerService($this->mHelloFunctions);
   }

   function getServicePrefix() {
      return 'complex';
   }

   function getComplexTypes() {
      $tnsComplexs[] = array(
         'SomeStruct',
         'complexType',
         'struct',
         'all',
         "",
         array(
            'hi' => array('name'=>'status','type'=>'xsd:string'),
            'object' => array('name'=>'ssid','type'=>'xsd:string')
         ));

      $tnsComplexs[] = array(
         'ListType',
         'complexType',
         'array'
         );

      return $tnsComplexs;
   }
}

class SessionPublicService extends PublicServiceProvider {
   var $mExposedFunctions = array(
      'GetSession' => array(
            'in' => array("name" => 'xsd:string'),
            'out' => array("return" => "xsd:string"),
            'namespace' => 'soapgateway',
            'soapaction' => 'soapgateway',
            'style' => 'rpc',
            'use' => false,
            'documentation' => 'Get cookie value',
            'encodingStyle' => 'utf8'),
       'SetSession' => array(
            'in' => array("name" => 'xsd:string', "value" => 'xsd:string'),
            'out' => array("return" => "xsd:boolean"),
            'namespace' => 'soapgateway',
            'soapaction' => 'soapgateway',
            'style' => 'rpc',
            'use' => false,
            'documentation' => 'Set Session value',
            'encodingStyle' => 'utf8'),
       'GetCookie' => array(
            'in' => array(),
            'out' => array("return" => "xsd:string"),
            'namespace' => 'soapgateway',
            'soapaction' => 'soapgateway',
            'style' => 'rpc',
            'use' => false,
            'documentation' => 'Get cookie',
            'encodingStyle' => 'utf8')
            ,
       'SetCookie' => array(
            'in' => array("name" => 'xsd:string', "value" => 'xsd:string'),
            'out' => array("return" => "xsd:boolean"),
            'namespace' => 'soapgateway',
            'soapaction' => 'soapgateway',
            'style' => 'rpc',
            'use' => false,
            'documentation' => 'Set cookie value',
            'encodingStyle' => 'utf8')
         );

   function GetCookie() {
      ob_start();
      var_dump($_COOKIE);
      $result = ob_get_contents();
      ob_end_clean();
      //$result = print_r($_COOKIE, true);
      return new soapval("return", "xsd:string", $result);
   }

   function SetSession($name, $value) {
      $_SESSION[$name] = $value;
      $result = true;
      return new soapval("return", "xsd:boolean", $result);
   }

   function SetCookie($name, $value) {
      $_COOKIE[$name] = $value;
      $result = true;
      return new soapval("return", "xsd:boolean", $result);
   }

   function GetSession($name) {
      $result = $_SESSION[$name];
      return new soapval("return", "xsd:string", $result);
   }

   function collectServices() {
      //die('collectServices::register service here');
      $this->registerService($this->mExposedFunctions);
   }

   function getServicePrefix() {
      return 'test';
   }
}
?>