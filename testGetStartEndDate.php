<?php
   include 'CoreFunctions.php';
   include 'graphFunctions.php';
   $root = "./";

   $connLocal = connectToLocal("capstoneLocal");

   $query = "SELECT distinct project_id from master_events limit 10";
   // $projectidList = getResultArray($connLocal, $query, "project_id");
   $projectidList = getResult($connLocal, $query);
   
   foreach($projectidList as $id){
      $var = "Empty";
      $var += $id;
      echo $var;
   }

   $query = "SELECT event_id 
               From master_events 
               WHERE event_type='Invocation' 
                  and created_at BETWEEN '".$startDate. "' and '" .$endDate. "' 
                  and user_id=".$userId." and participant_id=". $participantId;

   $query = "SELECT code 
               From invocations 
               where code like '%main%' and id=" . $event;

   // $query = "select id from users";
   // $results = getResultArray($conn, $query, 'id');

   // echo "Current table: " . count($results) . $endLine;

   // updateLocal('users_out.csv');

   // $query = "select id from users";
   // $results = getResultArray($conn, $query, 'id');

   // echo "First add to table: " . count($results) . $endLine;


   // updateLocal('users_out.csv');

   // $query = "select id from users";
   // $results = getResultArray($conn, $query, 'id');

   // echo "Second add table: " . count($results) . $endLine;

   // $numOfEvent = 20;

   // // //Get start and end date for the data to download
   // $dateRange = getStartEndDate();
   // $startDate = $dateRange[0];
   // $endDate = $dateRange[1];

   // // //Query for return all possible name of event between the time frame
   // $query = "SELECT distinct name from master_events where name != 'bluej_start' and created_at BETWEEN '" . $startDate . "' and '" . $endDate . "'";
   // $eventTypes = getResultArray($conn, $query, 'name');

   // $typeCount = array();
   // $category = array();

   // // //Create labels for chart object with eventType arrays and initialize typeCount to 0 for each type of name
   // foreach($eventTypes as $type){
   //    array_push($category, array('label' => $type));
   //    $typeCount[$type] = 0;
   // }

   // //a bluej_start to a bluej_finish is a session
   // //we can find the end sequence num for a particular session
   // $query = "SELECT session_id, sequence_num from master_events where name = 'bluej_finish' and created_at BETWEEN '".$startDate."' AND '".$endDate."' order by id desc";
   // // //Returns ALL events that have bluej_finish, which is the end of a session
   // $bluejCloseEvents = getResult($conn, $query);

   // if($bluejCloseEvents->num_rows > 0){
   //    foreach($bluejCloseEvents as $bluejClose){      
   //       //last event is the ONE event before the LAST session bluej_finish
   //       $max = $bluejClose['sequence_num'] - 1;

   //       // sets min to 0 if sequence_num is less than the desire number of event to see
   //       if($bluejClose['sequence_num'] < $numOfEvent)
   //          $min = 0;
   //       else  
   //          // //Otherwise, the end of last few events to see is the end event sequence minus the desire number of events
   //          $min = $bluejClose['sequence_num'] - $numOfEvent;

   //       // //Query for finding all events name that is between the sequence range
   //       $query = "SELECT name From master_events WHERE session_id= '".$bluejClose['session_id']. "'" . " AND sequence_num between " . $min . " AND " . $max;
         
   //       // $results = getResultArray($conn, $query, "event_type");
   //       $results = getResultArray($conn, $query, "name");
         
   //       foreach($results as $value){
   //          $typeCount[$value]++;
   //       }
   //       // if(array_key_exists($results[0], $typeCount)){ 
   //       //    $typeCount[$results[0]]++;
   //       // }
   //       echo "<pre>";
   //       print_r($typeCount);
   //       echo "</pre>";
   //    }
   // }
?>