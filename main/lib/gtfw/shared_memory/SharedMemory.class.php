<?php
class SharedMemory {
   private static $mrInstance;
   private $_SHM_AC_ = 0644;
   private $_SHM_SIZE_ = 1024;

   public function Instance() {
      if (!isset(self::$mrInstance))
         self::$mrInstance = new SharedMemory();

      return self::$mrInstance;
   }

   private function __construct() {

      /* if wndows *//*
      function ftok($pathname, $proj_id) {
         $st = @stat($pathname);
         if (!$st) {
            return -1;
         }

         $key = sprintf("%u", (($st['ino'] & 0xffff) | (($st['dev'] & 0xff) << 16) | (($proj_id & 0xff) << 24)));
         return $key;
      */
      $shm_key = ftok(__FILE__, 't');
      $this->mShmId = shmop_open($shm_key, 'ac', 0, 0);
      if ($this->mShmId) {
         #it is already created
         //echo '#it is already created';
      } else {
         #you need to create it with shmop_open using "c" only
         //echo '#you need to create it with shmop_open using "c" only';
         $this->mShmId = shmop_open($shm_key, 'c', $this->_SHM_AC_, $this->_SHM_SIZE_);
      }
      $this->mShmId = shmop_open($shm_key, 'w', 0, 0);
      //echo 'ShmId:'.$this->mShmId;
      $this->ShmIsClean = false;
   }

   /*private function open( int $key, string $flags, int $mode, int $size ) {
      shm_open($key, $flags);
   }*/

   private function Close() {
      shmop_close($this->mShmId);
   }

   private function Read() {
      if ($this->ShmIsClean) {
         //echo "reading cache: ".print_r($this->mShmCache, true)."<br/>";
         return $this->mShmCache;
      } else {
         $this->ShmIsClean = true;
         $this->mShmCache = unserialize(shmop_read($this->mShmId, 0, $this->_SHM_SIZE_));
         //echo "reading: ".print_r($this->mShmCache, true)."<br/>";
         if ($this->mShmCache == false)
            $this->mShmCache = array('gtfw' => '');
         return $this->mShmCache;
      }
   }

   private function Write($data) {
      $tmp = serialize($data);
      $this->ShmIsClean = false;
      //echo "writing $tmp<br/>";
      return shmop_write($this->mShmId, $tmp, 0) == strlen($tmp)?true: false;
   }

   private function Size() {
      return shmop_size($this->mShmId);
   }

   private function Delete() {
      return shmop_delete($this->mShmId);
   }

   function VarIsSet($key) {
      $tmp = $this->Read();
      if (isset($tmp['gtfw'][$key]))
         return true;
      else
         return false;
   }

   function Get($key) {
      $tmp = $this->Read();
      return $tmp['gtfw'][$key];
   }

   function Set($key, $data) {
      $tmp = $this->Read();
      $tmp['gtfw'][$key] = $data;
      $this->write($tmp);
   }
}
?>