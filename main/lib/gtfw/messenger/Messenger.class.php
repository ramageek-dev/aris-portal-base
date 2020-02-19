<?php

class Messenger
{
   const CurrentRequest = 0;
   const NextRequest = 1;
   const UntilFetched = 2;

   static $mrInstance = NULL;

   private $mrMessageQueue;

   private function __construct()
   {
   	
      if (!isset($_SESSION['messenger_queue']))
      {
         $this->mrMessageQueue = new MessageQueue();
      }
      else
      {
         $this->mrMessageQueue = unserialize($_SESSION['messenger_queue']);
      }

      // reflect changes to session immediately
      $this->mrMessageQueue->MarkForNextRequest();
      $_SESSION['messenger_queue'] = serialize($this->mrMessageQueue);
   }

   public function __destruct()
   {

      // reflect changes
      $this->mrMessageQueue->CleanMessage();
      $_SESSION['messenger_queue'] = serialize($this->mrMessageQueue);
   }

   private function DoSend($module, $subModule, $act, $type, $name, $message, $timeToFetch = self::NextRequest)
   {
   	
      list($path, $class_name) = Dispatcher::Instance()->GetModule($module, $subModule, $act, $type);
      $name = "_$name";
      $class_name.= $name;
      $path.= $name;

      if (!$path)
      {

         return FALSE;
      }
      else
      {
         $this->mrMessageQueue->PutMessage($path, $message, $timeToFetch);

         // awkward compatibility, use with your own risk
         // soon will be obsolete

         #$this->mrMessageQueue->PutMessage($class_name, $message, $timeToFetch);

         //

         // reflect changes to session immediately

         $_SESSION['messenger_queue'] = serialize($this->mrMessageQueue);

         return TRUE;
      }
   }

   public function Send($module, $subModule, $act, $type, $message, $timeToFetch = self::NextRequest)
   {

      return $this->DoSend($module, $subModule, $act, $type, '', $message, $timeToFetch);
   }

   public function SendToComponent($module, $subModule, $act, $type, $name, $message, $timeToFetch = self::NextRequest)
   {

      return $this->DoSend($module, $subModule, $act, $type, $name, $message, $timeToFetch);
   }

   // calling Receive(__FILE__) is preferable than Receive($this)
   // set $name to component's name via $this->mComponentName to receive message sent to component

   // ex. Messenger::Instance()->Receive(__FILE__, $this->mComponentName)


   public function Receive($receiver, $name = '')
   {
      $receiver = (is_object($receiver)) ? get_class($receiver) : str_replace('\\', '/', $receiver);
      $receiver.= "_$name";
      $msg = $this->mrMessageQueue->GetMessage($receiver);

      // reflect changes to session immediately
      $_SESSION['messenger_queue'] = serialize($this->mrMessageQueue);

      return $msg;
   }

   public function MessageCount($receiver = NULL, $name = '')
   {

      if ($receiver)
      {
         $receiver = (is_object($receiver)) ? get_class($receiver) : str_replace('\\', '/', $receiver);
         $receiver.= "_$name";
      }

      return $this->mrMessageQueue->MessageCount($receiver);
   }

   public
   static function &Instance()
   {

      if (self::$mrInstance == NULL)
      {
         $class_name = __CLASS__;
         self::$mrInstance = new $class_name();
      }

      return self::$mrInstance;
   }
}

// init
Messenger::Instance();
?>