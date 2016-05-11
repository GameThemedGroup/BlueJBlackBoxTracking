<?php
   include "../CoreFunctions.php";
   include "../graphFunctions.php";
      
   if(file_exists($root . "checkpoints/" . $useridFile)){
      // //Load useridList
      $useridList = restoreFromFile($useridFile);

      // //Make all identifiers lower case, remove all extra space, and remove 'skl'
      // //Could be removed after re-downloading of the useridFile
      foreach($useridList as $key => $value){
         $value['participant_identifier'] = strtolower($value['participant_identifier']);
         $value['participant_identifier'] = str_replace(' ', '', $value['participant_identifier']);
         $value['participant_identifier'] = str_replace('skl', '', $value['participant_identifier']);
         $useridList[$key] = $value;
      }

      // //Keep count of students for each class
      $allArray = array();
      foreach($useridList as $key => $value){
         if(key_exists($value['participant_identifier'], $allArray))
            $allArray[$value['participant_identifier']]++;
         else
            $allArray[$value['participant_identifier']] = 1;
      }
      // //Sort count of classes in descending order
      arsort($allArray);

      $arrData = array("chart" => initChartProperties());
      $chartType = "column2D";
      $propertiesToChange = array(
         "caption" => "Participation rate by instructors",
         "xAxisName"=> "Classes",
         "yAxisName"=> "Number of students",
         "paletteColors" => "#0075c2",
         "bgColor" => "#ffffff",
         "showXAxisLine"=> "1",
         "showlegend" => "1",
         "showLabels" => "1",
      );

      modifyMultiProperties($arrData["chart"], $propertiesToChange);
      $arrData["data"] = array();
      // //Push data into arrData chart variable
      foreach($allArray as $key => $value){
         array_push($arrData["data"],
            array(
               'label' => $key,
               'value' => $value
            ));
      }

      echo createChartObj($arrData, $chartType, getStat($allArray));
   } else {
      $jsonMsg = array("error" => "Please download remote data at least once");
      echo json_encode($jsonMsg, true);
   }

?>