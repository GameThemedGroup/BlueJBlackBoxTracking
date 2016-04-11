<?php
   // //Include only if running this page on a php without the include of CoreFunctions
   // include 'CoreFunctions.php';

   // durationOfSpaceSmasherAPI();
   // participationRate();
   // lastFewEvents();
   // topTenComileErrors();  
   // numberOfCompilePerFile();
   // numberOfCompilePerTodo();
   // numberOfGameExecution();
   // invokeExceptions();
   // invocationsPerUser();
   // getUserList();

   // $conn = connectToLocal("capstoneLocalForQA");
   // $query = "select source_time from master_events where id = 720536179";
   // $result = getResult($conn, $query);

   // foreach($result as $info){
   //    echo $info['source_time'];
   //    $query = "date_format(".$info['source_time'].",%Y-%m-%d %T)";
   //    $conn->query($query);
   // }

   // disconnectFromBlackBox($conn);


   function getUserList(){
      //Compute the number of times a file is compiled between the date 2016-01-01 to 2016-01-25 on a per user base
      //Total time per file can not be identified because an entry in the master_events shows when the file was compiled, but doesn't track 
      //anything which identifies as end of a file edit (the only event says the file is being work on)
      $conn = connectToLocal('capstoneLocal');
      $query = "select id from users order by id asc";
      $useridList = getResult($conn, $query);
      // $startDate = '2016-01-01';
      // $endDate = '2016-01-25';

      if($useridList->num_rows > 0){
         echo "Total Users: " . $useridList->num_rows . "<br>";
      ?>
         <form name="form1" method="POST" action="<?php $_SERVER['PHP_SELF'];?>">
         <select name="user_id">
         <option value=""></option> <!--Can have or not, to be tested-->
      <?php
         // echo "<select>";
         foreach($useridList as $user){
            echo "<option value='" . $user[id] . "'>" . $user[id] . "</option>";
         }
         echo "</select><br>";
      }
   }

   function invocationsPerUser(){
      //Compute the number of times a file is compiled between the date 2016-01-01 to 2016-01-25 on a per user base
      //Total time per file can not be identified because an entry in the master_events shows when the file was compiled, but doesn't track 
      //anything which identifies as end of a file edit (the only event says the file is being work on)
      $conn = connectToLocal('capstoneLocal');
      $query = "select id from users order by id asc";
      $useridList = getResult($conn, $query);
      $startDate = '2016-01-01';
      $endDate = '2016-01-25';

      $arrData = array(
         "chart" => array(
            "caption" => "Number of Invocations by Type Per User",
            "labelDisplay" => "auto",
            
            "showvalues" => "1",
            "showyaxisvalues" => "1",
            "xAxisName"=> "User and Invocation Type",
            "yAxisName"=> "Number of Invocations",
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

      //////////////////////////
      $arrData['dataset'] = array();
      $arrData['categories'] = array();
      $query = "select distinct result from invocations";
      $resultTypes = getResult($conn, $query);
      
      if($useridList->num_rows > 0){
         //push category into categories in $arrData()
         //array_push($arrData['categories'], array('category' => $category));

         // echo "Total Users: " . $useridList->num_rows . "<br>";
         $category = array();

         foreach($resultTypes as $type){

            // array_push($arrData['dataset'], array('seriesname'=>$type['result'], 'data'=>array()));
            $data = array();
            foreach($useridList as $user){
               $query = "SELECT result, count(result) as count From invocations JOIN master_events ON master_events.event_id = invocations.id WHERE invocations.code like '%Main.main%' and invocations.result='".$type['result']. "' and master_events.event_type='Invocation' and master_events.created_at BETWEEN '".$startDate. "' and '" .$endDate. "' and master_events.user_id=".$user['id']." group by invocations.result";
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
                     // $seriesname =  $row['result'];
                  }
               } else {
                  array_push($data, array('value' => ''));
               }

               if (empty($arrData['categories']))
                  array_push($category, array('label' => $user['id']));
            }

            array_push($arrData['dataset'], 
               array(
                  'seriesname' => $type['result'], 
                  'data' => $data
                  )
               );

            if (empty($arrData['categories'])){
               // category label are individual user
               array_push($arrData['categories'], array('category' => $category));
            }
         }

         // echo "<pre>";
         // print_r($arrData);
         // echo "</pre>";

         //encode php data into JSON format
         $jsonEncodedData = json_encode($arrData, true);
         //create chart object with JSON data
         $columnChart = new FusionCharts("mscolumn2d", "myFirstChart" , 1000, 650, "bottomRight", "json", $jsonEncodedData);
         // Render the chart
         $columnChart->render();
      }
      
      //////////////////////////////

      
      // //Original code
      /*
      $arrData["data"] = array();
      if($useridList->num_rows > 0){
         echo "Total Users: " . $useridList->num_rows . "<br>";
         foreach($useridList as $user){
            // echo $user['id'] . "<br>";
            // $query = "SELECT event_id From master_events WHERE event_type='Invocation' and created_at BETWEEN '".$startDate. "' and '" .$endDate. "' and user_id=".$user[id];
            $query = "SELECT result, count(result) as count From invocations JOIN master_events ON master_events.event_id = invocations.id WHERE invocations.code like '%Main.main%' and master_events.event_type='Invocation' and master_events.created_at BETWEEN '".$startDate. "' and '" .$endDate. "' and master_events.user_id=".$user['id']." group by invocations.result";
            // echo $query . "<br>";
            $invocationEvents = getResult($conn, $query);

            if($invocationEvents->num_rows > 0){
               // echo "user_id: " . $user['id'] . "<br>";
               printResultInTable($invocationEvents);

               while($row = $invocationEvents->fetch_array()) {
                  array_push($arrData['data'], 
                     array(
                        'label' => "UserID: " . $user['id']. "<br>Invocation Type: " .$row['result'],
                        'value' => $row['count']
                     )
                  );
               }
            }
         }

         // $jsonEncodedData = json_decode($arrData, true);
         $jsonEncodedData = json_encode($arrData);
         // $columnChart = new FusionCharts("mscolumn2d", "myFirstChart" , 1000, 650, "topRight", "json", $jsonEncodedData);
         $columnChart = new FusionCharts("column2D", "myFirstChart" , 1000, 650, "topRight", "json", $jsonEncodedData);
         // Render the chart
         // $columnChart->render();
      }
      */
      

      disconnectServer($conn);
   }

   function invokeExceptions(){
      //Compute the number of times a file is compiled between the date 2016-01-01 to 2016-01-25 on a per user base
      //Total time per file can not be identified because an entry in the master_events shows when the file was compiled, but doesn't track 
      //anything which identifies as end of a file edit (the only event says the file is being work on)
      $conn = connectToLocal('capstoneLocal');
      $query = "select id from users order by id asc";
      $useridList = getResult($conn, $query);
      $startDate = '2016-01-01';
      $endDate = '2016-01-25';

      if($useridList->num_rows > 0){
         echo "Total Users: " . $useridList->num_rows . "<br>";
         foreach($useridList as $user){
            // echo "user_id: ".$user[id]."<br>";
            //using user_id, query for all event_id events where event_type = Invocations between the date 2016-01-01 to 2016-01-25
            //which is the time up till the first assignment due date
            $query = "SELECT event_id From master_events WHERE event_type='Invocation' and created_at BETWEEN '".$startDate. "' and '" .$endDate. "' and user_id=".$user[id];
            // echo $query . "<br>";
            $invocationEvents = getResult($conn, $query);

            if($invocationEvents->num_rows > 0){
               echo "user_id: ".$user[id]."<br>";
               $numberOfInvoked = 0; 
               foreach($invocationEvents as $event){
                  $query = "SELECT result, exception_class, exception_message From invocations where code like '%Main.main({ })%' and result='exception' and id=" . $event[event_id];
                  // echo $query ."<br>";
                  $results = getResult($conn, $query);

                  if($results->num_rows > 0){
                     $numberOfInvoked+=$results->num_rows;                  
                     printResultInTable($results);
                  }
               }
               if($numberOfInvoked > 0){
                  // echo "user_id: ".$user[id]."<br>";
                  echo "Number of Invocations with exceptions: " . $numberOfInvoked . " bewteen " . $startDate . "until " . $endDate. "<br><br>";
               }
               $numberOfInvoked = 0;
            }
         }
      }
   }

   function numberOfGameExecution(){
      //Compute the number of times a file is compiled between the date 2016-01-01 to 2016-01-25 on a per user base
      //Total time per file can not be identified because an entry in the master_events shows when the file was compiled, but doesn't track 
      //anything which identifies as end of a file edit (the only event says the file is being work on)
      $conn = connectToLocal('capstoneLocal');
      $query = "select id from users order by id asc";
      $useridList = getResult($conn, $query);
      $startDate = '2016-01-01';
      $endDate = '2016-01-25';

      if($useridList->num_rows > 0){
         echo "Total Users: " . $useridList->num_rows . "<br>";
         foreach($useridList as $user){
            // $numberOfMain = array();
            // echo "user_id: ".$user[id]."<br>";
            //using user_id, query for all event_id events where event_type = Invocations between the date 2016-01-01 to 2016-01-25
            //which is the time up till the first assignment due date
            $query = "SELECT event_id From master_events WHERE event_type='Invocation' and created_at BETWEEN '".$startDate. "' and '" .$endDate. "' and user_id=".$user[id];
            // echo $query . "<br>";
            $invocationEvents = getResult($conn, $query);

            if($invocationEvents->num_rows > 0){
               echo "user_id: ".$user[id]."<br>";
               echo "Number of Invocations: " . $invocationEvents->num_rows . " bewteen " . $startDate . "until " . $endDate. "<br>";
               $numberOfMainInvoked = 0; 
               foreach($invocationEvents as $event){
                  $query = "SELECT code, result From invocations where code like '%Main.main({ })%' and id=" . $event[event_id];
                  // echo $query ."<br>";
                  $results = getResult($conn, $query);

                  if($results->num_rows > 0){
                     $numberOfMainInvoked+=$results->num_rows;
                  }

                  // // echo "event_id= " .$event[event_id] . " session_id= " . $event[session_id]. "<br>";
                  // while($row = $results->fetch_assoc()){
                  //    foreach($row as $field){
                  //       // echo "File name= " . $field . "<br>";
                  //       if(!array_key_exists($field, $arrayOfFileNames)){
                  //          $arrayOfFileNames[$field] = 1;
                  //       } else {
                  //          $arrayOfFileNames[$field]++;
                  //       }
                  //    }
                  // }
               }
               echo "Number of Main.main({}) invoked: " . $numberOfMainInvoked . "<br><br>";
               $numberOfMainInvoked = 0;            
               printResultInTable($invocationEvents);
            }
         }
      }
   }

   function numberOfCompilePerTodo(){
      //Compute the number of times a TODO file is compiled between the date 2016-01-01 to 2016-01-25 on a per user base
      //Total time per file can not be identified because an entry in the master_events shows when the file was compiled, but doesn't track 
      //anything which identifies as end of a file edit (the only event says the file is being work on)
      $conn = connectToLocal('capstoneLocal');
      $query = "select id from users order by id asc";
      $useridList = getResult($conn, $query);
      $startDate = '2016-01-01';
      $endDate = '2016-01-25';

      // $arrData = array(
      //    "chart" => array(
      //       "caption" => "Total Sessions Per User ID",
      //       "paletteColors" => "#0075c2",
      //       "bgColor" => "#ffffff",
      //       "borderAlpha"=> "20",
      //       "canvasBorderAlpha"=> "0",
      //       "usePlotGradientColor"=> "0",
      //       "plotBorderAlpha"=> "10",
      //       "plotHighlightEffect"=> "fadeout",
      //       "showXAxisLine"=> "1",
      //       "showlegend" => "1",
      //       // "showlabels" => "0",
      //       // "labelDisplay" => "wrap",
      //       "showvalues" => "1",
      //       "showyaxisvalues" => "1",
      //       "showxaxisvalues" => "0",
      //       "xAxisName"=> "User IDs",
      //       "yAxisName"=> "Number of Sessions",
      //       "xAxisLineColor" => "#999999",
      //       "divlineColor" => "#999999",
      //       "divLineIsDashed" => "1",
      //       "showAlternateHGridColor" => "0"
      //    )
      // );

      // $arrData["dataset"] = array();
      // $arrData['categories'] = array();

      if($useridList->num_rows > 0){
         echo "Total Users: " . $useridList->num_rows . "<br>";
         foreach($useridList as $user){
            $arrayOfFileNames = array();
            //echo "user_id: ".$user[id]."<br>";
            //using user_id, query for all event_id and session_id events where event_type = CompileEvents between the date 2016-01-01 to 2016-01-25
            //which is the time up till the first assignment due date
            $query = "SELECT event_id From master_events WHERE event_type='CompileEvent' and created_at BETWEEN '".$startDate. "' and '" .$endDate. "' and user_id=".$user['id'];
            // echo $query;
            $compileEvents = getResult($conn, $query);

            //To retrieve source_file name we need to match compile_input.source_file_id to source_files.id
            //Then pick out those matches by selecting rows in compile_inputs.compile_event_id that is found from the above query
            //that is event_id when event_type = CompileEvent from the master_events
            if($compileEvents->num_rows > 0){
               echo "user_id: ".$user['id']."<br>";
               foreach($compileEvents as $event){
                  $query = "SELECT source_files.name From source_files JOIN compile_inputs on compile_inputs.source_file_id=source_files.id where compile_inputs.compile_event_id='".$event['event_id']."' and name LIKE '%TODO%' group by source_files.name";
                  $results = getResult($conn, $query);
                  // echo "event_id= " .$event[event_id] . "<br>";
                  while($row = $results->fetch_assoc()){
                     foreach($row as $field){
                        // echo "File name= " . $field . "<br>";
                        if(!array_key_exists($field, $arrayOfFileNames)){
                           $arrayOfFileNames[$field] = 1;
                        } else {
                           $arrayOfFileNames[$field]++;
                        }
                     }
                  }
               }
               if(count($arrayOfFileNames)>0){
                  ksort($arrayOfFileNames);
                  echo "<pre>";
                  print_r($arrayOfFileNames);
                  echo "</pre>"; 
               } else {
                  echo "No Compile Event<br><br>";
               }
               // foreach($arrayOfFileNames as $file => $value){
               //    // echo $file;
               //    if(!array_key_exists($file, $arrData['categories']))
               //       array_push($arrData['categories'], array('label' => $file));
               // }
               // print_r($arrData['categories']);
               // array_push($arrData["dataset"], 
               //    array(
               //       "seriesname" => "UserID: " . $user["id"],
               //       "data" => $arrayOfFileNames
               //    )
               // );
               // print_r($arrData['dataset']);
            }
         }

         // $jsonEncodedData = json_decode($arrData, true);
         // $jsonEncodedData = json_encode($arrData);
         // $columnChart = new FusionCharts("mscolumn2d", "myFirstChart" , 1000, 650, "topRight", "json", $jsonEncodedData);
         // $columnChart = new FusionCharts("column2D", "myFirstChart" , 1000, 650, "topRight", "json", $jsonEncodedData);
         // Render the chart
         // $columnChart->render();
         // echo $jsonEncodedData;
         // Close the database connection
         disconnectServer($conn);
      }
   }

   function occuranceOfSessions(){
      echo "<div id='topRightGraph'></div>";

      echo "<div>";
      echo "<form action='" . $_SERVER['PHP_SELF'] . "' method='POST'>";
      // echo "<select name='type'>";
      echo "<input type='radio' name='radio' value='column2D'>Column 2D";
      echo "<input type='radio' name='radio' value='bar2D'>Bar 2D";
      echo "<input type='radio' name='radio' value='line'>Line 2D";
      // echo "</select>";
      echo "<input type='submit' name='submit' value='Get Selected Values' />";
      echo "</form>";
      echo "</div>";

      $chartType = 'bar2D';
      if (isset($_POST['submit'])) {
         if(isset($_POST['radio']))
         {
            $chartType = $_POST['radio'];
            echo "You have selected :".$_POST['radio'];  //  Displaying Selected Value
         }
      }

      // $date = new DateTime('2016-01-01', new DateTimeZone('America/New_York'));
      // echo $date->format('Y-m-d') . "<br>";
      // $date->modify('+1 day');
      // echo $date->format('Y-m-d');

      // if($date->format('Y-m-d') == '2016-01-01')
      //    echo "boo yah";
      $conn = connectToLocal('capstoneLocal');
      $query = "select id from users order by id asc";
      $useridList = getResult($conn, $query);
      $startDate = new DateTime('2016-01-01', new DateTimeZone('America/Los_Angeles'));
      $nextDay = new DateTime('2016-01-02', new DateTimeZone('America/Los_Angeles'));
      $endDate = new DateTime('2016-01-25', new DateTimeZone('America/Los_Angeles'));;

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
         // printResultInTable($occurance);
         $startDate->modify('+1 day');
         $nextDay->modify('+1 day');
      }

      // $jsonEncodedData = json_decode($arrData, true);
      $jsonEncodedData = json_encode($arrData);
      // $columnChart = new FusionCharts("mscolumn2d", "myFirstChart" , 1000, 650, "topRight", "json", $jsonEncodedData);
      $columnChart = new FusionCharts($chartType, "myFirstChart" , 1120, 650, "bottomRight", "json", $jsonEncodedData);
      // Render the chart
      $columnChart->render();

      disconnectServer($conn);
   }

   function durationBetweenTodo(){
      //Compute the number of times a TODO file is compiled between the date 2016-01-01 to 2016-01-25 on a per user base
      //Total time per file can not be identified because an entry in the master_events shows when the file was compiled, but doesn't track 
      //anything which identifies as end of a file edit (the only event says the file is being work on)
      $conn = connectToLocal('capstoneLocal');
      $query = "select id from users order by id asc";
      $useridList = getResult($conn, $query);
      $startDate = '2016-01-01';
      $endDate = '2016-01-25';

      if($useridList->num_rows > 0){
         echo "Total Users: " . $useridList->num_rows . "<br>";
         foreach($useridList as $user){
            $arrayOfFileNames = array();
            //echo "user_id: ".$user[id]."<br>";
            //using user_id, query for all event_id and session_id events where event_type = CompileEvents between the date 2016-01-01 to 2016-01-25
            //which is the time up till the first assignment due date
            $query = "SELECT event_id From master_events WHERE event_type='CompileEvent' and created_at BETWEEN '".$startDate. "' and '" .$endDate. "' and user_id=".$user['id'];
            // echo $query;
            $compileEvents = getResult($conn, $query);

            //To retrieve source_file name we need to match compile_input.source_file_id to source_files.id
            //Then pick out those matches by selecting rows in compile_inputs.compile_event_id that is found from the above query
            //that is event_id when event_type = CompileEvent from the master_events
            if($compileEvents->num_rows > 0){
               echo "user_id: ".$user['id']."<br>";
               foreach($compileEvents as $event){
                  $query = "SELECT source_files.name From source_files JOIN compile_inputs on compile_inputs.source_file_id=source_files.id where compile_inputs.compile_event_id='".$event['event_id']."' and name LIKE '%TODO%' group by source_files.name";
                  $results = getResult($conn, $query);
                  // echo "event_id= " .$event[event_id] . "<br>";
                  while($row = $results->fetch_assoc()){
                     foreach($row as $field){
                        // echo "File name= " . $field . "<br>";
                        if(!array_key_exists($field, $arrayOfFileNames)){
                           $arrayOfFileNames[$field] = 1;
                        } else {
                           $arrayOfFileNames[$field]++;
                        }
                     }
                  }
               }
               if(count($arrayOfFileNames)>0){
                  ksort($arrayOfFileNames);
                  echo "<pre>";
                  print_r($arrayOfFileNames);
                  echo "</pre>"; 
               } else {
                  echo "No Compile Event<br><br>";
               }
            }
         }
         // Close the database connection
         disconnectServer($conn);
      }
   }

   function numberOfCompilePerFile(){
      //Compute the number of times a file is compiled between the date 2016-01-01 to 2016-01-25 on a per user base
      //Total time per file can not be identified because an entry in the master_events shows when the file was compiled, but doesn't track 
      //anything which identifies as end of a file edit (the only event says the file is being work on)
      $conn = connectToLocal('capstoneLocal');
      $query = "select id from users order by id";
      $useridList = getResult($conn, $query);
      $startDate = '2016-01-12';
      $endDate = '2016-01-25';

      if($useridList->num_rows > 0){
         echo "Total Users: " . $useridList->num_rows . "<br>";
         foreach($useridList as $user){
            $arrayOfFileNames = array();
            echo "user_id: ".$user['id']."<br>";
            //using user_id, query for all event_id and session_id events where event_type = CompileEvents between the date 2016-01-01 to 2016-01-25
            //which is the time up till the first assignment due date
            $query = "SELECT event_id, session_id From master_events WHERE event_type='CompileEvent' and created_at BETWEEN '".$startDate. "' and '" .$endDate. "' and user_id=".$user['id'];
            // echo $query;
            $compileEvents = getResult($conn, $query);

            //To retrieve source_file name we need to match compile_input.source_file_id to source_files.id
            //Then pick out those matches by selecting rows in compile_inputs.compile_event_id that is found from the above query
            //that is event_id when event_type = CompileEvent from the master_events
            if($compileEvents->num_rows > 0){
               foreach($compileEvents as $event){
                  $query = "SELECT source_files.name From source_files JOIN compile_inputs on compile_inputs.source_file_id=source_files.id where compile_inputs.compile_event_id='".$event['event_id']."' group by source_files.name";
                  $results = getResult($conn, $query);
                  // echo "event_id= " .$event[event_id] . " session_id= " . $event[session_id]. "<br>";
                  while($row = $results->fetch_assoc()){
                     foreach($row as $field){
                        // echo "File name= " . $field . "<br>";
                        if(!array_key_exists($field, $arrayOfFileNames)){
                           $arrayOfFileNames[$field] = 1;
                        } else {
                           $arrayOfFileNames[$field]++;
                        }
                     }
                  }
               }
            }

            if(count($arrayOfFileNames)>0){
               //arsort($arrayOfFileNames);
               echo "<pre>";
               print_r($arrayOfFileNames);
               echo "</pre>"; 
            } else {
               echo "No Compile Event<br><br>";
            }
         }
      }
   }

   function topTenComileErrors(){
      $conn = connectToLocal('capstoneLocalForQA');
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
         $jsonEncodedData = json_encode($arrData);
         // $columnChart = new FusionCharts("mscolumn2d", "myFirstChart" , 1000, 650, "topRight", "json", $jsonEncodedData);
         $columnChart = new FusionCharts("column2D", "myFirstChart" , 1000, 650, "bottomRight", "json", $jsonEncodedData);
         // Render the chart
         $columnChart->render();
       
         disconnectServer($conn);
      }
   }

   function lastFewEvents(){
      //Find all events in master_events when BlueJ closes, name='bluej_finish'
      $conn = connectToLocal('capstoneLocalForQA');

      //a bluej_start to a bluej_finish is a session
      //we can find the end sequence num for a particular session
      $query = "select session_id, sequence_num from master_events where name = 'bluej_finish' order by id desc";
      $bluejCloseEvents = getResult($conn, $query);

      // echo "<table border=1>";
      // printQueryResults($bluejCloseEvents);
      // echo "</table>";

      if($bluejCloseEvents->num_rows > 0){
         foreach($bluejCloseEvents as $bluejClose){
            echo "session_id: ". $bluejClose['session_id'] . "</br> last sequence_num: ".$bluejClose['sequence_num']."<br>";
            $numOfEvent = 10;
            $max = $bluejClose['sequence_num'] - 1;

            if($bluejClose['sequence_num'] < 5)
               $min = 0;
            else 
            { 
               $min = $bluejClose['sequence_num'] - $numOfEvent;
               if($min < 0)
                  $min = 0;
            }

            $query = "SELECT * From master_events WHERE session_id= '".$bluejClose['session_id']. "'" . " AND sequence_num between " . $min . " AND " . $max;
            echo $query . "<br>";
            $results = getResult($conn, $query);

            echo "<table border=1>";
            $fields = getFieldNames($results);
            printArray($fields, yes);
            printQueryResults($results);
            echo "</table>";

         }
      }

      disconnectServer($conn);
   }

   function participationRate(){
      $tUStudent = 0; //total Unique students
      $tPStudents = 0; //total Possible students

      $conn = connectToBlackBox();  

      //Shows one user_id can have more than one participant_id and identifier
      // $query = "SELECT s.* FROM (SELECT @experiment:='uwbgtcs') unused, sessions_for_experiment s";
      // $t = getResult($conn, $query);
      // $fields = getFieldNames($t, yes);
      // echo "<table border=1>";
      // printArray($fields, yes);
      // printQueryResults($t);
      // echo "</table>";

      //Get UNIQUE (distinct) user_id using experiment_identifier (e.g.'uwbgtcs')
      //from the session_for_experiment table, one user_id can have more than one participant_id and identifier
      //therefore not entirely UNIQUE
      $query = "SELECT count(distinct s.user_id) FROM (SELECT @experiment:='uwbgtcs') unused, sessions_for_experiment s";
      $totalUniqueStudent = getResult($conn, $query);

      
      // printQueryResults($totalUniqueStudent);

      if($totalUniqueStudent->num_rows > 0){
         // echo "Have ". mysqli_num_rows($results)." of results<br>";
         while($row = $totalUniqueStudent->fetch_assoc()){
            foreach($row as $field){
               if($field != null)
                  $tUStudent = $field;
            }
         }
      } 

      //Get TOTAL (non-distinct) user_id using experiment_identifier (e.g.'uwbgtcs')
      $query = "SELECT count(s.user_id) FROM (SELECT @experiment:='uwbgtcs') unused, sessions_for_experiment s";
      $totalPossibleStudents = getResult($conn, $query);
      // printQueryResults($totalPossibleStudents);
      if($totalPossibleStudents->num_rows > 0){
         // echo "Have ". mysqli_num_rows($results)." of results<br>";
         while($row = $totalPossibleStudents->fetch_assoc()){
            foreach($row as $field){
               if($field != null)
                  $tPStudents = $field;
            }
         }
      } 

   
      $pRate = $tUStudent / $tPStudents;
      echo "Participation rate  = Total Unique Students / Total Possible Students <br>";
      echo "Participation rate  = " . $tUStudent . "/" . $tPStudents . " = " . $pRate*100 . "%";
      // return $pRate;
   }

   function calcDuration($conn, $open, $close){
      $query = "SELECT TIMESTAMPDIFF(second, '" . $open ."', '". $close . "')";
      $result = $conn->query($query);

      $duration = 0;
      while($row = $result->fetch_assoc()){
         foreach($row as $field){
            $duration = $field;
         }
      }

      return $duration;
   }

   function graphTotalSessionsPerUser(){
      //////////////////////////////////////////////
      // //FusionChart
      $query = "SELECT user_id, count(user_id) as count from sessions group by user_id";
      $conn = connectToLocal("capstoneLocal");
      $result = getResult($conn, $query);

      if($result){

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
               // "showlabels" => "0",
               // "labelDisplay" => "wrap",
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

         $arrData["data"] = array();
         while($row = $result->fetch_array()) {
            array_push($arrData["data"], 
               array(
                  "label" => "UserID: " . $row["user_id"],
                  "value" => $row["count"]
               )
            );
         }
         //set the response content type as JSON
         // header('Content-type: application/json');
         // $jsonEncodedData = json_decode($arrData, true);
         $jsonEncodedData = json_encode($arrData);
         // $columnChart = new FusionCharts("mscolumn2d", "myFirstChart" , 1000, 650, "topRight", "json", $arrData);
         $columnChart = new FusionCharts("column2D", "myFirstChart" , 1000, 650, "bottomRight", "json", $jsonEncodedData);
         // Render the chart
         $columnChart->render();
         // $jsonDecode = json_decode($arrData, true);
         // echo "<pre>";
         // print_r($arrData);
         // echo "</pre>";
         // Close the database connection
         disconnectServer($conn);
      }
      /////////////////////////////////////////////
   }

   function durationOfSpaceSmasherAPI(){
      $conn = connectToLocal('capstoneLocalForQA');

      //query for all SpaceSmasherAPI from Packge
      $query = "select id, project_id from packages where name = 'SpaceSmasher_FunctionalAPI'";

      $projectNpackageIDS = getResult($conn, $query);

      // $duration = calcDuration($conn, '2015-05-28 21:25:57','2015-05-28 21:26:40');
      // printQueryResults($duration);
      echo "<table border=1>";
      foreach($projectNpackageIDS as $infoId){
         echo "<tr><td>Package ID: ". $infoId['id'] . "</td></tr>";
         echo "<tr><td>Project ID: ". $infoId['project_id'] . "</td></tr>";

         $totalDuration = 0;

         //Query for package information in descending order using created_at 
         //To find the duration of the opening and closing pair, we first find the closing time then back track to the 
         //first occurance of opening to relate both events together.
         //Tirst finding the opening time than closing wouldn't work because and opening might not have a closing if the IDE 
         //is closed abruptly.
         $query = "SELECT created_at, session_id, sequence_num From master_events WHERE name = 'package_opening' AND package_id= '".$infoId['id']."' AND project_id = '".$infoId['project_id']."' order by created_at desc";
         // echo $query . "";
         $openTime = getResult($conn, $query);

         $query = "SELECT created_at, session_id, sequence_num From master_events WHERE name = 'package_closing' AND package_id= '".$infoId['id']."' AND project_id = '".$infoId['project_id']."' order by created_at desc";
         // echo $query . "";
         $closeTime = getResult($conn, $query);

         $numOfOpen = $openTime->num_rows;
         $numOfClose = $closeTime->num_rows;

         $arrayOfClosing = array();
         $arrayOfOpening = array();

         //computer duration using numOfClose, since there can be an package_opening without a closing
         if($numOfClose > 0 && $numOfOpen > 0){
         // echo "Have ". mysqli_num_rows($results)." of results";
            while($row = $closeTime->fetch_array()){
               $arrayOfClosing[] = $row['created_at'];
            }

            while($row = $openTime->fetch_array()){
               $arrayOfOpening[] = $row['created_at'];
            }

            for($x = 0; $x < $numOfClose; $x++){     
               $a = $arrayOfOpening[$x];
               $b = $arrayOfClosing[$x];

               echo "<tr><td>Opening: " . $a . "</td>";
               echo "<td>Closing: " . $b . "</td>";

               $gg = calcDuration($conn, $a, $b);
               $totalDuration += $gg;
               echo "<td>Duration: " . $gg . "  seconds</td></tr>";
            }
         } 
         
         echo "<tr><td><td><td>Total Package Duration: ". $totalDuration . "</td></td></td></tr>";

         // echo "Open<table border=1>";
         // printQueryResults($openTime); 
         // echo "</table>"; 

         // echo "Close<table border=1>";
         // printQueryResults($closeTime); 
         // echo "</table>";   
      }
      disconnectServer($conn);
      echo "</table>";
   }

   function getUserIdByType($typeOfInfo, $infoId){
      switch ($typeOfInfo){
         case "master_event_id":
            $query = "select distinct user_id from master_events where id =" .$infoId."";
            getByOtherID($query);
            break;
         case "session_id":
            $query = "select distinct user_id from master_events where session_id =" .$infoId."";
            getByOtherID($query);
            break;
         case "package_id":
            $query = "select distinct user_id from master_events where package_id =" .$infoId."";
            getByOtherID($query);
            break;
         case "project_id":
            $query = "select distinct user_id from master_events where project_id =" .$infoId."";
            getByOtherID($query);
            break;
         case "source_file_id":
            $query = "select distinct project_id from source_files where id =" .$infoId."";
            $projectId = getByOtherID($query);
            if($projectId->num_rows > 0){
               foreach($projectId as $project){
                  $query = "select distinct user_id from master_events where project_id =" .$project[project_id].""; 
                  $userId = getByOtherID($query);
               }
            }
            printResultInTable($userId);
            break;
      }
   }

   function getByOtherID($query){
      $conn = connectToLocal("capstoneLocal");
      $useridList = getResult($conn, $query);
      disconnectServer($conn);
      return $useridList;
   }

?>