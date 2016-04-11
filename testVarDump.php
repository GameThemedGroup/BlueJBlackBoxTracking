<?php
   //var_dump($argv);
   if(count($argv) == 3){
      $argument1 = $argv[1];
      $argument2 = $argv[2];
      echo $argument1 . "\n" . $argument2 . "\n";
   } else {
      echo "No arguments passed\n";
   }
   
?>