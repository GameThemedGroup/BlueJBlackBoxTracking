<?php
   include "../CoreFunctions.php";
   include "../graphFunctions.php";
   // //Question:
   // //Do any students go into the SpaceSmasherAPI and modify it? 
   // //If so, how long do they spend on it?.
   // //Modifying the API could indicate students' interest into experimenting by changing the code. 

   // //Answer: 
   // //Highest time spent with API was 169426 seconds and the least with 1 second

   // //Implication of answer:
   // //There are students who spent some time looking into the API which might indicate their interest to learn and experiment more 
   // //by modifying the code.

   // //Answer's correctness: 
   // //

   // //Methods for improving correctness: 
   // //

   $conn = connectToLocal($db);

   $dateRange = getStartEndDate();
   $startDate = $dateRange[0];
   $endDate = $dateRange[1];

   $query = "select id from users order by id asc";
   $useridList = getResultArray($conn, $query, "id");

   $allArray = array();

   foreach($useridList as $id){
      $query = "select distinct project_id from master_events where user_id=" . $id ." order by project_id asc";
      $projectList = getResultArray($conn, $query, "project_id");
      $totalDuration = 0;
      
      foreach($projectList as $project){
         $query = "select id from packages where project_id =".$project." and name LIKE '%SpaceSmasher%' order by project_id asc";
         $projectNpackageIDS = getResultArray($conn, $query, "id");

         if(count($projectNpackageIDS) != 0){
            foreach($projectNpackageIDS as $package){
               $query = "SELECT created_at From master_events WHERE created_at BETWEEN '".$startDate. "' and '" .$endDate. "' and name = 'package_opening' AND package_id= '".$package."' AND project_id = '".$project."' order by created_at asc";
               // echo $query . "<br>";
               $openTime = getResult($conn, $query);
               $numOfOpen = $openTime->num_rows;
               // $numOfOpen = count($openTime);

               $query = "SELECT created_at From master_events WHERE created_at BETWEEN '".$startDate. "' and '" .$endDate. "' and name = 'package_closing' AND package_id= '".$package."' AND project_id = '".$project."' order by created_at asc";
               // echo $query . "<br>";
               $closeTime = getResult($conn, $query);
               $numOfClose = $closeTime->num_rows;
               // $numOfClose = count($closeTime);

               $arrayOfOpening = array();
               $arrayOfClosing = array();
               
               //computer duration using numOfClose, since there can be an package_opening without a closing
               if($numOfClose > 0 && $numOfOpen > 0){
                  while($row = $openTime->fetch_array()){
                     array_push($arrayOfOpening, $row['created_at']);
                  }

                  while($row = $closeTime->fetch_array()){
                     array_push($arrayOfClosing, $row['created_at']);
                  }

                  for($x = 0; $x < $numOfClose; $x++){     
                     $open = $arrayOfOpening[$x];
                     $close = $arrayOfClosing[$x];
                     $gg = calcDuration($conn, $open, $close);
                     $totalDuration += $gg;
                  }
               } 
            }
         }
      }
      if($totalDuration != 0)
         $allArray[$id] = $totalDuration;
   }

   if($allArray != null){
      arsort($allArray);
      $arrData = array("chart" => initChartProperties());
      $chartType = "column2D";
      $propertiesToChange = array(
         "caption" => "Duration opening and closing SpaceSmasherAPI",
         "xAxisName"=> "User IDs",
         "yAxisName"=> "Time in seconds",
         "paletteColors" => "#0075c2",
         "bgColor" => "#ffffff",
         "showXAxisLine"=> "1",
         "showlegend" => "1",
      );

      modifyMultiProperties($arrData["chart"], $propertiesToChange);
      $arrData["data"] = array();

      foreach($allArray as $key=>$value){
         array_push($arrData["data"], 
            array(
               "label" => "UserID: " . $key,
               "value" => $value
            )
         );
      }
      echo createChartObj($arrData, $chartType, getStat($allArray));
   }
?>