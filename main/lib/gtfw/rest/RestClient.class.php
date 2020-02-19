<?php
/**
RestClient

@author  :Dyan Galih

@modified   :
   Wahyu A.Y   :
      - @20140923 : rewrite code, fixing cookie, parsing http header
   Abdul R. Wahid
      - @20160119 : upgrade uploadPhp to support send file information options (mime-type, etc)
      - @20160223 : add ALo Support
      - @20160404 : add httpheader function, auto turn off ALo and new debug fn if not supported, front and back curl header
@copyright 2013 Gamatechno
*/

if(defined('RESTCLIENT_HEADER_SEPARATOR'))return;  // prevent executing the line below if already included

define('RESTCLIENT_HEADER_SEPARATOR', "\r\n\r\n");
define('RESTCLIENT_MAX_REDIRECT', 20);
if(function_exists('curl_file_create'))define('RESTCLIENT_FILE_UPLOAD_MODE', 'uploadPhpNew');
else define('RESTCLIENT_FILE_UPLOAD_MODE', 'uploadPhpOld');

class RestClient{
   public $debug = array('info' => array(), 'result' => array());
   
   private $mHost;

   private $mPath;

   private $mUser='';

   private $mPass;

   private $mPostStatus=false;

   private $mProxyPort;

   private $mProxyStatus=false;

   private $mProxyAddress='';

   private $mProxyType='';

   private $mAuthenticationMethodList = array('default'=>CURLAUTH_BASIC,'digest'=>CURLAUTH_DIGEST,'ntlm'=>CURLAUTH_NTLM,'gssngotiate'=>CURLAUTH_GSSNEGOTIATE);

   private $mAuthenticationMethod;

   private $mKeycode;

   private $mPost = '';

   private $mGet = '';

   private $userAgent;

   private $followLocation;

   private $secure;

   private $allowedHost;

   private $debugStatus = false;

   private $oriStatus = false;

   private $mNewSessionFlag = true;

   private $mRedirectCount = 0;

   private $mALoCount = 0;
   
   private $mCookie = '';
   
   private $mSessionName = 'RestClient_cookie';
   
   private $mRedirectCode = array('301' => true, '302' => true, '303' => true);

   private $mHeader = array();

   private $mHttpHeader = array();

   private $responseHeader = array();

   private $gtfwJsonHeader = false;

   static $mrInstance;

   public function __construct()
   {
      $this->userAgent = $_SERVER['HTTP_USER_AGENT'];
      $this->followLocation = false;
      $this->mAuthenticationMethod=$this->mAuthenticationMethodList['default'];
      $this->tempDir = sys_get_temp_dir();
   }

   /* ==================== MAIN SECTION ==================== */
   /*
      Set end point
   */
   public function SetPath($path) {
      // $this->mHeader = array();
      $tmp = $this->mPath = $path;
      $pos = strpos($tmp, '?');
      if($pos !== false) $tmp = substr($tmp, 0, $pos);
      if(strpos($tmp, 'http') !== 0)$tmp = 'http://' . $tmp;
      $tmp = parse_url($tmp);
      
      $this->mHost = $tmp['host'];
      if(isset($_SESSION[$this->mSessionName][$this->mHost])){
         $this->mCookie = $_SESSION[$this->mSessionName][$this->mHost];
      }
      $pos = $tmp = null;
   }

   /*
      Get end point
   */
   public function GetPath() {

      return $this->mPath;
   }

   /*
      Set GET query string
   */
   public function SetGet($queryString){
      $add='';

      if (!empty($this->mGet)) $add = '&';

      //if (empty($this->mGet)) $this->mGet.= '?';
      $this->mGet .= $queryString;
   }

   /*
      Get query string
   */
   public function GetGet(){
      return $this->mGet;
   }

   /*
      Set POST flag
   */
   public function setPostOn(){
      $this->mPostStatus=true;
   }

