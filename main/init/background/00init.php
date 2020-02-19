<?php
if (strtolower(substr(php_sapi_name(), 0, 3)) != 'cli') {
   echo 'You can only run this script through command line interface!';
   die;
}

// send pid back to parent process
echo getmypid() . "\n";

// change directory to docroot
chdir(dirname(GTFW_APP_DIR));

// parsing parameters
$process_info_file = $argv[1];
$output_buffer_file = $argv[2];

// obtaining lock for pif file, indicating this child process is running
$ob_file = @fopen($output_buffer_file, 'wb');
if ($ob_file)
   flock($ob_file, LOCK_EX);

// parsing pif file, get parameters
$str_pif = @file_get_contents($process_info_file);
list($module, $submodule, $action, $type, $serialized_params, ) = explode("\n", $str_pif);

// start buffering, all result is sent to $output_buffer_file
ob_start();

function flush_output() {
   $output = ob_get_contents();
   rewind($GLOBALS['ob_file']);
   fwrite($GLOBALS['ob_file'], $output);
   fflush($GLOBALS['ob_file']);
}

register_tick_function('flush_output');
declare(ticks = 3);

?>