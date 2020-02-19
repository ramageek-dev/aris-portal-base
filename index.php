<?php

$gtfw_init_dir = GTFW_BASE_DIR . 'main/init/' . basename(__FILE__, '.php') . '/*.php';

foreach (glob($gtfw_init_dir) as $value) {
	require_once $value;
}

?>
