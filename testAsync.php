<?php
   include 'CoreFunctions.php';
   date_default_timezone_set("America/Los_Angeles"); 

   $servername = "127.0.0.1";
   $username = "root";
   $password = "26064228";
   $database = "capstoneLocal";

   //Establish queries
   $query1 = "select * from master_events";
   $query2 = "select * from intval(var)vocations";
   $query3 = "select * from users";
   $query4 = "select * from sessions";
   $query5 = "select * from compile_outputs";
   $query6 = "select * from source_files";
   $query7 = "select * from stack_entries";
   $query8 = "select * from bench_objects";
   $query9 = "select * from breakpoints";

   //Sequential
   echo "Seque Start queries: " . date("Y-m-d h:i:s:a") . "<br>";

   $link = new mysqli($servername, $username, $password, $database);
   if($link->connect_error){
      die("Link FAILEDDDDDDDDD " . $conn->connect_error);
   }

   $allResults = array();

   $result = $link->query($query1);
   // printResultInTable($result);
   // mysqli_free_result($result);
   array_push($allResults, $result);
   
   $result = $link->query($query2);
   // printResultInTable($result);
   // mysqli_free_result($result);
   array_push($allResults, $result);

   $result = $link->query($query3);
   // printResultInTable($result);
   // mysqli_free_result($result);
   array_push($allResults, $result);

   $result = $link->query($query4);
   // printResultInTable($result);
   // mysqli_free_result($result);
   array_push($allResults, $result);
   
   $result = $link->query($query5);
   // printResultInTable($result);
   // mysqli_free_result($result);
   array_push($allResults, $result);
   
   $result = $link->query($query6);
   // printResultInTable($result);
   // mysqli_free_result($result);
   array_push($allResults, $result);
   
   $result = $link->query($query7);
   // printResultInTable($result);
   // mysqli_free_result($result);
   array_push($allResults, $result);
   
   $result = $link->query($query8);
   // printResultInTable($result);
   // mysqli_free_result($result);
   array_push($allResults, $result);
   
   $result = $link->query($query9);
   // printResultInTable($result);
   // mysqli_free_result($result);
   array_push($allResults, $result);
   //Sequential
   
   echo "Seque End queries: " . date("Y-m-d h:i:s:a") . "<br>";

   echo sizeof($allResults);
   printResultInTable($allResults[1]);
?>