<?php
	require_once Configuration::Instance()->GetValue('application', 'gtfw_base') . 'main/lib/gtfw/log/Loger.intf.php';
	
	class LogDb extends Database implements loger
	{
	
		function __construct ($connectionNumber = 0)
		{	
			$this->mSqlFile = Configuration::Instance()->GetValue('application', 'gtfw_base') .
         'main/lib/gtfw/log/sql/mysqlt/log.sql.php'; #FIXME change mysqlt into value from config
      	parent::__construct($connectionNumber);
		}
		
		public function send($user,$ip,$log){
			$result =  $this->execute($this->mSqlQueries['send_log'],array($user,$ip,$log));
			return $result;
		}
	
	}
?>