   /*
      Set POST value
   */
   public function SetPost($arrPost){
      $this->setPostOn();
      if(is_array($arrPost)){
         foreach($arrPost as $name => $value){
            // set file upload
            if(isset($value{0}) && $value{0} === '@'){
               $arrPost[$name] = $this->{RESTCLIENT_FILE_UPLOAD_MODE}(substr($value, 1));
            }
         }
      }
      $this->mPost = $arrPost;
   }

   /*
      Get POST value
   */
   public function GetPost(){
      return $this->mPost;
   }
   
   /*
      Set debug on
   */
   public function setDebugOn() {
      #code here
      $this->debugStatus = true;
   }

   /*
      Set debug off
   */
   public function setDebugOff() {
      $this->debugStatus = false;
   }

   /*
      Set mode to raw result
   */
   public function setResultDefault(){
      $this->oriStatus = true;
   }

   /*
      Set mode to json
   */
   public function setResultArray(){
      $this->oriStatus = false;
   }

   /*
      Get response header
   */
   public function getResponseHeader(){
      return $this->responseHeader;
   }

   /*
      Set gtfw json header on
   */
   public function setGtfwJsonHeaderOn() {
      $this->gtfwJsonHeader = true;
   }

   /*
      Set gtfw json header off
   */
   public function setGtfwJsonHeaderOff() {
      $this->gtfwJsonHeader = false;
   }

   /* END OF MAIN SECTION */
   
   
   /* ==================== PROXY SECTION ==================== */
   /*
      Set proxy on
   */
   public function setProxyOn(){
      $this->mProxyStatus=true;
   }

   /*
      Set proxy address
   */
   public function setProxyAddress($address, $port){
      $this->mProxyAddress=$address;
      $this->mProxyPort=$port;
   }

   /*
      Set proxy type
   */
   public function setProxyType($type='http'){
      if($type=='http')
         $this->mProxyType = CURLPROXY_HTTP;
      else
         $this->mProxyType = CURLPROXY_SOCKS5;
   }

   /*
      Get proxy type
   */
   public function getProxyType(){
      return $this->mProxyType;
   }
   /* END OF PROXY SECTION */

   
   /* ==================== AUTHENTICATION SECTION ==================== */
   /*
      Set authentication method
   */
   public function setAuthenticationMethod($method='default'){
      $this->mAuthenticationMethod=$this->mAuthenticationMethodList[$method];
   }

   /*
      Set user and password for http authentication
   */
   public function setUserPassword($user, $pass){
      $this->mUser = $user;
      $this->mPass = $pass;
   }
   /* END OF AUTHENTICATION SECTION */
   
   /*
      Additional request header
      WARNING :
         - make sure each front header has different value of priority.
         - front header will executed first, but they can be overwrite by hardcoded or back header
   */
   public function setHeader($header, $value, $priority=''){
      if ($priority != '') {
         $this->mHeader[$this->mPath]['front'][$priority] = array($header, $value);
      } else {
         $this->mHeader[$this->mPath]['back'][] = array($header, $value);
      }
   }

   public function getHeader() {
      return $this->mHeader[$this->mPath];
   }

   /*
      Additional request http header
   */
   public function setHttpHeader($key, $value){
      $this->mHttpHeader[$this->mPath][] = $key .": ". $value;
   }

   public function clearHeader($all = false) {
      if ($all) {
         $this->mHeader = array();
      } else {
         $this->mHeader[$this->mPath] = array();
      }
   }

   /*
      Additional request http header
   */
   public function clearHttpHeader($all = false){
      if ($all) {
         $this->mHttpHeader = array();
      } else {
         $this->mHttpHeader[$this->mPath] = array();
      }
   }

   /*
      Send request
   */
   public function Send($sqlQueries = ''){
      if(!empty($sqlQueries)){
         // set post mode for query
         $this->SetPost($sqlQueries);
      }
      
      $url  = $this->mPath . $this->mGet;
      return $this->doRequest($url);
   }

