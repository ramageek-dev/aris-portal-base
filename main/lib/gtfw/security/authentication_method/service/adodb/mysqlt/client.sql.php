<?php
	$sql['get_client_app_by_address']="
	SELECT
	   `ApplicationId`,
	   `ApplicationName`,
	   `ApplicationAddress`,
	   `Description`,
	   `ApplicationOwner`,
	   `ApplicationStatusAktif`
	FROM `gtfw_application`
	WHERE 
		 MD5(CONCAT(`ApplicationId`,`ApplicationAddress`)) = '%s'
	AND(
		ApplicationAddress='%s'
	)
	AND
		ApplicationStatusAktif = 'Y'
	";
?>