<?php
class BackgroundProcess {
   // deprecated
   //const Terminated = 0;
   //const Running = 1;

   const TERMINATED = 0;
   const RUNNING = 1;

   private $mIsRunable = FALSE;

   private $mProcessId = NULL;

   private $mOutBuffer = NULL;

   private $mRealProcessId = NULL;

   private $mProcessInfoFile = NULL;

   private $mModule = NULL;

   private $mSubModule = NULL;

   private $mAct = NULL;

   private $mType = 'cli'; // always cli

   private $mCommandString = NULL;

   private $mStatus = BackgroundProcess::TERMINATED;

   private $mSerializedParams = NULL;

   private $mProcessSavePath = NULL;

   // note: $module, $subModule, $action isn't used upon resuming a process
   // but it is still a required parameters!
   public function __construct($module, $subModule, $action, $processId = NULL) {
      list($this->mModule, $this->mSubModule, $this->mAct) = array($module, $subModule, $action);

      if (!$processId) { // new process
         SysLog::Log('Creating new child process...', 'BgProcess');
      } else { // resume
         SysLog::Log('Resuming child process, PID: ' . $processId, 'BgProcess');
         $this->mProcessId = $processId;
         $this->Resume();
      }

      // we use Dispatcher class here
      $this->mIsRunable = (bool) current(Dispatcher::Instance()->GetModule($this->mModule, $this->mSubModule, $this->mAct, $this->mType));
      $this->mStatus = $this->GetStatus();

      $this->mProcessSavePath = Configuration::Instance()->GetValue('background_process', 'process_save_path') != '' ? Configuration::Instance()->GetValue('background_process', 'process_save_path') : Configuration::Instance()->GetTempDir() . '/';
   }

   private function GenerateProcessId() {
      SysLog::Log('Generating process id...', 'BgProcess');

      $result = md5(uniqid(rand(), TRUE));

      SysLog::Log('Got process id: ' . $result, 'BgProcess');

      return $result;
   }

   private function ExtractPifFile() {
      SysLog::Log('Extracting process info file...', 'BgProcess');

      $pif_str = @file_get_contents($this->mProcessInfoFile);

      SysLog::Log('Got process info: ' . $pif_str, 'BgProcess');

      if (!$pif_str) {
         return FALSE;
      } else {
         return explode("\n", $pif_str);
      }
   }

   // prepare for running or resuming a child process
   private function Prepare() {
      SysLog::Log('Configuring child process...', 'BgProcess');

      Configuration::Instance()->Load('background_process.conf.php');

      $this->mOutBuffer = $this->mProcessSavePath .
         $this->mProcessId . '.out';
      $this->mProcessInfoFile = $this->mProcessSavePath .
         $this->mProcessId . '.pif';
   }

   private function Resume() {
      SysLog::Log('Do resuming child process...', 'BgProcess');

      $this->Prepare();

      // for consistency purpose, resuming a child process indirectly override
      // $module, $subModule, $action params
      list($this->mModule, $this->mSubModule,
         $this->mAct, $this->mType, $this->mSerializedParams,
         $this->mCommandString, $this->mRealProcessId) = $this->ExtractPifFile();
   }

   public function Run() {
      if (!$this->mIsRunable)
         return FALSE;

      SysLog::Log('Running child process...', 'BgProcess');

      // prevents running twice, until terminated
      if ($this->GetStatus() == BackgroundProcess::RUNNING) {
         SysLog::Log('No, you can\'t run a running process while it\'s running', 'BgProcess');
         return $this->mProcessId;
      }

      // assigns new process id
      $this->mProcessId = $this->GenerateProcessId();
      $this->Prepare();

      $this->mCommandString = ' -f ' . escapeshellarg(Configuration::Instance()->GetValue('application', 'docroot') . 'background.php') .
         ' -- ' . escapeshellarg($this->mProcessInfoFile) . ' ' . escapeshellarg($this->mOutBuffer) . ' 2>&1';

      if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') { // windows family, preferably 2000 & XP
         $this->mCommandString = Configuration::Instance()->GetValue('background_process', 'win_start_exe') .
            ' /b "gtfw_bg_process" ' . escapeshellarg(str_replace('/', '\\', Configuration::Instance()->GetValue('background_process', 'php_cli'))) .
            $this->mCommandString;
      } else { // assumes *nix family
         $this->mCommandString = escapeshellcmd(Configuration::Instance()->GetValue('background_process', 'php_cli')) .
            $this->mCommandString . ' &';
      }

      SysLog::Log('Executing: ' . $this->mCommandString, 'BgProcess');

      // put child process info
      @file_put_contents($this->mProcessInfoFile, $this->mModule . "\n" . $this->mSubModule . "\n" .
            $this->mAct . "\n" . $this->mType . "\n" . $this->mSerializedParams . "\n" .
            $this->mCommandString . "\n");

      $process = popen($this->mCommandString, 'r');
      $this->mRealProcessId = trim(fgets($process)); // get child process id
      pclose($process);

      // put child process id & result part sequence number (always starts from 0)
      @file_put_contents($this->mProcessInfoFile, $this->mRealProcessId . "\n" . '0', FILE_APPEND);

