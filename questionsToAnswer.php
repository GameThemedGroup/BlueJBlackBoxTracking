<?php
   // //Include only if running this page on a php without the include of CoreFunctions
   // include 'CoreFunctions.php';

   // //Question:
   // //
   // //Answer: 
   // //
   // //Implication of answer:
   // //
   // //Answer's correctness: 
   // //
   // //Methods for improving correctness: 
   // //

   $endLine = "\n";
   $db = "capstoneLocal";

   function getUserList(){
      global $db;
      $conn = connectToLocal($db);
      $query = "SELECT id FROM users order by id asc";
      // $useridList = getResult($conn, $query);
      $useridList = getResultArray($conn, $query, "id");

      // $startDate = '2016-01-01';
      // $endDate = '2016-01-25';

      // if($useridList->num_rows > 0){
      if(count($useridList) > 0){
         // echo "Total Users: " . $useridList->num_rows . "<br>";
         echo "Total Users: " . count($useridList) . "<br>";
      ?>
         <form name="form1" method="POST" action="<?php $_SERVER['PHP_SELF'];?>">
         <select name="user_id">
         <option value=""></option> <!--Can have or not, to be tested-->
      <?php
         // echo "<select>";
         foreach($useridList as $user){
            // echo "<option value='" . $user[id] . "'>" . $user[id] . "</option>";
            echo "<option value='" . $user . "'>" . $user . "</option>";
         }
         echo "</select><br>";
      }
   }

   function invokeExceptions(){
   // //Not needed, as invocation per user does it
      global $db;
      $conn = connectToLocal($db);
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

   function invocationsPerUser(){
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
      global $db;
      $conn = connectToLocal($db);
      $query = "select id from users order by id asc";
      // $useridList = getResult($conn, $query);
      $useridList = getResultArray($conn, $query, "id");
      $startDate = '2016-01-01';
      $endDate = '2016-01-25';
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
         createChartObj($arrData, $chartType)->render();
      }
      // House keeping
      disconnectServer($conn);
      unset($category);
      unset($resultTypes);
      unset($arrData);
      unset($useridList);
   }

   function numberOfGameExecution(){
      // //Question (why):
      // //How many times has the game been ran

      // //Answer: 
      // //Display number of times main.main({}), the function invoked to run the game, has been called per user

      // //Implication of answer:
      // //Min number of calls was 0 and the maximum was 136. In a month time for one assignment, a student ran the game 136 times, which averaged
      // //out to running the game at least 5 times a day.

      // //Answer's correctness: 
      // //The number of invocations called to run the game only showes how many times the game has be ran, but not how long it has been played.
      // //The high number of calling main.main({}) could be called just to see if the game compiles and runs.

      // //Methods for improving correctness: 
      // //Recommend adding an event when an invoked function ends. 
      // //We could also assume the time it took from one game execution to the next execution as the time it took students to fix or play the game.
      global $endLine;
      global $db;
      $conn = connectToLocal($db);
      $query = "select id from users order by id asc";
      // $useridList = getResult($conn, $query);
      $useridList = getResultArray($conn, $query, "id");
      // $startDate = '2016-01-01';
      // $endDate = '2016-01-25';

      // //Get start and end date for the data to download
      $dateRange = getStartEndDate();
      $startDate = $dateRange[0];
      $endDate = $dateRange[1];

      // print_r($dateRange);

      $arrData = array("chart" => initChartProperties());
      $chartType = "mscolumn2d";

      $propertiesToChange = array(
            "caption" => "Number of Game Invocations",
            "xAxisName"=> "User ID",
            "yAxisName"=> "Number of invocations",
            "paletteColors" => "#0075c2, #ff0000",
      );

      modifyMultiProperties($arrData["chart"], $propertiesToChange);
      $arrData['dataset'] = array();
      $arrData['categories'] = array();

      // if($useridList->num_rows > 0){
      if(count($useridList) > 0){
         echo "Total Users: " . count($useridList) . $endLine;
         $category = array();
         $dataMain = array();
         $dataInvo = array();

         foreach($useridList as $user){
            // array_push($category, array('label' => $user));
            // $numberOfMain = array();
            // echo "user_id: ".$user[id]."<br>";
            //using user_id, query for all event_id events where event_type = Invocations between the date 2016-01-01 to 2016-01-25
            //which is the time up till the first assignment due date
            // $query = "SELECT event_id From master_events WHERE event_type='Invocation' and created_at BETWEEN '".$startDate. "' and '" .$endDate. "' and user_id=".$user[id];
            $query = "SELECT event_id From master_events WHERE event_type='Invocation' and created_at BETWEEN '".$startDate. "' and '" .$endDate. "' and user_id=".$user;
            // echo $query . "<br>";
            // $invocationEvents = getResult($conn, $query);
            $invocationEvents = getResultArray($conn, $query, "event_id");

            // if($invocationEvents->num_rows > 0){
            if(count($invocationEvents) > 0){
               // if (empty($arrData['categories']))
                  // array_push($category, array('label' => $user['id']));
               array_push($category, array('label' => $user));
               array_push($dataInvo, array('value' => count($invocationEvents)));
               // echo "user_id: ".$user[id]."<br>";
               // echo "user_id: ".$user.$endLine;
               // echo "Number of Invocations: " . count($invocationEvents) . $endLine;
               // echo "Number of Invocations: " . $invocationEvents->num_rows . $endLine;
               // echo " bewteen " . $startDate . "until " . $endDate. $endLine;
               
               $numberOfMainInvoked = 0; 
               foreach($invocationEvents as $event){
                  // $query = "SELECT code, result From invocations where code like '%Main.main({ })%' and id=" . $event[event_id];
                  $query = "SELECT result From invocations where code like '%Main.main({ })%' and id=" . $event;
                  // echo $query ."<br>";
                  $results = getResult($conn, $query);

                  if($results->num_rows > 0){
                     $numberOfMainInvoked+=$results->num_rows;
                     // $numberOfMainInvoked++;
                  }
                  mysqli_free_result($results);
               }
               // echo "Number of Main.main({}) invoked: " . $numberOfMainInvoked . "<br><br>";
               array_push($dataMain, array('value' => $numberOfMainInvoked));
               // $numberOfMainInvoked = 0;            
               // printResultInTable($invocationEvents);
            }
         }

         array_push($arrData['categories'], array('category' => $category));
         array_push($arrData['dataset'], array('seriesname'=>'Total Invocations', 'data' => $dataInvo));
         array_push($arrData['dataset'], array('seriesname'=>'Main.main({})', 'data' => $dataMain));

         createChartObj($arrData, $chartType)->render();
      }
      // House keeping
      disconnectServer($conn);
      unset($invocationEvents);
      unset($dataInvo);
      unset($dataMain);
      unset($category);
      unset($arrData);
   }

   function numberOfCompilePerTodo(){
      // //Question:
      // //Total time per "to-do"
      
      // //Answer: 
      // //Compute the number of times a TODO file is compiled between the date 2016-01-01 to 2016-01-25 on a per user base
      
      // //Implication of answer:
      // //Some To-do files have high number of compilation as much as 100 times.
      // //Which could be an indicator of the difficulties student encounters when learning introductory programming class.
      // //The number of compilation decreases over the future To-do(s) which could mean students are learning and finding the later 
      // //tasks easier to solve.
      
      // //Answer's correctness: 
      // //Total time spent on a file can not be identified because an entry in the master_events shows when the file was compiled, but doesn't track 
      // //anything which identifies as end of a file edit (the only event says the file is being work on)
      
      // //Methods for improving correctness: 
      // //Recommend adding an event when an invoked function ends. 
      // //We could also assume the time it took from one game execution to the next execution as the time it took students to fix or play the game.
      global $db;
      $conn = connectToLocal($db);
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

         disconnectServer($conn);
      }
   }

   function numberOfCompilePerFile(){
      /// //Question:
      // //Total time per "to-do"
      
      // //Answer: 
      // //Compute the number of times any file is compiled between the date 2016-01-01 to 2016-01-25 on a per user base
      
      // //Implication of answer:
      // //Some files have higher number of compilation than the To-do(s).
      // //Which could be an indicator of the difficulties student encounters on a particular assignment.
      
      // //Answer's correctness: 
      // //Total time spent on a file can not be identified because an entry in the master_events shows when the file was compiled, but doesn't track 
      // //anything which identifies as end of a file edit (the only event says the file is being work on)
      
      // //Methods for improving correctness: 
      // //Recommend adding an event when an invoked function ends. 
      // //We could also assume the time it took from one game execution to the next execution as the time it took students to fix or play the game.

      $conn = connectToLocal($db);
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

   function occuranceOfSessions(){
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

      echo "<div id='topRightGraph'></div>";

      echo "<div>";
      echo "<form action='" . $_SERVER['PHP_SELF'] . "' method='POST'>";
      echo "<input type='radio' name='radio' value='column2D'>Column 2D";
      echo "<input type='radio' name='radio' value='bar2D'>Bar 2D";
      echo "<input type='radio' name='radio' value='line'>Line 2D";
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

      global $db;
      $conn = connectToLocal($db);
      $query = "select id from users order by id asc";
      $useridList = getResult($conn, $query);
      $startDate = new DateTime('2016-01-01', new DateTimeZone('America/Los_Angeles'));
      $nextDay = new DateTime('2016-01-02', new DateTimeZone('America/Los_Angeles'));
      $endDate = new DateTime('2016-01-25', new DateTimeZone('America/Los_Angeles'));;

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
         // printResultInTable($occurance);
         $startDate->modify('+1 day');
         $nextDay->modify('+1 day');
      }

      // Render the chart
      createChartObj($arrData, $chartType)->render();

      disconnectServer($conn);
   }

   function durationBetweenTodo(){
      //Compute the number of times a TODO file is compiled between the date 2016-01-01 to 2016-01-25 on a per user base
      //Total time per file can not be identified because an entry in the master_events shows when the file was compiled, but doesn't track 
      //anything which identifies as end of a file edit (the only event says the file is being work on)
      global $db;
      $conn = connectToLocal($db);
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

   function topTenComileErrors(){
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
      global $db;
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
         createChartObj($arrData, $chartType)->render();
         disconnectServer($conn);
         unset($arrData);
      }
   }

   function lastFewEvents(){
      // //Question:
      // //What do students do just before they close BlueJ?
      
      // //Answer: 
      // //Event like compilation and invocation were the most common events that occured before BlueJ was closed
      
      // //Implication of answer:
      // //Those events were immediately before BlueJ closed, which could mean the student might have compiled for the last
      // //before they closed BlueJ for the day. Or a student invoked a function just to see if it works and then closed BlueJ
      
      // //Answer's correctness: 
      // //The data doesn't provide enough detail as to what other things students might be doing just before they closed BlueJ
      // //The only noticable events in invocation and compilation
      
      // //Methods for improving correctness: 
      // //Represent the data using per student's session time per day instead of summation of all students

      //Find all events in master_events when BlueJ closes, name='bluej_finish'
      global $db;
      $conn = connectToLocal($db);
      $numOfEvent = 20;

      // //Get start and end date for the data to download
      // $dateRange = getStartEndDate();
      // $startDate = $dateRange[0];
      // $endDate = $dateRange[1];

      $query = "SELECT distinct event_type from master_events where event_type!='' and created_at BETWEEN '" . $startDate . "' and '" . $endDate . "'";
      $eventTypes = getResultArray($conn, $query, 'event_type');

      $typeCount = array();
      $category = array();
      foreach($eventTypes as $type){
         array_push($category, array('label' => $type));
         $typeCount[$type] = 0;
      }

      //a bluej_start to a bluej_finish is a session
      //we can find the end sequence num for a particular session
      $query = "SELECT session_id, sequence_num from master_events where name = 'bluej_finish' and created_at BETWEEN '".$startDate."' AND '".$endDate."' order by id desc";
      $bluejCloseEvents = getResult($conn, $query);

      // echo "<table border=1>";
      // printQueryResults($bluejCloseEvents);
      // echo "</table>";

      if($bluejCloseEvents->num_rows > 0){
         $arrData = array("chart" => initChartProperties());
         $chartType = "column2d";

         $propertiesToChange = array(
               "caption" => "Last few events before closing BlueJ",
               "xAxisName"=> "Event types",
               "yAxisName"=> "Number of events",
               "paletteColors" => "#0075c2, #ff0000",
         );

         modifyMultiProperties($arrData["chart"], $propertiesToChange);

         foreach($bluejCloseEvents as $bluejClose){
            // echo "session_id: ". $bluejClose['session_id'] . "</br> last sequence_num: ".$bluejClose['sequence_num']."<br>";
            
            //last event is the one before the LAST session, bluej_close
            $max = $bluejClose['sequence_num'] - 1;

            // sets min to 0 if sequence_num is less than the desire number of event to see
            if($bluejClose['sequence_num'] < $numOfEvent)
               $min = 0;
            else  
               $min = $bluejClose['sequence_num'] - $numOfEvent;

            $query = "SELECT event_type From master_events WHERE session_id= '".$bluejClose['session_id']. "'" . " AND event_type !='' AND sequence_num between " . $min . " AND " . $max;
            // echo $query . "<br>";
            $results = getResultArray($conn, $query, "event_type");
            
            if(array_key_exists($results[0], $typeCount)){ 
               $typeCount[$results[0]]++;
            }
         }
         // array_push($arrData['categories'], array('category' => $category));
         // array_push($arrData['dataset'], array('seriesname'=>'Total Invocations', 'data' => $dataInvo));
         // array_push($arrData['dataset'], array('seriesname'=>'Main.main({})', 'data' => $dataMain));
         // $data = array();
         // foreach($typeCount as $type=>$value){
         //    array_push($data, array('value' => $value));
         //    array_push($arrData['dataset'], array('seriesname'=>$type, 'data' => $data));
         // }

         $arrData["data"] = array();
         foreach($typeCount as $type=>$value){
            array_push($arrData["data"], 
               array(
                  "label" => $type,
                  "value" => $value
               )
            );
         }

         createChartObj($arrData, $chartType)->render();
      }

      disconnectServer($conn);
      unset($results);
      unset($eventTypes);
      unset($typeCount);
      unset($category);
      unset($arrData);
      mysqli_free_result($bluejCloseEvents);
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
      global $db;
      $conn = connectToLocal($db);
      $result = getResult($conn, $query);

      if($result){
         $arrData = array("chart" => initChartProperties());
         $chartType = "column2D";
         $propertiesToChange = array(
            "caption" => "Total Sessions Per User ID",
            "xAxisName"=> "User IDs",
            "yAxisName"=> "Number of Sessions",
            "paletteColors" => "#0075c2",
            "bgColor" => "#ffffff",
            "showXAxisLine"=> "1",
            "showlegend" => "1",
         );

         modifyMultiProperties($arrData["chart"], $propertiesToChange);

         $arrData["data"] = array();
         while($row = $result->fetch_array()) {
            array_push($arrData["data"], 
               array(
                  "label" => "UserID: " . $row["user_id"],
                  "value" => $row["count"]
               )
            );
         }

         createChartObj($arrData, $chartType)->render();
         disconnectServer($conn);
      }
      /////////////////////////////////////////////
   }

   function durationOfSpaceSmasherAPI(){
      // //Question:
      // //Do any students go into the API and modify it? 
      // //If so, how long do they spend on it?

      // //Answer: 
      // //

      // //Implication of answer:
      // //

      // //Answer's correctness: 
      // //

      // //Methods for improving correctness: 
      // //
      global $db;
      $conn = connectToLocal($db);

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
      global $db;
      $conn = connectToLocal($db);
      $useridList = getResult($conn, $query);
      disconnectServer($conn);
      return $useridList;
   }

?>