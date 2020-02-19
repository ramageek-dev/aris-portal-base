<?php

   class ConfigurationDb extends Database {
   	static $mrInstance;
   	
      function __construct($connectionNumber = 0) {
      	$type = Configuration::Instance()->GetValue('application', 'db_conn','0','db_type');
      	if($type=="mysqli")
      		$type="mysqlt";
         $this->mSqlFile = Configuration::Instance()->GetValue('application', 'gtfw_base') .
            'main/lib/gtfw/configuration/adodb/'.$type.'/configuration.sql.php';
            
         parent::__construct($connectionNumber);
      }
      
   	#gtfwMethodOpen
   	public function GetAllTables(){
   		$result = $this->Open($this->mSqlQueries['get_all_table'], array());
   		return $result;
   	}
   	
   	#gtfwMethodOpen
   	public function CheckGtfwMenuTable(){
   		if(!isset($_SESSION['check_gtfw_menu_table'])){
   			$result = $this->Open($this->mSqlQueries['check_gtfw_menu_table'], array());
   			$_SESSION['check_gtfw_menu_table']=$result;
   		}else
   			$result=$_SESSION['check_gtfw_menu_table'];
   		return empty($result)?false:true;
   	}
   	
   	#gtfwMethodOpen
   	public function GetAllConfig(){
   		$result="";
   		if(!isset($_SESSION['table_gtfw_config_available'])){
   			$result =  $this->open($this->mSqlQueries['check_table_gtfw_config'],array());
   			$_SESSION['table_gtfw_config_available']=$result;
   		}else{
   			$tableAvailable=$_SESSION['table_gtfw_config_available'];
   		}
   		if(empty($tableAvailable))
   			$result =  $this->execute($this->mSqlQueries['create_table_gtfw_config'],array());
   			//no need get all config
//    		if($result){
//    			#gtfwDbOpen
//    			$result =  $this->open($this->mSqlQueries['get_config_from_db'],array());
//    		}
   		
   		return $result;
   	}
   	
   	static function Instance() {
   		if (!isset(self::$mrInstance))
   			self::$mrInstance = new ConfigurationDb();
   	
   		return self::$mrInstance;
   	}
   }

?>
