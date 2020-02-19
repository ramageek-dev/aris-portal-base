<?php
/*
 CREATE TABLE `gtfw_session_table` (
  `SessionId` varchar(64) NOT NULL,
  `SessionData` longtext NOT NULL,
  `SessionCTime` timestamp NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`SessionId`)
 ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
*/

$sql["read_data"] = "SELECT * FROM gtfw_session_table WHERE (SessionId = '%s')";

$sql["insert_data"] = "INSERT INTO gtfw_session_table (SessionId, SessionData, SessionCTime) VALUES('%s', '%s', '%s')";

$sql["update_data"] = "UPDATE gtfw_session_table SET SessionData = '%s', SessionCTime = '%s' WHERE (SessionId = '%s')";

$sql["delete_data"] = "DELETE FROM gtfw_session_table WHERE (SessionId = '%s')";

$sql["gc_data"] = "DELETE FROM gtfw_session_table WHERE (SessionId = '%s') AND (UNIX_TIMESTAMP('%s') - UNIX_TIMESTAMP(SessionCTime) > %s)";
?>