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
         "divLineIsDashed" => "1",
         "baseFontSize" => "20",
         "valuePadding" => "10",
         "valueFontBold" => "1",
         "valueFontSize" => "12",
         "valueFontColor" => "#ff0000"
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

   function findSum($dataArray){
      return array_sum($dataArray);
   }

   // Function to calculate square of value - mean
   function sd_square($x, $mean) { return pow($x - $mean,2); }

      // Function to calculate standard deviation (uses sd_square)    
   function sd($array) {
       
      // square root of sum of squares devided by N-1
      return sqrt(array_sum(array_map("sd_square", $array, array_fill(0,count($array), (array_sum($array) / count($array)) ) ) ) / (count($array)-1) );
   }

   function getStat($dataArray){
      $stat = "Number of entries: " .count($dataArray). "<br>Total: " .findSum($dataArray). "<br>Max: " .findMax($dataArray). "<br>Average: " .findAvg($dataArray). "<br>Min: ". findMin($dataArray) . "<br>Standard Deviation: " . sd($dataArray);
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