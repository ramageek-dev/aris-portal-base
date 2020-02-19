<?php
	$sql['send_log']="
		INSERT INTO gtfw_log 
			(
				gtfwLogUserName, 
				gtfwLogIp, 
				gtfwLogLog, 
				gtfwLogDate, 
				gtfwLogTime
			)
			VALUES
			(
				'%s', 
				'%s', 
				'%s', 
				DATE(NOW()), 
				TIME(NOW())
			);

	";
?>
