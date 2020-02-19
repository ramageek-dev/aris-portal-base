<?php

class UploadHelper {
   private $mAcceptedFileTypes = array();
   private $mLimitFileSize = -1;
   private $mFiles = array();
   private $mFileNameFormatter;
   private $mFileNameFormatterClassContext;

   public function __construct($fieldName) {
      SysLog::Log('FILES: '.print_r($_FILES, true), 'uploadhelper');
      if (isset($_FILES[$fieldName])) {
         SysLog::Log('We ahve got files!', 'uploadhelper');
         if (!is_array($_FILES[$fieldName]["error"])) {
            SysLog::Log('Single upload: ' . $fieldName, 'uploadhelper');

            $tmp_name = $_FILES[$fieldName]["tmp_name"];
            $name = $_FILES[$fieldName]["name"];
            $type = $_FILES[$fieldName]["type"];
            $size = $_FILES[$fieldName]["size"];

            if (!empty($this->mAcceptedFileTypes))
               $mime_compliance = in_array($type, $this->mAcceptedFileTypes);
            else
               $mime_compliance = true;

            if ($this->mLimitFileSize != -1)
               $size_compliance = $size <= $this->mLimitFileSize;
            else
               $size_compliance = true;

            $compliance = $mime_compliance && $size_compliance;

            $this->mFiles[] = array('name' => $name, 'tmp_name' => $tmp_name, 'type' => $type, 'size' => $size, 'compliance' => $compliance);
         } else {
            SysLog::Log('Multiple upload: ' . $fieldName, 'uploadhelper');

            foreach ($_FILES[$fieldName]["error"] as $key => $error) {
               if ($error == UPLOAD_ERR_OK) {
                  $tmp_name = $_FILES[$fieldName]["tmp_name"][$key];
                  $name = $_FILES[$fieldName]["name"][$key];
                  $type = $_FILES[$fieldName]["type"][$key];
                  $size = $_FILES[$fieldName]["size"][$key];

                  if (!empty($this->mAcceptedFileTypes))
                     $mime_compliance = in_array($type, $this->mAcceptedFileTypes);
                  else
                     $mime_compliance = true;

                  if ($this->mLimitFileSize != -1)
                     $size_compliance = $size <= $this->mLimitFileSize;
                  else
                     $size_compliance = true;

                  $compliance = $mime_compliance && $size_compliance;

                  $this->mFiles[] = array('name' => $name, 'tmp_name' => $tmp_name, 'type' => $type, 'size' => $size, 'compliance' => $compliance);
               }
            }
         }
      }
   }

   function AcceptFileType($mime) {
      $this->mAcceptedFileTypes[] = $mime;
   }

   function LimitFileSize($value) {
      $this->mLimitFileSize = $value;
   }

   function IsUploading() {
      SysLog::Log('mFiles count: ' . count($this->mFiles), 'uploadhelper');
      return count($this->mFiles)>0;
   }

   function GetFiles() {
      return $this->mFiles;
   }

   function FileCount() {
      return count($this->mFiles);
   }

   /**
    * Processes all files and returns array containing all processed files,
    * ie: processable and non processable
    * this is a default action that can be used. As requirement progress,
    * we'll see how we should handle things
    * @param $final_destination_folder string
    * @return array
    */
   function ProcessFiles($final_destination_folder) {
      $files = $this->GetFiles();

      SysLog::Log('Processing ' .count($files). ' uploads', 'uploadhelper');

      foreach($files as $file) {
         SysLog::Log('Processing upload: ' . $file['name'], 'uploadhelper');
         if ($file['compliance']) {
            SysLog::Log('Moving upload: ' . $file['name'], 'uploadhelper');
            $fname = $this->GetFileNameFor($file['name']);
            move_uploaded_file($file['tmp_name'], $final_destination_folder.'/'.$fname);
            $success[] = $file['name'];
         } else {
            $failed[] = $file['name'];
         }
      }

      return array('success' => $success, 'failed' => $failed);
   }

   /**
    * Let user use his/her own filename formatter
    * @param $class_object class object where the function reside
    * @param $func_name function name to call. It must have this signature func($fname) and must return String
    */
   public function SetFileNameFormatter(&$class_object, $func_name) {
      $this->mFileNameFormatter = $func_name;
      $this->mFileNameFormatterClassContext = $class_object;
   }

   /**
    * Provide custom filename formatter
    * @param $fname original filename
    */
   protected function GetFileNameFor($fname) {
      if (isset($this->mFileNameFormatter) && isset($this->mFileNameFormatterClassContext))
         $new_fname = call_user_func(array($this->mFileNameFormatterClassContext, $this->mFileNameFormatter), $fname);
      else
         $new_fname = $fname;
      return $new_fname;
   }
}
?>