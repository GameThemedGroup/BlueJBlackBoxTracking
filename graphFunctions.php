<?php
   function modifyChartProperty(& $chartProperties, $property, $pValue){
      $chartProperties[$property] = $pValue;
   }

   function modifyMultiProperties(& $chartProperties, $properties){
      foreach($properties as $property => $value){
         modifyChartProperty($chartProperties, $property, $value);
      }
   }

   function getChartProperty($chartProperties, $property){
      return $chartProperties[$property];
   }

   function initChartProperties(){
      $chartProperties = array(
         "caption" => "Chart Title",
         "labelDisplay" => "auto",
         "showvalues" => "1",
         "showyaxisvalues" => "1",
         "xAxisName"=> "X Axis Name",
         "yAxisName"=> "Y Axis Name",
         "showAlternateHGridColor" => "1",
         "paletteColors" => "#0075c2",
         "bgColor" => "#ffffff",
         "borderAlpha"=> "20",
         "canvasBorderAlpha"=> "0",
         "usePlotGradientColor"=> "0",
         "plotBorderAlpha"=> "10",
         "plotHighlightEffect"=> "fadeout",
         "xAxisLineColor" => "#999999",
         "divlineColor" => "#999999",
         "divLineIsDashed" => "1"
      );

      return $chartProperties;
   }

   function findMin($dataArray){
      $min = min($dataArray);
      return $min;
   }

   function findMax($dataArray){
      $max = max($dataArray);
      return $max;
   }

   function findAvg($dataArray){
      $avg = array_sum($dataArray) / count($dataArray);
      return $avg;
   }

   function getStat($dataArray){
      $stat = "Average: ".findAvg($dataArray)."<br>Max: ".findMax($dataArray)."<br>Min: ".findMin($dataArray);
      return $stat;
   }

   function createChartObj($arrData = null, $chartType = null, $sideChart = null){
      //$chartObj = new FusionCharts($chartType, "myFirstChart", 1120, 650, "bottomRight", "json", json_encode($arrData, true));
      $result = array(
         "type" => $chartType,
         "data" => $arrData,
         "sideChart" => $sideChart
      );

      return json_encode($result, true);
      // return $chartObj;
   }

?>