   /*
      Set key code, still unused
   */
   public function SetKeyCode($key)
   {
      $this->mKeyCode = $key;
   }

   /*
      Get key code, still unused
   */
   public function GetKeyCode()
   {

      return $this->mKeyCode;
   }

   /* ==================== PRIVATE SECTION ==================== */
   /*
      Set request header
   */
   private function setRequestHeader($ch){
      if($this->mCookie === ''){
         $this->mCookie=microtime(true);
      }
      $cookieId = 'cookiefile_'.$this->mCookie;
      $cookieId = $this->tempDir . '/' . $cookieId;

      if (isset($this->mHeader[$this->mPath]['front'])) {
         foreach ($this->mHeader[$this->mPath]['front'] as $headers) {
            curl_setopt($ch, $headers[0], $headers[1]);
         }
      }

      curl_setopt($ch, CURLOPT_HEADER, true);      // selalu gunakan header
      curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 25);
      curl_setopt($ch, CURLOPT_TIMEOUT, 10);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

      if($this->mPostStatus){
         curl_setopt($ch, CURLOPT_POST, $this->mPostStatus);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $this->mPost);
      }
      curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->followLocation);
      
      curl_setopt($ch, CURLOPT_COOKIESESSION, 0);
      curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieId);
      curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieId);

      if(isset($this->mProxyStatus) && $this->mProxyAddress !== ''){
         curl_setopt($ch, CURLOPT_PROXYTYPE, $this->mProxyType);
         curl_setopt($ch, CURLOPT_PROXY, $this->mProxyAddress.':'.$this->mProxyPort);
      }

      if(isset($this->mUser) && $this->mUser !== ''){
         curl_setopt($ch, CURLOPT_USERPWD, $this->mUser.':'.$this->mPass);
         curl_setopt($ch, CURLOPT_HTTPAUTH, $this->mAuthenticationMethod);
      }

      if ($this->gtfwJsonHeader) {
         $this->setHttpHeader('X-GtfwModuleType', 'json');
      }

      if (isset($this->mHttpHeader[$this->mPath]) && !empty($this->mHttpHeader[$this->mPath])) {
         curl_setopt($ch, CURLOPT_HTTPHEADER, $this->mHttpHeader[$this->mPath]);
      }

      if (isset($this->mHeader[$this->mPath]['back'])) {
         foreach ($this->mHeader[$this->mPath]['back'] as $headers) {
            curl_setopt($ch, $headers[0], $headers[1]);
         }
      }
   }
   
   /*
      Process request
   */
   private function doRequest($url){
      // init request
      $result = '';
      $this->mRedirectCount = 0;
      $ch = curl_init();
      $result = $this->sendRequest($ch, $url);
      curl_close($ch);
      
      // flush get and post param
      $this->mGet='';
      $this->mPost='';

      // debug
      if ($this->debugStatus) {
         if (defined('GTFW_X_JSON') && GTFW_X_JSON) {
            setLog($this->debug, 'System', 'RestClient');
         } else {
            echo "<pre>";
            print_r($this->debug);
            echo "</pre>";          
         }
      }
      
      // result
      if($this->oriStatus==false){
         $result = json_decode($result,true);
      }
      if (class_exists('GtfwCpu') && class_exists('ALoService')) {
         if(GtfwCpu::$system->GtfwConfiguration->get('application', 'autologin_service') && $result['gtfwResult']['status'] == 401) {
            ALoService::Instance()->setUrlApp(str_replace(basename($this->mPath), '', $this->mPath));
            if($this->mALoCount++ < 10){
               ALoService::Instance()->login();
               $result = $this->doRequest($url);
            }
         }
      }
      return $result;
   }
   
   /*
      Send request
   */
   private function sendRequest($ch, $url){
      // set header
      curl_setopt($ch, CURLOPT_URL, $url);
      $this->setRequestHeader($ch);

      // exec curl
      $data = curl_exec($ch);

      // get info request
      $curl_info = curl_getinfo($ch);
      $curl_err = curl_error($ch);

      $parsed = $this->parseHttpResponse($data);
      
      // debugging
      if($this->debugStatus){
         $this->debug['error'] = $curl_err;
         $this->debug['info'] = $curl_info;
         $this->debug['result']['header'] = $parsed['header'];
         $this->debug['result']['data'] = $parsed['data'];  
      }
      
      // check redirect header
      if(isset($this->mRedirectCode[$info['http_code']])){
         if($this->mRedirectCount++ < RESTCLIENT_MAX_REDIRECT){
            $matches = array();
            $url = false;
            if(preg_match('#Location: (.*)#', $parsed['header'], $matches)){
               $url = @parse_url(trim($matches[1]));
            }
            if(!$url){
               // invalid url
               return false;
            }
            $last_url = @parse_url($info['url']);
            if(!isset($url['scheme']))$url['scheme'] = $last_url['scheme'];
            if(!isset($url['host']))$url['host'] = $last_url['host'];
            if(!isset($url['path']))$url['path'] = $last_url['path'];
            
            if(!isset($url['port']))$url['port'] = isset($last_url['port'])?':' . $last_url['port']:'';
            else $url['port'] = ':' . $url['port'];
            
            if(isset($url['query']) and !empty($url['query']))$url['query'] = '?' . $url['query'];
            else $url['query'] = '';
            
            $new_url = $url['scheme'] . '://' . $url['host'] . $url['port'] . $url['path'] . $url['query'];

            $last_url = $url = $matches = null;
            $parsed['data'] = $this->sendRequest($ch, $new_url);
         }else{
            return false;
         }
      }
      $this->responseHeader = explode("\n", $parsed['header']);
      return trim($parsed['data']);
   }
   
   /*
      Parse Http Response, seperate header and data
   */
   private function parseHttpResponse($data){
      // parsing header, loop to prevent added header from proxy or redirect flag
      $tmp = explode(RESTCLIENT_HEADER_SEPARATOR, $data);
      $header = $tmp[0];
      $m = count($tmp);
      $delimiterLength = strlen(RESTCLIENT_HEADER_SEPARATOR);
      $headerLength = strlen($header) + $delimiterLength;
      if($m > 2){
         for($i = 1; $i < $m; ++$i){
            if(stripos($tmp[$i], 'HTTP/1.1') === false){
               $header = $tmp[$i-1];
               break;
            }else $headerLength += strlen($tmp[$i]) + $delimiterLength;
         }
         
         $data = substr($data, $headerLength);
      }else $data = isset($tmp[1])?$tmp[1]:'';
      $tmp = $i = $m = $delimiterLength = $headerLength = null;
      return array(
         'header' => $header,
         'data'      => $data
      );
   }
   
   /*
      Handle file upload for PHP 5.5 >
   */
   private function uploadPhpOld($strPost){
      if (strpos($strPost, ';filename=') || strpos($strPost, ';type=')) {
         return '@' . $strPost;  
      } else {
         return '@' . realpath($strPost);
      }     
   }
   
   /*
      Handle file upload for PHP 5.5 <=
   */
   private function uploadPhpNew($strPost){
      $arrFilePost = explode(';', $strPost);
      $fileAttr['file'] = $arrFilePost[0];
      foreach ($arrFilePost as $key => $val) {
         if (strpos($val, '=')) {
            $attr = explode('=', $val);
            $fileAttr[$attr[0]] = $attr[1];
         }
      }
      if (!isset($fileAttr['filename'])) {
         $fileAttr['filename'] = basename($fileAttr['file']);
      }
      if (!isset($fileAttr['type'])) {
         $fileAttr['type'] = null;
      }
      return curl_file_create($fileAttr['file'], $fileAttr['type'], $fileAttr['filename']);
   }
}
?>