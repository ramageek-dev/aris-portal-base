<?php

class MessageQueue
{
   const CurrentRequest = 0;
   const NextRequest = 1;
   const UntilFetched = 2;

   private $mCurrentRequestId = 0;

   private $mNextRequestId = 0;

   private $mMessages = array();

   public function __construct()
   {

      if ($this->mCurrentRequestId == 0 && $this->mNextRequestId == 0)
      {
         $this->mCurrentRequestId = 1;
         $this->mNextRequestId = 2;
      }
      else
      {
         $this->__wakeup();
      }
   }

   public function __wakeup()
   {
      $this->mCurrentRequestId = $this->mNextRequestId;
      $this->mNextRequestId++;
   }

   public function GetMessage($receiver)
   {
      $message = array();

      if (!empty($this->mMessages) && isset($receiver)) {
         if(isset($this->mMessages[$receiver])){
            if (count($this->mMessages[$receiver]) > 0)
            {
               $keys = array_keys($this->mMessages[$receiver]);
      
               foreach($keys as $k => $v)
               {
                  $msg = $this->mMessages[$receiver][$v];
      
                  if ($msg->GetRequestId() == $this->mCurrentRequestId || $msg->GetRequestId() == 0)
                  {
                     $message[] = $msg->GetMessage();
                     unset($this->mMessages[$receiver][$v]);
                  }
                  elseif ($msg->GetRequestId() < $this->mCurrentRequestId && $msg->GetRequestId() != 0)
                  {
                     unset($this->mMessages[$receiver][$v]);
                  }
               }
            }
         }
      }

      return $message;
   }

   public function PutMessage($receiver, $message, $timeToFetch = self::NextRequest)
   {

      // defaults to next request
      $request_id = $this->mNextRequestId;

      if ($timeToFetch == self::CurrentRequest) // for current request?

      $request_id = $this->mCurrentRequestId;

      if ($timeToFetch == self::UntilFetched) // until fetched?

      $request_id = 0; // special case

      $message = new Message($request_id, $message);
      $this->mMessages[$receiver][] = $message;
   }

   public function MessageCount($receiver = NULL)
   {

      if (isset($receiver))
      {

         return count($this->mMessages[$receiver]);
      }

      return count($this->mMessages, COUNT_RECURSIVE) - count($this->mMessages);
   }

   public function CleanMessage()
   {

      foreach($this->mMessages as $key => $value)
      {

         if (empty($this->mMessages[$key])) unset($this->mMessages[$key]);
         else
         {
            $msg = $this->mMessages[$key];

            foreach($msg as $k => $v)
            {

               if ($msg[$k]->GetOldStatus())
               {
                  unset($this->mMessages[$key][$k]);
               }
            }
         }
      }
   }

   public function MarkForNextRequest()
   {

      foreach($this->mMessages as $key => $value)
      {
         $msg = $this->mMessages[$key];

         if (!empty($msg))
         {

            foreach($msg as $k => $v)
            {
               $msg[$k]->SetOldStatus();
            }
         }
      }
   }
}
?>
