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

   if(file_exists($uniqueUserFile) && 0 != filesize($uniqueUserFile)){
      $useridList = restoreFromFile($uniqueUserFile);
   } else {
      // $query = "SELECT distinct s.user_id, s.participant_id FROM (SELECT @experiment:='uwbgtcs') unused, sessions_for_experiment s";
      $remoteConn = connectToBlackBox();
      $useridList = getUniqueUsers($remoteConn);
      disconnectServer($remoteConn);
   }

   // $startDate = '2016-01-01';
   // $endDate = '2016-01-25';

   // //Get start and end date for the data to query
   $dateRange = getStartEndDate();
   $startDate = $dateRange[0];
   $endDate = $dateRange[1];

   // //Create arrData for creating chart object later
   $arrData = array("chart" => initChartProperties());
   // //Specify type of chart
   $chartType = "mscolumn2d";
   // //Specify chart properties
   $propertiesToChange = array(
         "caption" => "Number of Game Invocations",
         "xAxisName"=> "User ID",
         "yAxisName"=> "Number of invocations",
         "paletteColors" => "#0075c2, #ff0000",
   );
   // //Apply changes to chart properties that are different than the defaults
   modifyMultiProperties($arrData["chart"], $propertiesToChange);
   // //arrData['dataset'] contains value of the graph
   $arrData['dataset'] = array();
   // //arrData['categories'] contains individual name for the groups of dataset
   $arrData['categories'] = array();

   // if($useridList->num_rows > 0){
   if(count($useridList) > 0){
      // echo "Total Users: " . count($useridList) . $endLine;
      $category = array(); //contain name of each data group
      $dataMain = array(); //contain data of all invocations
      $dataInvo = array(); //contain data of "%main%" invocations
      
      $statArray = array();

      foreach($useridList as $user => $value){
         $participantId = $value['participant_id'];
         $userId = $value['user_id'];
      
         $query = "SELECT event_id From master_events WHERE event_type='Invocation' and created_at BETWEEN '".$startDate. "' and '" .$endDate. "' and user_id=".$userId." and participant_id=". $participantId;
         $invocationEvents = getResultArray($conn, $query, "event_id");

         // if($invocationEvents->num_rows > 0){
         if(count($invocationEvents) > 0){
            // //each label is the user_id
            array_push($category, array('label' => $userId));
            // array_push($category, array('label' => $user));
            // //each value is the number of invocations retrieved
            array_push($dataInvo, array('value' => count($invocationEvents)));
            
            // //Keep tracks of "%main%" that is invoked
            $numberOfMainInvoked = 0; 
            foreach($invocationEvents as $event){
               // //Get result from invocations that contains main within the invoked method
               $query = "SELECT code From invocations where code like '%main%' and id=" . $event;
               $results = getResult($conn, $query);

               if($results->num_rows > 0){
                  // //Increment number of times a method that contains "main" when found
                  $numberOfMainInvoked+=$results->num_rows;
                  
                  $row = $results->fetch_assoc();
                  
                  if(key_exists($row['code'],$statArray)){
                     $statArray[$row['code']]++;
                  } else {
                     $statArray[$row['code']] = 1;
                  }
               }
               mysqli_free_result($results);
            }
            // //Store number of times method that contain "main" was invoked in dataMain
            array_push($dataMain, array('value' => $numberOfMainInvoked));
         }
      }
      // //Store all relevant data for the graph objects into arrData
      array_push($arrData['categories'], array('category' => $category));
      array_push($arrData['dataset'], array('seriesname'=>'Total Invocations', 'data' => $dataInvo));
      array_push($arrData['dataset'], array('seriesname'=>'main', 'data' => $dataMain));
      
      arsort($statArray);
      $arrayString = "<table><tr><th>Method name</th><th>Count</th></tr>";

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
?>