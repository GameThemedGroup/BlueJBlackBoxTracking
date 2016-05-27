<?php
   include "../CoreFunctions.php";
   include "../graphFunctions.php";
   // //Question:
   // //Do any students go into the SpaceSmasherAPI and modify it? 
   // //If so, how long do they spend on it?.
   // //Modifying the API could indicate students' interest into experimentation by changing the code. 

   // //Answer: 
   // //Highest time spent with API was 169426 seconds and the least with 1 second

   // //Implication of answer:
   // //There are students who spent some time looking into the API which might indicate their interest to learn and experiment more 
   // //by modifying the code.

   $conn = connectToLocal($db);
   $dateRange = getStartEndDate();
   $startDate = $dateRange[0];
   $endDate = $dateRange[1];

   $useridFile = $root . "checkpoints/" . $useridFile;

   // //Load user id list, exit if there are no list
   if(file_exists($useridFile) && 0 != filesize($useridFile)){
      $useridList = restoreFromFile($useridFile);
      $userId = 0;
      $allArray = array();

      // //Search for project id for each unique user + participant id
      foreach($useridList as $user => $value){
         $participantId = $value['participant_id'];
         $userId = $value['user_id'];

         $query = "SELECT distinct project_id from master_events where user_id=" . $userId ." and participant_id =" . $participantId . " order by project_id asc";
         $projectList = getResultArray($conn, $query, "project_id");
         $totalDuration = 0;
            
         // //Retrieve all project that have the package which contains SpaceSmasher
         foreach($projectList as $project){
            $query = "SELECT id from packages where project_id =".$project." and name LIKE '%SpaceSmasher%' order by project_id asc";
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
                  
                  //compute duration when there are more than one events of package open and close, since there can be an package_opening without a closing
                  if($numOfClose > 0 && $numOfOpen > 0){
                     while($row = $openTime->fetch_array()){
                        array_push($arrayOfOpening, $row['created_at']);
                     }

                     while($row = $closeTime->fetch_array()){
                        array_push($arrayOfClosing, $row['created_at']);
                     }
                     // //use numOfClose as condition because there are packages without closing event
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
         // //store the total duration for a particular user in an array using the user_id as key
         if($totalDuration != 0)
            $allArray[$userId] = $totalDuration;
      }

      if($allArray != null){
         arsort($allArray);

         $arrData = array("chart" => initChartProperties());
         $chartType = "column2D";
         $propertiesToChange = array(
            "caption" => "Duration spent in SpaceSmasherAPI",
            "xAxisName"=> "Per Machine Per Instructor",
            "yAxisName"=> "Time in minutes",
            "paletteColors" => "#0075c2",
            "bgColor" => "#ffffff",
            "showXAxisLine"=> "1",
            "showlegend" => "1",
            "showLabels" => "0",
            "rotateValues" => "1"
         );

         modifyMultiProperties($arrData["chart"], $propertiesToChange);
         $arrData["data"] = array();

         foreach($allArray as $key=>$value){
            // //convert duration from seconds into minutes
            $allArray[$key] = $value/60;
            // $timeSpent /= 60;

            array_push($arrData["data"], 
               array(
                  "label" => "Per Machine Per Instructor: " . $key,
                  "value" => floor($allArray[$key])
               )
            );
         }

         echo createChartObj($arrData, $chartType, getStat($allArray));
      }
   } else {
      $jsonMsg = array("error" => "Please download remote data at least once");
      echo json_encode($jsonMsg, true);
   }
?>