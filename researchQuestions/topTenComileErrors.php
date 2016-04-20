<?php
   include "../CoreFunctions.php";
   include "../graphFunctions.php";

   // //Question:
   // //Do we have similar compiler errors as the other previous researches that used the Blackbox database.
   
   // //Answer: 
   // //Missing ";" is the number 1 compiler error follow by other types of syntac errors.
   
   // //Implication of answer:
   // //For the top few errors, we shared similar outcome as the other researches where syntac error has the highest number of errors.
   
   // //Answer's correctness: 
   // //The error is only as good as the BlueJ IDE is able to identify and sends the corresponding error correctly to the server.
   
   // //Methods for improving correctness: 
   // //
   
   $conn = connectToLocal($db);
   //compile_error from invocations when compiling the generated code (e.g. be- cause the user entered invalid parameters)
   //$query = "select count(compile_error), compile_error from invocations where result = 'compile_error' group by compile_error order by count(compile_error) desc limit 10";
   
   //error messages from compile_outputs table
   $query = "select distinct message, count(message) as count from compile_outputs group by message order by count(message) desc limit 10";
   $result = getResult($conn, $query);

   // printResultInTable($result);
   // echo "<table border=1>";
   // $fields = getFieldNames($results);
   // printArray($fields, true);
   // printQueryResults($results);
   // echo "</table>";
   if($result){
      $arrData = array("chart" => initChartProperties());
      $chartType = "column2D";
      $propertiesToChange = array(
            "caption" => "Top 10 Compiler Errors",
            "xAxisName"=> "Error Type",
            "yAxisName"=> "Number of Errors",
            "labelDisplay" => "rotate",
            "paletteColors" => "#0075c2",
            "showxaxisvalues" => "0",
         );

      modifyMultiProperties($arrData["chart"], $propertiesToChange);


      $arrData["data"] = array();
      while($row = $result->fetch_array()) {
         array_push($arrData["data"], 
            array(
               "label" => $row["message"],
               "value" => $row["count"]
            )
         );
      }

      // $jsonEncodedData = json_decode($arrData, true);
      // $jsonEncodedData = json_encode($arrData);
      // $columnChart = new FusionCharts("mscolumn2d", "myFirstChart" , 1000, 650, "topRight", "json", $jsonEncodedData);
      // $columnChart = new FusionCharts("column2D", "myFirstChart" , 1000, 650, "bottomRight", "json", $jsonEncodedData);
      // Render the chart
      // $columnChart->render();
      // createChartObj($arrData, $chartType)->render();
      echo createChartObj($arrData, $chartType);
      disconnectServer($conn);
      unset($arrData);
   }
?>