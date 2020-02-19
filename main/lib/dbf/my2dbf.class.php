<?php

/**
 * My2DBF
 * 
 * @package 
 * @author haris (haris@gamatechno.com)
 * @copyright Copyright (c) 2005
 * @version $0.1$
 * @access public
 * @modified by roby@gamatechno.com, date 2009-08-11 for sia framework
 * @modified by roby@gamatechno.com, date 2011-04-18 for gtfw3  
 **/
 
class My2DBF
{
   var $mDbName;
   var $mDbId;
   var $mPathFile;
   var $mTime;
   
   var $mDef;
   
   function My2DBF($path, $file_name) {
      $this->mPathFile = $path;
      $this->setDbName($file_name);
   }
   
   function CreateDbfFromDef($def) {
      $this->mDef = $def;
      $this->createDBF();
   }
   
   function getTime() {
      return $this->mTime;   
   }
   
   /**
    * My2DBF::setPathFile()
    * method untuk mengubah/menge-set alamat folder diekspor
    * secara default akan diekspor ke direktori "exported_dbf"
    * 
    * @param string $path_file
    * @return 
    **/
   function setPathFile($path_file)
   {
      $this->mPathFile = $path_file;
   }
   
   function getPathFile()
   {
      return $this->mPathFile;
   }
   
   /**
    * My2DBF::setFileName()
    * 
    * @param $file_name
    * @return 
    **/
   function setDbName($file_name)
   {
      $this->mDbName = $this->mPathFile.$file_name;
   }
   
   function getDbName()
   {
      return $this->mDbName;
   }
   
   function createDBF()   
   {
      $this->mDbId = dbase_create($this->mDbName, $this->mDef);
      
      if (!$this->mDbId) 
      {
         return "<strong>Error Creating DBF Database</strong>";
      }
      
      $this->closeDb();
   }
   
   function openDb()
   {
      $this->mDbId = dbase_open($this->mDbName, 2);   
   }
   
   function closeDb()
   {
      dbase_close($this->mDbId);
   }
   
   function addRecord($records)
   {
      $this->openDb();
      
      foreach ($records as $record)
      {
         dbase_add_record($this->mDbId, $record);
      }
         
      $this->closeDb();
   }
}

?>