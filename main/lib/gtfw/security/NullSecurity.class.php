<?php
final class NullSecurity {
   public function __call($name, $args) {
      SysLog::Instance()->Log('Warning: Attempting to access security objects while security is off! Command was ' . $name . '.', 'security');
      return $this;
   }

   public function __get($name) {
      SysLog::Instance()->Log('Warning: Attempting to access security objects while security is off! Command was ' . $name . '.', 'security');
      return $this;
   }

   public function __set($name, $value) {
      SysLog::Instance()->Log('Warning: Attempting to access security objects while security is off! Command was ' . $name . '.', 'security');
   }

   public function __toString() {
      return 'NullSecurity Object';
   }
}
?>