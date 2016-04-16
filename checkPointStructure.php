userid = (init), master_events, users, projects

projectid = (master_events), packages, source_files

sessionid = (init), inspectors, invocations, sessions

source_filesid = (sourceIds), breakpoints, compile_inputs, fixtures




 $arrData = array(
         "chart" => array(
            "caption" => "Number of Invocation Results by Type Per User",
            "labelDisplay" => "auto",
            "showvalues" => "1",
            "showyaxisvalues" => "1",
            "xAxisName"=> "User and Invocation Result Type",
            "yAxisName"=> "Number of Result",
            "showAlternateHGridColor" => "1",
            "paletteColors" => "#0075c2, #ff0000, #33cc33",
            "bgColor" => "#ffffff",
            "borderAlpha"=> "20",
            "canvasBorderAlpha"=> "0",
            "usePlotGradientColor"=> "0",
            "plotBorderAlpha"=> "10",
            "plotHighlightEffect"=> "fadeout",
            "xAxisLineColor" => "#999999",
            "divlineColor" => "#999999",
            "divLineIsDashed" => "1"
         )
      );

      $arrData = array(
         "chart" => array(
            "caption" => "Occurance of Sessions",
            "paletteColors" => "#0075c2",
            "bgColor" => "#ffffff",
            "borderAlpha"=> "20",
            "canvasBorderAlpha"=> "0",
            "usePlotGradientColor"=> "0",
            "plotBorderAlpha"=> "10",
            "plotHighlightEffect"=> "fadeout",
            "showXAxisLine"=> "1",
            "showlegend" => "0",
            "labelDisplay" => "rotate",
            "showvalues" => "1",
            "showyaxisvalues" => "1",
            "showxaxisvalues" => "0",
            "xAxisName"=> "Date",
            "yAxisName"=> "Number of Session",
            "xAxisLineColor" => "#999999",
            "divlineColor" => "#999999",
            "divLineIsDashed" => "1",
            "showAlternateHGridColor" => "0"
         )
      );

      $arrData = array(
            "chart" => array(
               "caption" => "Top 10 Compiler Errors",
               "paletteColors" => "#0075c2",
               "bgColor" => "#ffffff",
               "borderAlpha"=> "20",
               "canvasBorderAlpha"=> "0",
               "usePlotGradientColor"=> "0",
               "plotBorderAlpha"=> "10",
               "plotHighlightEffect"=> "fadeout",
               "showXAxisLine"=> "1",
               "showlegend" => "1",
               "labelDisplay" => "rotate",
               "showvalues" => "1",
               "showyaxisvalues" => "1",
               "showxaxisvalues" => "0",
               "xAxisName"=> "Error Type",
               "yAxisName"=> "Number of Errors",
               "xAxisLineColor" => "#999999",
               "divlineColor" => "#999999",
               "divLineIsDashed" => "1",
               "showAlternateHGridColor" => "0"
            )
         );

         $arrData = array(
            "chart" => array(
               "caption" => "Total Sessions Per User ID",
               "paletteColors" => "#0075c2",
               "bgColor" => "#ffffff",
               "borderAlpha"=> "20",
               "canvasBorderAlpha"=> "0",
               "usePlotGradientColor"=> "0",
               "plotBorderAlpha"=> "10",
               "plotHighlightEffect"=> "fadeout",
               "showXAxisLine"=> "1",
               "showlegend" => "1",
               "showvalues" => "1",
               "showyaxisvalues" => "1",
               "showxaxisvalues" => "0",
               "xAxisName"=> "User IDs",
               "yAxisName"=> "Number of Sessions",
               "xAxisLineColor" => "#999999",
               "divlineColor" => "#999999",
               "divLineIsDashed" => "1",
               "showAlternateHGridColor" => "0"
            )
         );

$arrData = array("chart" => initChartProperties());
      $propertiesToChange = array(
            "caption" => "Number of Invocation Results by Type Per User",
            "xAxisName"=> "User and Invocation Result Type",
            "yAxisName"=> "Number of Result",
         );

      $arrData['dataset'] = array();
      $arrData['categories'] = array();

