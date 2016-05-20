<?php
   include "../CoreFunctions.php";
   include "../graphFunctions.php";

   // //Question:
   // //When do student usually do their work? 
   // //Which day of the week has the highest number of running sessions?
   // //Understanding students’ motivation, e.g. if using GTCS students begin working earlier.
   
   // //Answer: 
   // //The highest occurance of sessions are usually on Fridays or a few days before the Friday.
   // //Early week days and weekend has very low session occurance.
   
   // //Implication of answer:
   // //The high occurnace of sessions on Friday may indicates most students do their homework just as they are about to be due.
   // //Another possibility is the class isn't challenging enough that enable students to their work at last minute.
   
   // //Answer's correctness: 
   // //Since the graph data shows the "total" number of sessions per day, than the data could be influence if a student open and closing BlueJ more than others. 
   // //There are days with zero sessions, this could be possible if the student did not work on their assignment, 
   // //student opt-out of the tracking program after the assignment is given, or worked on a different machine that is not opt-in for data collection
   
   // //Methods for improving correctness: 
   // //Represent the data using per student's session time per day instead of the summation of all students

   $chartType = 'column2D';

   $conn = connectToLocal($db);

   $dateRange = getStartEndDate();
   $startDate = new DateTime($dateRange[0], new DateTimeZone('America/Los_Angeles'));
   $endDate = new DateTime($dateRange[1], new DateTimeZone('America/Los_Angeles'));
   $nextDay = new DateTime($dateRange[0], new DateTimeZone('America/Los_Angeles'));
   $nextDay->modify('+1 day');

   // //adjust chart caption according to userid and participantid
   if(isset($_GET['userid']) && isset($_GET['participantid'])){
      $userid = $_GET['userid'];
      $participantid = $_GET['participantid'];
      if(!empty($userid) && !empty($participantid))
         $caption = "Occurrence of Sessions Per Day for User ID: " . $userid . " and Participant ID: " . $participantid;
      else 
         $caption = "Occurrence of Sessions Per Day";   
   } else {
      $caption = "Occurrence of Sessions";
   }
   
   // $startDate = new DateTime('2016-01-01', new DateTimeZone('America/Los_Angeles'));
   // $nextDay = new DateTime('2016-01-02', new DateTimeZone('America/Los_Angeles'));
   // $endDate = new DateTime('2016-01-25', new DateTimeZone('America/Los_Angeles'));;

   $arrData = array("chart" => initChartProperties());   

   $propertiesToChange = array(
         "caption" => $caption,
         "xAxisName"=> "Date",
         "yAxisName"=> "Number of Session",
         "showXAxisLine"=> "1",
         "labelDisplay" => "rotate",
   );

   modifyMultiProperties($arrData["chart"], $propertiesToChange);

   $arrData['data'] = array();

   // $category = array();
   $allArray = array();

   // //continue until endDate
   while ($startDate->format('Y-m-d') != $endDate->format('Y-m-d')){
      // //if either userid or participantid is missing just query for everyones' session
      if((empty($userid) || $userid == null) && (empty($participantid)) || $participantid == null){
         $query = "SELECT count(id) as totalSessions from sessions where created_at BETWEEN'" . $startDate->format('Y-m-d') . "' AND '" .$nextDay->format('Y-m-d'). "'";
      } else {
         // //else query for one user's session
         $query = "SELECT count(id) as totalSessions from sessions where user_id = " . $userid . " and participant_id = " .$participantid. " and created_at BETWEEN'" . $startDate->format('Y-m-d') . "' AND '" .$nextDay->format('Y-m-d'). "'";
      }
      // echo $query . "<br>";
      $occurance = getResult($conn, $query);

      while($row = $occurance->fetch_array()) {
         array_push($arrData['data'],
            array( 
               'label' => $startDate->format('Y-m-d l'),
               'value' => $row['totalSessions']
            )
         );
         $allArray[$startDate->format('Y-m-d l')] = $row['totalSessions'];
      }
      
      $startDate->modify('+1 day');
      $nextDay->modify('+1 day');
   }

   // Render the chart
   echo createChartObj($arrData, $chartType, getStat($allArray));

   disconnectServer($conn);
   mysqli_free_result($occurance);
?>
