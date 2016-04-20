<?php
   include "../CoreFunctions.php";
   include "../graphFunctions.php";
   // include("../fusioncharts/fusioncharts.php");

   // //Question (why):
   // //How many times has the game been ran

   // //Answer: 
   // //Display number of times main.main({}), the function invoked to run the game, has been called per user

   // //Implication of answer:
   // //Min number of calls was 0 and the maximum was 136. In a month time for one assignment, a student ran the game 136 times, which averaged
   // //out to running the game at least 5 times a day.

   // //Answer's correctness: 
   // //The number of invocations called to run the game only showes how many times the game has be ran, but not how long it has been played.
   // //The high number of calling main.main({}) could be called just to see if the game compiles and runs.

   // //Methods for improving correctness: 
   // //Recommend adding an event when an invoked function ends. 
   // //We could also assume the time it took from one game execution to the next execution as the time it took students to fix or play the game.

   $conn = connectToLocal($db);
   $query = "select id from users order by id asc";
   // $useridList = getResult($conn, $query);
   $useridList = getResultArray($conn, $query, "id");
   // $startDate = '2016-01-01';
   // $endDate = '2016-01-25';

   // //Get start and end date for the data to download
   $dateRange = getStartEndDate();
   $startDate = $dateRange[0];
   $endDate = $dateRange[1];

   // print_r($dateRange);

   $arrData = array("chart" => initChartProperties());
   $chartType = "mscolumn2d";

   $propertiesToChange = array(
         "caption" => "Number of Game Invocations",
         "xAxisName"=> "User ID",
         "yAxisName"=> "Number of invocations",
         "paletteColors" => "#0075c2, #ff0000",
   );

   modifyMultiProperties($arrData["chart"], $propertiesToChange);
   $arrData['dataset'] = array();
   $arrData['categories'] = array();

   // if($useridList->num_rows > 0){
   if(count($useridList) > 0){
      // echo "Total Users: " . count($useridList) . $endLine;
      $category = array();
      $dataMain = array();
      $dataInvo = array();

      foreach($useridList as $user){
         // array_push($category, array('label' => $user));
         // $numberOfMain = array();
         // echo "user_id: ".$user[id]."<br>";
         //using user_id, query for all event_id events where event_type = Invocations between the date 2016-01-01 to 2016-01-25
         //which is the time up till the first assignment due date
         // $query = "SELECT event_id From master_events WHERE event_type='Invocation' and created_at BETWEEN '".$startDate. "' and '" .$endDate. "' and user_id=".$user[id];
         $query = "SELECT event_id From master_events WHERE event_type='Invocation' and created_at BETWEEN '".$startDate. "' and '" .$endDate. "' and user_id=".$user;
         // echo $query . "<br>";
         // $invocationEvents = getResult($conn, $query);
         $invocationEvents = getResultArray($conn, $query, "event_id");

         // if($invocationEvents->num_rows > 0){
         if(count($invocationEvents) > 0){
            // if (empty($arrData['categories']))
               // array_push($category, array('label' => $user['id']));
            array_push($category, array('label' => $user));
            array_push($dataInvo, array('value' => count($invocationEvents)));
            // echo "user_id: ".$user[id]."<br>";
            // echo "user_id: ".$user.$endLine;
            // echo "Number of Invocations: " . count($invocationEvents) . $endLine;
            // echo "Number of Invocations: " . $invocationEvents->num_rows . $endLine;
            // echo " bewteen " . $startDate . "until " . $endDate. $endLine;
            
            $numberOfMainInvoked = 0; 
            foreach($invocationEvents as $event){
               // $query = "SELECT code, result From invocations where code like '%Main.main({ })%' and id=" . $event[event_id];
               $query = "SELECT result From invocations where code like '%Main.main({ })%' and id=" . $event;
               // echo $query ."<br>";
               $results = getResult($conn, $query);

               if($results->num_rows > 0){
                  $numberOfMainInvoked+=$results->num_rows;
                  // $numberOfMainInvoked++;
               }
               mysqli_free_result($results);
            }
            // echo "Number of Main.main({}) invoked: " . $numberOfMainInvoked . "<br><br>";
            array_push($dataMain, array('value' => $numberOfMainInvoked));
            // $numberOfMainInvoked = 0;            
            // printResultInTable($invocationEvents);
         }
      }

      array_push($arrData['categories'], array('category' => $category));
      array_push($arrData['dataset'], array('seriesname'=>'Total Invocations', 'data' => $dataInvo));
      array_push($arrData['dataset'], array('seriesname'=>'Main.main({})', 'data' => $dataMain));
      
      // createChartObj($arrData, $chartType)->render();
      echo createChartObj($arrData, $chartType);
   }
   // House keeping
   disconnectServer($conn);
   unset($invocationEvents);
   unset($dataInvo);
   unset($dataMain);
   unset($category);
   unset($arrData);
?>