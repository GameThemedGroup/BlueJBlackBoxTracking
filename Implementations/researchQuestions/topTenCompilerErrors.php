<?php
   include (dirname(__FILE__) . "/../common/CoreFunctions.php");
   include (dirname(__FILE__) . "/../visualization/graphFunctions.php");

   // //Question:
   // //Do we have similar compiler errors as the other previous researches that used the Blackbox database.
   // //Use result to verify that the tracked data show similar outcome 
   
   // //Answer: 
   // //Missing ";" is the number 1 compiler error follow by other types of syntac errors.
   
   // //Implication of answer:
   // //For the top few errors, we shared similar outcome as the other researches where syntac error has the highest number of errors.
   // //Which implies that the tracking is working and we have similar student's performance using top error types.

   // //Answer's correctness: 
   // //The query is very simple and straight forward and therefore has a low rate of inaccuracy
   
   // //Methods for improving correctness: 
   // //n/a

   
   $conn = connectToLocal($db);
   //compile_error from invocations when compiling the generated code (e.g. be- cause the user entered invalid parameters)
   //$query = "select count(compile_error), compile_error from invocations where result = 'compile_error' group by compile_error order by count(compile_error) desc limit 10";
   
   //error messages from compile_outputs table, only return top 10 errors from the highest one
   $query = "select distinct message, count(message) as count from compile_outputs group by message order by count(message) desc limit 10";
   $result = getResult($conn, $query);

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
            "slantLabels"=> "1"
         );

      modifyMultiProperties($arrData["chart"], $propertiesToChange);

      $arrData["data"] = array();
      $dataArray = array();
      while($row = $result->fetch_array()) {
         array_push($arrData["data"], 
            array(
               "label" => $row["message"],
               "value" => $row["count"]
            )
         );
         array_push($dataArray, $row["count"]);
      }

      echo createChartObj($arrData, $chartType, getStat($dataArray));
   }
   
   disconnectServer($conn);
   unset($arrData);
?>