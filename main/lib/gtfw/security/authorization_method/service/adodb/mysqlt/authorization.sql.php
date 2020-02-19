<?php
	$sql['allowed_to_access']="
		SELECT
		   COUNT(*) access_right
		FROM `gtfw_module`
		WHERE 
			(
				(ApplicationId='%s'
					AND 
				`Access` = 'Exclusive')
				OR `Access` = 'All'
			)
		AND 
			Module='%s'
		AND 
			SubModule='%s'
		AND 
			`Action`= '%s'
		AND 
			`Type` = '%s'
	";
?>