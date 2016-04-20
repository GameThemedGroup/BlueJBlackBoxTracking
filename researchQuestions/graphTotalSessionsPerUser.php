<?php
   include "../CoreFunctions.php";
   include "../graphFunctions.php";

   // //Question (why):
   // //Total sessions per user. Higher the sessions = higher invocations?

   // //Answer: 
   // //Display total sessions within two dates per user

   // //Implication of answer:
   // //There is as high as 356 sessions within a month time. That student could be particular hardwork or just love to trail and error.

   // //Answer's correctness: 
   // //

   // //Methods for improving correctness: 
   // //
   
   
   $query = "SELECT user_id, count(user_id) as count from sessions group by user_id";
   
   $conn = connectToLocal($db);
   $result = getResult($conn, $query);

   if($result){
      $arrData = array("chart" => initChartProperties());
      $chartType = "column2D";
      $propertiesToChange = array(
         "caption" => "Total Sessions Per User ID",
         "xAxisName"=> "User IDs",
         "yAxisName"=> "Number of Sessions",
         "paletteColors" => "#0075c2",
         "bgColor" => "#ffffff",
         "showXAxisLine"=> "1",
         "showlegend" => "1",
      );

      modifyMultiProperties($arrData["chart"], $propertiesToChange);

      $arrData["data"] = array();
      while($row = $result->fetch_array()) {
         array_push($arrData["data"], 
            array(
               "label" => "UserID: " . $row["user_id"],
               "value" => $row["count"]
            )
         );
      }

      echo createChartObj($arrData, $chartType);
      
      disconnectServer($conn);
   }
?>