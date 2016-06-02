<?php
   include (dirname(__FILE__) . "/../common/CoreFunctions.php");
   include (dirname(__FILE__) . "/../visualization/graphFunctions.php");

   // //Question (Why): 
   // //Total time student spent on different files. Compute the number of times a file is compiled between the date 2016-01-01 to 2016-01-25 on a per user base
   
   // //Answer:  
   // //Count of different results when a file has been invoked. Majority of the invocation were successful, none of the invocation caused compile_error
   // //only three users had invocations that was terminated, and about ten users had exceptions during an invocation.
   // //Implication of answer: Successful invocation basically means the invocation was invoked without compile error, but on the other side we don't know 
   // //what causes a terminated invocation.
   
   // //Answer's correctness: 
   // //If the tracking feature in BlueJ supports when an invocation has been terminated,
   // //then we can say if a specific invocation, the main game, has been triggered for certain amount of time. 
   // //Total time per file can not be identified because an entry in the master_events shows when the file was compiled, but doesn't track 
   // //anything which identifies as end of a file edit (the only event says the file is being work on)
   
   // //Methods for improving correctness: 
   // //Suggest tracking time for invocation to Blackbox staffs
   
   $conn = connectToLocal($db);
   $query = "select id from users order by id asc";
   // $useridList = getResult($conn, $query);
   $useridList = getResultArray($conn, $query, "id");

   $dateRange = getStartEndDate();
   $startDate = $dateRange[0];
   $endDate = $dateRange[1];
   // $startDate = '2016-01-01';
   // $endDate = '2016-01-25';
   $arrData = array("chart" => initChartProperties());
   $chartType = "mscolumn2d";
   $propertiesToChange = array(
         "caption" => "Number of Invocation Results by Type Per User",
         "xAxisName"=> "User ID",
         "yAxisName"=> "Number of Invocations",
         "paletteColors" => "#0075c2, #ff0000, #33cc33, #ffff33",
      );

   modifyMultiProperties($arrData["chart"], $propertiesToChange);

   $arrData['dataset'] = array();
   $arrData['categories'] = array();
   $query = "select distinct result from invocations"; // returns 4 result types
   // $resultTypes = getResult($conn, $query);
   $resultTypes = getResultArray($conn, $query, "result");
   
   // if($useridList->num_rows > 0){
   if(count($useridList) > 0){   
      //push category into categories in $arrData()
      //array_push($arrData['categories'], array('category' => $category));

      // echo "Total Users: " . $useridList->num_rows . "<br>";
      $category = array();

      foreach($resultTypes as $type){

         // array_push($arrData['dataset'], array('seriesname'=>$type['result'], 'data'=>array()));
         $data = array();
         foreach($useridList as $user){
            // $query = "SELECT result, count(result) as count From invocations JOIN master_events ON master_events.event_id = invocations.id WHERE invocations.code like '%Main.main%' and invocations.result='".$type['result']. "' and master_events.event_type='Invocation' and master_events.created_at BETWEEN '".$startDate. "' and '" .$endDate. "' and master_events.user_id=".$user['id']." group by invocations.result";
            $query = "SELECT count(result) as count From invocations JOIN master_events ON master_events.event_id = invocations.id WHERE invocations.result='".$type. "' and master_events.event_type='Invocation' and master_events.created_at BETWEEN '".$startDate. "' and '" .$endDate. "' and master_events.user_id=".$user." group by invocations.result";
            // echo $query . "<br>";
            //returns all invocations of a User
            $invocationEvents = getResult($conn, $query);
            
            // printResultInTable($invocationEvents);
            //creates array to hold invocation data 
            // $data = array();

            if($invocationEvents->num_rows > 0){
               // $seriesname = "";
               while($row = $invocationEvents->fetch_assoc()) {
                  array_push($data, array('value' => $row['count']));
                  array_push($category, array('label' => $user));
               }
            }

            // if (empty($arrData['categories']))
            //    // array_push($category, array('label' => $user['id']));
            mysqli_free_result($invocationEvents);
         }

         array_push($arrData['dataset'], 
            array(
               'seriesname' => $type, 
               'data' => $data
               )
            );

         // if (empty($arrData['categories'])){
            // category label are individual user
         array_push($arrData['categories'], array('category' => $category));
         // $arrData['categories'] = array('category' => $category);
         // }
      }
      echo createChartObj($arrData, $chartType);
   }
   // House keeping
   disconnectServer($conn);
   unset($category);
   unset($resultTypes);
   unset($arrData);
   unset($useridList);
   
?>