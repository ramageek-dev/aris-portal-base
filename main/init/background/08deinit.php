<?php
unregister_tick_function('flush_output');

// save result
$output = ob_get_contents();
rewind($ob_file);
fwrite($ob_file, $output);

// release lock, indicating child is terminating
flock($ob_file, LOCK_UN);
fclose($ob_file);

ob_end_clean();
?>
