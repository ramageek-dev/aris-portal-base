<?php
if (in_array('Countable', array_keys(spl_classes()))) :
   SysLog::Log('Countable class found: using implements Countable', 'sanitizer');
   abstract class XVarBase implements Countable {
      protected $mCompound;

      //Region Countable
      function count()
      {
         return count(array_keys($this->mCompound));
      }
      //EndRegion
   }
else:
   SysLog::Log('Countable class not found: using regular extends (without implements)', 'sanitizer');
   abstract class XVarBase {
      protected $mCompound;

      //Region Countable
      function count()
      {
         return count(array_keys($this->mCompound));
      }
      //EndRegion
   }
endif;



class SanitizerFactory extends XVarBase implements ArrayAccess, Iterator {
   protected $mInitialized = false;

   static function Wrap($compound) {
      $vb = new SanitizerFactory();
      $vb->DoInit($compound);
      unset($compound);

      return $vb;
   }

   private function __construct() {
      $this->currentIndex = 0;
   }

   function DoGetVar($varName) {
      if ($this->DoVarIsSet($varName)) {
         $value = $this->mCompound[$varName];
         //var_dump($value);
         if (is_array($value)) {
            $result = SanitizerFactory::Wrap($value);

            SysLog::Log('Get var "'.$varName.'" returns SanitizerFactory::Wrap : '.$result, 'sanitizer');
         } else {
            $result = Sanitizer::Instance($value);
            SysLog::Instance()->log('Get var "'.$varName.'" returning "'.$value.'" in Sanitizer : '.$result, 'sanitizer');
         }
      } else {
         $result = Sanitizer::Instance(''); // make it behave the same, with or without availbility of compund var
         SysLog::Instance()->log('Get var (!isset) "'.$varName.'" returns: '.$result, 'sanitizer');
      }

      return $result;
   }

   function DoVarIsSet($varName) {
      return isset($this->mCompound[$varName]);
   }

   function IsArray() {
      return is_array($this->mCompound);
   }

   function AsArray($autoSanitize = true) {
      // sanitize first, do array walk to Sanitizer::Other($value)->SystemString();
      $result = $this->mCompound;

      if ($autoSanitize) {
         $result = $this->Sanitize($result);
      }

      // return result
      return $result;
   }

   function __toString() {
      return 'Object wrapped array';
   }

   private function Sanitize($compound) {
      $result = array();
      foreach($compound as $key => $value) {
         if (!is_array($value)) {
            $sanitized_value = Sanitizer::Instance($value)->SqlString()->Raw();
         } else
            $sanitized_value = $this->Sanitize($value);

         $result[$key] = $sanitized_value;
      }

      return $result;
   }

   protected function DoInit($var) {
      $this->mCompound = $var;
      $this->mInitialized = true;
   }

   // SPL crazeeeeee
   private $currentIndex;

   //===== Region ArrayAccess
   function offsetExists($offset)
   {
      return $this->DoVarIsSet($offset);
   }

   function offsetGet($offset)
   {
      return $this->DoGetVar($offset);
   }

   function offsetSet($offset,$value)
   {
      //throw new Exception("This collection is read only.");
      $this->mCompound[$offset] = $value;
   }

   function offsetUnset($offset)
   {
      //throw new Exception("This collection is read only.");
      unset($this->mCompound[$offset]);
   }
   //========= EndRegion

   //Region Countable
   function count()
   {
      return count(array_keys($this->mCompound));
   }
   //EndRegion

   //Region Iterator
   function current()
   {
      $dummy = array_keys($this->mCompound);
      return $this->offsetGet($dummy[$this->currentIndex]);
   }

   function key()
   {
      $dummy = array_keys($this->mCompound);
      return $dummy[$this->currentIndex];
   }

   function next()
   {
      $dummy = array_keys($this->mCompound);

      if ($this->currentIndex < count($dummy))
         $this->currentIndex++;

      return $this->currentIndex;
   }

   function rewind()
   {
      $this->currentIndex = 0;
   }

   function valid()
   {
      $dummy = array_keys($this->mCompound);
      if(isset($dummy[$this->currentIndex])){
	      if($this->offsetExists($dummy[$this->currentIndex])) {
	            return true;
	      } else {
	            return false;
	      }
      }else 
      	return false;
   }

   function append($value)
   {
      $this->mCompound[] = $value;
   }

   function getIterator()
   {
      return $this;
   }
   //EndRegion
}

// init
$_GET = SanitizerFactory::Wrap($_GET);
SysLog::Log('initializing Sanitizer factory for $_POST: '.print_r($_POST, true), 'sanitizer');
$_POST = SanitizerFactory::Wrap($_POST);
$_COOKIE = SanitizerFactory::Wrap($_COOKIE);
$_REQUEST = SanitizerFactory::Wrap($_REQUEST);
?>