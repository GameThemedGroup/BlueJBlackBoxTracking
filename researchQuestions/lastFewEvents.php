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

   // //Get start and end date for the data to download
   $dateRange = getStartEndDate();
   $startDate = $dateRange[0];
   $endDate = $dateRange[1];

   // $query = "SELECT distinct event_type from master_events where event_type!='' and created_at BETWEEN '" . $startDate . "' and '" . $endDate . "'";
   $query = "SELECT distinct name from master_events where name != 'bluej_start' and created_at BETWEEN '" . $startDate . "' and '" . $endDate . "'";
   // $eventTypes = getResultArray($conn, $query, 'event_type');
   $eventTypes = getResultArray($conn, $query, 'name');

   $typeCount = array();
   $category = array();
   foreach($eventTypes as $type){
      array_push($category, array('label' => $type));
      $typeCount[$type] = 0;
   }

   //a bluej_start to a bluej_finish is a session
   //we can find the end sequence num for a particular session
   $query = "SELECT session_id, sequence_num from master_events where name = 'bluej_finish' and created_at BETWEEN '".$startDate."' AND '".$endDate."' order by id desc";
   $bluejCloseEvents = getResult($conn, $query);

   // echo "<table border=1>";
   // printQueryResults($bluejCloseEvents);
   // echo "</table>";

   if($bluejCloseEvents->num_rows > 0){
      $arrData = array("chart" => initChartProperties());
      $chartType = "column2d";

      $propertiesToChange = array(
            "caption" => "Last few events before closing BlueJ",
            "xAxisName"=> "Event types",
            "yAxisName"=> "Number of events",
            "paletteColors" => "#0075c2, #ff0000",
      );

      modifyMultiProperties($arrData["chart"], $propertiesToChange);

      foreach($bluejCloseEvents as $bluejClose){
         // echo "session_id: ". $bluejClose['session_id'] . "</br> last sequence_num: ".$bluejClose['sequence_num']."<br>";
         
         //last event is the one before the LAST session, bluej_close
         $max = $bluejClose['sequence_num'] - 1;

         // sets min to 0 if sequence_num is less than the desire number of event to see
         if($bluejClose['sequence_num'] < $numOfEvent)
            $min = 0;
         else  
            $min = $bluejClose['sequence_num'] - $numOfEvent;

         // $query = "SELECT event_type From master_events WHERE session_id= '".$bluejClose['session_id']. "'" . " AND event_type !='' AND sequence_num between " . $min . " AND " . $max;
         $query = "SELECT name From master_events WHERE session_id= '".$bluejClose['session_id']. "'" . " AND sequence_num between " . $min . " AND " . $max;
         // echo $query . "<br>";
         // $results = getResultArray($conn, $query, "event_type");
         $results = getResultArray($conn, $query, "name");
         
         if(array_key_exists($results[0], $typeCount)){ 
            $typeCount[$results[0]]++;
         }
      }
      // array_push($arrData['categories'], array('category' => $category));
      // array_push($arrData['dataset'], array('seriesname'=>'Total Invocations', 'data' => $dataInvo));
      // array_push($arrData['dataset'], array('seriesname'=>'Main.main({})', 'data' => $dataMain));
      // $data = array();
      // foreach($typeCount as $type=>$value){
      //    array_push($data, array('value' => $value));
      //    array_push($arrData['dataset'], array('seriesname'=>$type, 'data' => $data));
      // }

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

      echo createChartObj($arrData, $chartType);
   }

   disconnectServer($conn);
   unset($results);
   unset($eventTypes);
   unset($typeCount);
   unset($category);
   unset($arrData);
   mysqli_free_result($bluejCloseEvents);
?>