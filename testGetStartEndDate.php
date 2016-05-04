<?php
   include 'CoreFunctions.php';
   include 'graphFunctions.php';
   
   $fileName = 'users_out.csv';

   $updateFile = "csv/" . $fileName;
   // echo $updateFile;
   
   if(file_exists($updateFile))
      echo "file exists";
   else 
      echo "file not found";
?>