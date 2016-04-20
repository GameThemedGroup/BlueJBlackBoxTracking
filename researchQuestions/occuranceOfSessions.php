<?php
   include "../CoreFunctions.php";
   include "../graphFunctions.php";

   // //Question:
   // //When do student usually do their work? 
   // //Which day of the week has the highest number of running sessions?
   
   // //Answer: 
   // //The highest occurance of sessions are usually on Fridays or a few days before the Friday.
   // //Early week days and weekend has very low session occurance.
   
   // //Implication of answer:
   // //The high occurnace of sessions on Friday may indicates most students do their homework just as they are about to be due.
   // //Another possibility is the class isn't challenging enough that enable students to their work at last minute.
   
   // //Answer's correctness: 
   // //Since the graph data shows the "total" number of sessions per day, than the data could be influence is a student is out performing 
   // //other students by spending more time working. There are days with zero sessions, this could be possible if no student work on their 
   // //assignment or student might opt-out of the tracking program after the assignment is given.
   
   // //Methods for improving correctness: 
   // //Represent the data using per student's session time per day instead of summation of all students

   // echo "<div id='topRightGraph'></div>";

   // echo "<div>";
   // echo "<form action='" . $_SERVER['PHP_SELF'] . "' method='POST'>";
   // echo "<input type='radio' name='radio' value='column2D'>Column 2D";
   // echo "<input type='radio' name='radio' value='bar2D'>Bar 2D";
   // echo "<input type='radio' name='radio' value='line'>Line 2D";
   // echo "<input type='submit' name='submit' value='Get Selected Values' />";
   // echo "</form>";
   // echo "</div>";

   $chartType = 'column2D';
   // if (isset($_POST['submit'])) {
   //    if(isset($_POST['radio']))
   //    {
   //       $chartType = $_POST['radio'];
   //       echo "You have selected :".$_POST['radio'];  //  Displaying Selected Value
   //    }
   // }

   $conn = connectToLocal($db);
   // $query = "select id from users order by id asc";
   // $useridList = getResult($conn, $query);
   $dateRange = getStartEndDate();
   $startDate = new DateTime($dateRange[0], new DateTimeZone('America/Los_Angeles'));
   $endDate = new DateTime($dateRange[1], new DateTimeZone('America/Los_Angeles'));
   $nextDay = new DateTime($dateRange[0], new DateTimeZone('America/Los_Angeles'));
   $nextDay->modify('+1 day');
   
   // $startDate = new DateTime('2016-01-01', new DateTimeZone('America/Los_Angeles'));
   // $nextDay = new DateTime('2016-01-02', new DateTimeZone('America/Los_Angeles'));
   // $endDate = new DateTime('2016-01-25', new DateTimeZone('America/Los_Angeles'));;

   $arrData = array("chart" => initChartProperties());

   $propertiesToChange = array(
         "caption" => "Occurance of Sessions",
         "xAxisName"=> "Date",
         "yAxisName"=> "Number of Session",
         "showXAxisLine"=> "1",
         "labelDisplay" => "rotate",
   );

   modifyMultiProperties($arrData["chart"], $propertiesToChange);

   $arrData['data'] = array();

   while ($startDate->format('Y-m-d') != $endDate->format('Y-m-d')){
      // echo "From " . $startDate->format('Y-m-d') . "To " . $nextDay->format('Y-m-d') ."<br>";
      $query = "select count(id) as totalSessions from sessions where created_at BETWEEN'" . $startDate->format('Y-m-d') . "' AND '" .$nextDay->format('Y-m-d'). "'";
      // echo $query . "<br>";
      $occurance = getResult($conn, $query);

      while($row = $occurance->fetch_array()) {
         array_push($arrData['data'],
            array( 
               'label' => $startDate->format('Y-m-d l'),
               'value' => $row['totalSessions']
            )
         );
      }
      
      $startDate->modify('+1 day');
      $nextDay->modify('+1 day');
   }

   // Render the chart
   echo createChartObj($arrData, $chartType);

   disconnectServer($conn);
   mysqli_free_result($occurance);
?>
