<?php

class Message
{

   private $mRequestId;

   private $mMessage;

   private $mOld = false;

   public function __construct($requestId, $message)
   {
      $this->mRequestId = $requestId;
      $this->mMessage = $message;
   }

   public function GetRequestId()
   {

      return $this->mRequestId;
   }

   public function GetMessage()
   {

      return $this->mMessage;
   }

   public function SetOldStatus()
   {
      $this->mOld = true;
   }

   public function GetOldStatus()
   {

      return $this->mOld;
   }
}
?>