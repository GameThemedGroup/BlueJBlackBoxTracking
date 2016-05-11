<?php
   include "../CoreFunctions.php";
   include "../graphFunctions.php";
   // //Question:
   // //What do students do just before they close BlueJ?
   // //Are they editing code or compiling or running the game?
   // //A student who spends more editing or running the game could be a result of higher attraction due to the GTCS curriculum.
   
   // //Answer: 
   // //Event like compilation and edit were the most common events that occured before BlueJ was closed
   
   // //Implication of answer:
   // //Those events were immediately before BlueJ closed, which could mean the student might have compiled for the last
   // //before they closed BlueJ for the day. Or a students' last edit before closing BlueJ
   
   // //Answer's correctness: 
   // //The data doesn't provide enough detail as to what other things students might be doing just before they closed BlueJ
   // //The only noticable events are edit and compilation
   
   // //Methods for improving correctness: 
   // //Represent the data using per student's last few events per day instead of summation of all students during the entire month. 

   //Find all events in master_events when BlueJ closes, name='bluej_finish'
   $conn = connectToLocal($db);
   $numOfEvent = 20;
   $stat = "";

   // //Get start and end date for the data to download
   $dateRange = getStartEndDate();
   $startDate = $dateRange[0];
   $endDate = $dateRange[1];

   if(isset($_GET['userid']) && isset($_GET['participantid'])){
      $userid = $_GET['userid'];
      $participantid = $_GET['participantid'];

      if(!empty($userid) && !empty($participantid))
         $caption = "Last few events before closing BlueJ for User ID: " . $userid . " and Participant ID: " . $participantid;
      else{
         $caption = "Last few events before closing BlueJ";   
      }
   } else {
      $caption = "Last few events before closing BlueJ";
   }

   // echo $caption;

   // //Query for return all possible name of event between the time frame
   if($userid == null || $participantid == null){
      $query = "SELECT distinct name from master_events where name != 'bluej_start' and created_at BETWEEN '" . $startDate . "' and '" . $endDate . "'";
   } else {
      $query = "SELECT distinct name from master_events where name != 'bluej_start' and user_id = ".$userid." and participant_id = ".$participantid." and created_at BETWEEN '" . $startDate . "' and '" . $endDate . "'";
   }
   $eventTypes = getResultArray($conn, $query, 'name');

   $typeCount = array();
   $category = array();

   // //Create labels for chart object with eventType arrays and initialize typeCount to 0 for each type of name
   foreach($eventTypes as $type){
      array_push($category, array('label' => $type));
      $typeCount[$type] = 0;
   }

   // //A bluej_start to a bluej_finish is a session
   // //We can find the end sequence num for a particular session
   // //First we want to find all the bluej_finish events
   if($userid == null || $participantid == null){
      $query = "SELECT session_id, sequence_num from master_events where name = 'bluej_finish' and created_at BETWEEN '".$startDate."' AND '".$endDate."' order by id desc";
   } else {
      $query = "SELECT session_id, sequence_num from master_events where name = 'bluej_finish' and user_id = ".$userid." and participant_id = ".$participantid." and created_at BETWEEN '".$startDate."' AND '".$endDate."' order by id desc";
   }
   $bluejCloseEvents = getResult($conn, $query);

   if($bluejCloseEvents->num_rows > 0){
      $stat .= "Number of bluej_finish event: " . $bluejCloseEvents->num_rows . "<br>";
      $arrData = array("chart" => initChartProperties());
      $chartType = "column2d";

      $propertiesToChange = array(
            "caption" => $caption,
            "xAxisName"=> "Event types",
            "yAxisName"=> "Number of events",
            "paletteColors" => "#0075c2",
      );

      modifyMultiProperties($arrData["chart"], $propertiesToChange);

      foreach($bluejCloseEvents as $bluejClose){      
         //last event is the ONE event before the LAST session bluej_finish
         $max = $bluejClose['sequence_num'] - 1;

         // sets min to 0 if sequence_num is less than the desire number of event to see
         if($bluejClose['sequence_num'] < $numOfEvent)
            $min = 0;
         else  
            // //Otherwise, the end of last few events to see is the end event sequence minus the desire number of events
            $min = $bluejClose['sequence_num'] - $numOfEvent;

         // //Query for finding all events name that is between the sequence range
         $query = "SELECT name From master_events WHERE session_id= '".$bluejClose['session_id']. "'" . " AND sequence_num between " . $min . " AND " . $max;
         
         $results = getResultArray($conn, $query, "name");

         // //Increment in tyepCount for each event name that is returned from within the sequence range
         foreach($results as $value){
            $typeCount[$value]++;
         }
      }

      $arrData["data"] = array();
      arsort($typeCount);
      foreach($typeCount as $type=>$value){
         array_push($arrData["data"], 
            array(
               "label" => $type,
               "value" => $value
            )
         );
      }

      echo createChartObj($arrData, $chartType, $stat);
   } else {
      $stat .= "No session event";
      echo createChartObj($arrData, $chartType, $stat);
   }

   disconnectServer($conn);
   unset($results);
   unset($eventTypes);
   unset($typeCount);
   unset($category);
   unset($arrData);
   mysqli_free_result($bluejCloseEvents);
?>