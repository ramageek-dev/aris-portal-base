<?php

header('Content-Type: application/javascript');
header("Expires: Mon, 15 Jun 2020 05:00:00 GMT");

$js_file = str_replace('\\', '/', realpath(GTFW_BASE_DIR . 'main/js'));

if(isset($_GET["js"])){
   $file = $_GET["js"];
   echo file_get_contents($js_file . "/" . $file .".js");
}else{
   $dir = scandir($js_file);
   foreach ($dir as $key => $value) {
      if(!is_dir($value)){
         $value  = str_replace(".js", "", $value);
         echo "document.writeln(\"<script type='text/javascript' src='js/gtfw_js.php?js=".$value."'></script>\");";
      }
   }
}
?>