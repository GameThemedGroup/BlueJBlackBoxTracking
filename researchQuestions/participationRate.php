<?php
   include "../CoreFunctions.php";
   include "../graphFunctions.php";

   // $conn = connectToLocal($db);

   $useridList = restoreFromFile($useridFile);

   print_r($useridList);
   
?>