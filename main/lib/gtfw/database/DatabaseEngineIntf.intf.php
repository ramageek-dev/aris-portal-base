<?php
interface DatabaseEngineIntf {
   // connect/disconnect
   public function Connect();
   public function Disconnect();

   // transaction
   public function StartTrans();
   public function EndTrans($condition);

   // database operation
   public function Open($sql, $params, $varMarker = NULL);
   public function OpenCache($sql, $params, $varMarker = NULL);
   public function Execute($sql, $params, $varMarker = NULL);
   public function AffectedRows();
   public function LastInsertId();

   // deprecated but still supported
   public function ExecuteDeleteQuery($sql, $params);
   public function ExecuteUpdateQuery($sql, $params);
   public function ExecuteInsertQuery($sql, $params);
   public function GetAllDataAsArray($sql, $params);

   // misc.
   public function SetDebugOn();
   public function SetDebugOff();
   public function GetLastError();
}
?>