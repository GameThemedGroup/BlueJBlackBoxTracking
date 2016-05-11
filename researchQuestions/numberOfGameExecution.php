<?php
   include "../CoreFunctions.php";
   include "../graphFunctions.php";
   // include("../fusioncharts/fusioncharts.php");

   // //Question (why):
   // //How many times has the game been ran

   // //Answer: 
   // //Display number of times any method that contains main, which should be the function that invoked the game, has been called per user
   // //Most users have the same, if not close, number of invocations between the total number of 'main' method calls and all method calls

   // //Implication of answer:
   // //Min number of calls was 0 and the maximum was 419. 
   // //The assumption is that the total number of method calls would be close or similar to the number of times main is called.
   // //It is only true if the renamed class is originally the game itself and not some other classes. 
   // //The missing number of invocations could be led to the initial data download process.
   // //If the session_id is not tied to our tag, the related table will not be downloaded. 

   // //Answer's correctness: 
   // //The number of invocations called to run the game showed the number of times the main method has been called, but not how long it has been running for.
   // //We assume the main method that was called was the renamed game class, if the student decide to rename it.

   // //Methods for improving correctness: 
   // //We could also assume the time it took from one game execution to the next execution as the time it took students to fix or play the game.

   $conn = connectToLocal($db);
   // //Obtain all user_id(s)
   // $query = "select id from users order by id asc";
   // $useridList = getResultArray($conn, $query, "id");
   $useridFile = $root . "checkpoints/" . $useridFile;

   // //Load used id list 
   if(file_exists($useridFile) && 0 != filesize($useridFile)){
      $useridList = restoreFromFile($useridFile);

      // //Get start and end date for the data to query
      $dateRange = getStartEndDate();
      $startDate = $dateRange[0];
      $endDate = $dateRange[1];

      // //Create arrData for creating chart object later
      $arrData = array("chart" => initChartProperties());

      // //Specify type of chart
      $chartType = "column2d";

      // //Specify chart properties
      $propertiesToChange = array(
            "caption" => "Number of Game method calls per user",
            "xAxisName"=> "User ID",
            "yAxisName"=> "Number of invocations",
            "paletteColors" => "#0075c2",
            "showxaxisvalues" => "0",
      );

      // //Apply changes to chart properties that are different than the defaults
      modifyMultiProperties($arrData["chart"], $propertiesToChange);
      $arrData["data"] = array();

      // //Do nothing if there is no user id
      if(count($useridList) > 0){
         
         // //contain data of "%main%" invocations
         $dataInvo = array();
         // //keeps count of each method that was invoked
         $statArray = array();

         // //Loop through each user id and find their invocation event ids
         foreach($useridList as $user => $value){
            $participantId = $value['participant_id'];
            $userId = $value['user_id'];
            
            // //Query for all Invocation events 
            $query = "SELECT event_id From master_events WHERE event_type='Invocation' and created_at BETWEEN '".$startDate. "' and '" .$endDate. "' and user_id=".$userId." and participant_id=". $participantId;
            $invocationEvents = getResultArray($conn, $query, "event_id");

            if(count($invocationEvents) > 0){         
               // //Keep tracks of method that contains "%main%" that was invoked
               $numberOfMainInvoked = 0; 

               // //Loop through returned result to find it in invocations table
               foreach($invocationEvents as $event){
                  // //Get result from invocations that contains main within the invoked method
                  $query = "SELECT code From invocations where code like '%main%' and id=" . $event;
                  $results = getResult($conn, $query);

                  if($results->num_rows > 0){
                     // //Increment number of times a method that contains "main" when found
                     $numberOfMainInvoked+=$results->num_rows;
                     
                     // //Gets the name of the method that was called
                     $row = $results->fetch_assoc();
                     
                     // //increment method that was called in statArray
                     if(key_exists($row['code'],$statArray)){
                        $statArray[$row['code']]++;
                     } else {
                        $statArray[$row['code']] = 1;
                     }
                  }
                  mysqli_free_result($results);
               }

               $dataInvo[$userId] = $numberOfMainInvoked;
            }
         }

         // //Sort the array that contains user and number of method calls by descending order
         arsort($dataInvo);
         $arrayString = "User statistics<br>";
         // //Retrieve simple statistics like average, max, total number of users
         $arrayString .= getStat($dataInvo);

         // //Push user and number of method calls into $arrData chart variable
         foreach($dataInvo as $data => $value){
            $labelStr = "";
            $labelStr .= $data;
            array_push($arrData["data"], 
               array(
                  // "label" => $labelStr,
                  "label" => "",
                  "value" => $value
               )
            );
         }
         
         // //Sort the array that contains count of each method calls
         arsort($statArray);
         $arrayString .= "<br><br>Method call statistics<br>";
         $arrayString .= getStat($statArray);
         $arrayString .= "<br><table><tr><th>Method name</th><th>Count</th></tr>";
         foreach($statArray as $key => $value){
            $arrayString .= "<tr><td>".$key."</td><td>".$value."</td></tr>";
         }
         $arrayString .= "</table>";

         // //create the chart object and return it to guiPage
         echo createChartObj($arrData, $chartType, $arrayString);
      }
      
      // //House keeping
      disconnectServer($conn);
      unset($invocationEvents);
      unset($dataInvo);
      unset($dataMain);
      unset($category);
      unset($arrData);
   } else {
      $jsonMsg = array("error" => "Please download remote data at least once");
      echo json_encode($jsonMsg, true);
   }
?>