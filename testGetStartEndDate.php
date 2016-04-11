<?php
   include 'CoreFunctions.php';
   // print_r(getStartEndDate());
   // echo exec('whoami');
   $file = "downloadStatus";
   writeDownloadStatus($file, 5);
   $stat = readDownloadStatus($file);
   if($stat < 6 && $stat > 4)
      echo "true";

?>