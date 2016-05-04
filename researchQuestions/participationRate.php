<?php
   include "../CoreFunctions.php";
   include "../graphFunctions.php";

   $useridList = restoreFromFile($useridFile);

   print_r($useridList);   

?>