      // updating status
      $this->mStatus = $this->GetStatus();

      return $this->mProcessId;
   }

   // i can't terminate child process in windows, why?
   public function Terminate() {
      if ($this->GetStatus() == BackgroundProcess::TERMINATED)
         return TRUE;

      SysLog::Log('Terminating child process...', 'BgProcess');

      if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') { // windows family
         $exe = Configuration::Instance()->GetValue('background_process', 'win_kill_exe');
         if (file_exists($exe)) {
            exec(sprintf($exe . ' ' . Configuration::Instance()->GetValue('background_process', 'win_kill_args'), $this->mRealProcessId), $output, $result);
            SysLog::Log('Terminating child process using ' . $exe .
               ': got ' . implode("\n", $output) . ', result ' . $result, 'BgProcess');
         }
      } else { // assumes *nix family
         $killed = FALSE;
         if (function_exists('posix_kill')) { // preferred way
            if (!defined('SIGKILL'))
               define('SIGKILL', 9); // assumes sigkill constant = 9
            if (posix_kill($this->mRealProcessId, SIGKILL)) {
               SysLog::Log('Terminating child process using posix_kill(): succeded', 'BgProcess');
               $killed = TRUE;
            } else {
               SysLog::Log('Terminating child process using posix_kill(): failed', 'BgProcess');
            }
         }
         // alternative way
         $bin = Configuration::Instance()->GetValue('background_process', 'unix_kill_bin');
         if (file_exists($bin) && !$killed) {
            exec(sprint_f($bin . ' ' . Configuration::Instance()->GetValue('background_process', 'unix_kill_args'), $this->mRealProcessId), $output, $result);
            SysLog::Log('Terminating child process using ' . $bin .
               ': got ' . implode("\n", $output) . ', result ' . $result, 'BgProcess');
         }
      }

      $this->mStatus = $this->GetStatus();

      return ($this->mStatus == BackgroundProcess::TERMINATED);
   }

   public function IsRunable() {
      SysLog::Log('Is child process runable? ' . print_r($this->mIsRunable, TRUE), 'BgProcess');

      return $this->mIsRunable;
   }

   public function IsOutputValid() {
      // output valid for 2 hours only
      $result = (@filemtime($this->mOutBuffer) + (60 * 60 * 2)) >= time();

      SysLog::Log('Is process output valid? ' . print_r($result, TRUE), 'BgProcess');

      return $result;
   }

   public function GetStatus() {
      SysLog::Log('Getting child process status...', 'BgProcess');

      if (!file_exists($this->mProcessInfoFile) && !file_exists($this->mOutBuffer)) {
         SysLog::Log('Terminated: not yet running', 'BgProcess');
         $result = BackgroundProcess::TERMINATED;
      } else {
         if ($this->mRealProcessId != '' && intval($this->mRealProcessId) > -1) {
            // doing trick, waiting output buffer to be ready
            $i = 0;
            $file_exists = file_exists($this->mOutBuffer);
            while (!$file_exists && $i < 5) { // wait and check for a total of ~0.5 seconds
               usleep(100000); // wait for 0.1 seconds, reducing access to disk, but not to increase performance
               $file_exists = file_exists($this->mOutBuffer);
               $i++;
            }

            if ($file_exists) {
               SysLog::Log('Got output buffer at ' . $i . ' attempt(s)!', 'BgProcess');
               $file = @fopen($this->mOutBuffer, 'r');
               if ($file) {
                  // we can determine whether the child process is running by obtaining
                  // an exclusive lock. if can't have the lock, then it is assumed to be
                  // running, otherwise it is terminated
                  // warning: ensure that you're using the same php engine! see php's manual
                  // on flock() for further information
                  if (!flock($file, LOCK_EX + LOCK_NB)) {
                     SysLog::Log('Running: output buffer file locked!', 'BgProcess');
                     $result = BackgroundProcess::RUNNING;
                  } else {
                     SysLog::Log('Terminated: output buffer file released!', 'BgProcess');
                     $result = BackgroundProcess::TERMINATED;
                  }
                  fclose($file);
               } else {
                  // grey area
                  SysLog::Log('Running: but output buffer file cannot be opened!', 'BgProcess');
                  $result = BackgroundProcess::RUNNING;
               }
            } else {
               // grey area
               SysLog::Log('Running: got no output buffer at ' . $i . ' attempt(s), an error might has been occured!', 'BgProcess');
               $result = BackgroundProcess::RUNNING;
            }
         } else {
            // grey area
            SysLog::Log('Terminated: not yet running or failed to run', 'BgProcess');
            $result = BackgroundProcess::TERMINATED;
         }
      }

      return $result;
   }

   public function SerializeParams() {
      SysLog::Log('Serializing parameters...', 'BgProcess');

      $this->mSerializedParams = serialize(func_get_args());
   }

   public function GetOutput() {
      // output valid for 2 hours only
      if ($this->GetStatus == BackgroundProcess::TERMINATED && !$this->IsOutputValid()) {
         return NULL;
      }

      if (file_exists($this->mOutBuffer)) {
         $result = file_get_contents($this->mOutBuffer);
         if ($result !== FALSE)
            return $result;
      }

      return NULL;
   }
}
?>