<?php
   include (dirname(__FILE__) . "/../common/CoreFunctions.php");
   include (dirname(__FILE__) . "/../visualization/graphFunctions.php");

   // //Question (why):
   // //Total sessions per user, a session is an opening and closing of BlueJ. 
   // //Does higher number of sessions correlates to working effort? 
   // //The more sessions there are could imply more chances of students actually working on assignments or other behaviors related to programming.

   // //Answer: 
   // //Display total sessions within two dates per user

   // //Implication of answer:
   // //There is as high as 105 sessions within a month time. 
   // //Could mean student gets distracted very often due to lack of concentration or lack of focus. Therefore having to open and close the BlueJ

   // //Answer's correctness: 
   // //Some user’s recorded session so minimal as if they never closed the BlueJ or just did their work in another computer that has not opted in for data-collection. 

   // //Methods for improving correctness: 
   // //Ensures student to stick with one computer as much as possible

   // //Get start and end date for the data to download
   $dateRange = getStartEndDate();
   $startDate = $dateRange[0];
   $endDate = $dateRange[1];
   
   $query = "SELECT user_id, count(user_id) as count from sessions where participant_id != 1 and created_at BETWEEN '".$startDate ."' and '".$endDate."' group by user_id";
   
   $conn = connectToLocal($db);
   $result = getResult($conn, $query);

   if($result){
      $arrData = array("chart" => initChartProperties());
      $chartType = "column2D";
      $propertiesToChange = array(
         "caption" => "Total number of sessions Per Machine Per Instructor",
         "xAxisName"=> "Per Machine Per Instructor",
         "yAxisName"=> "Number of Sessions",
         "paletteColors" => "#0075c2",
         "bgColor" => "#ffffff",
         "showXAxisLine"=> "1",
         "showXValue" => "0",
         "showlegend" => "1",
         "showLabels" => "0",
         "rotateValues" => "1"
      );

      modifyMultiProperties($arrData["chart"], $propertiesToChange);

      $arrData["data"] = array();
      $allArray = array();
      while($row = $result->fetch_array()) {
         $allArray[$row["user_id"]] = $row["count"];
      }

      arsort($allArray);

      foreach($allArray as $key => $value){
         array_push($arrData["data"], 
            array(
               "label" => "User ID: ".$key,
               // "label" => "",
               "value" => $value
            )
         );
      }

      echo createChartObj($arrData, $chartType, getStat($allArray));
      
      disconnectServer($conn);
   }
?>