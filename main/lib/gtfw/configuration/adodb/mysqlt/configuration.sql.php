<?php
   $sql['get_all_table']="
      SHOW TABLES;
   ";
   
   $sql['check_table_gtfw_config']="
   	SHOW TABLES LIKE 'gtfw_config';
   ";
   
   $sql['create_table_gtfw_config']="
   	CREATE TABLE IF NOT EXISTS gtfw_config (
		   configKode VARCHAR (20) NOT NULL PRIMARY KEY,
		   configName VARCHAR (50) NOT NULL,
		   configValue VARCHAR (255), 
		   codeUserId BIGINT NOT NULL
		) ENGINE = MEMORY ;
   ";
   
   $sql['get_config_from_db']="
   	SELECT
		   `configKode`,
		   `configName`,
		   `configValue`
		FROM `gtfw_config`
   ";
   
   $sql['check_gtfw_menu_table']="
   	SHOW TABLES LIKE 'gtfw_menu';
   ";
?>
