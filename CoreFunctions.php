<!-- <script src="sorttable.js"></script> -->

<?php
   date_default_timezone_set("America/Los_Angeles");
   $endLine = "\n";
   $logLevel = 1;
   $dbToUpdate = 'capstoneLocal';

   function getStartEndDate(){
      global $argv;
      if(count($argv) == 3){
         $startDate = $argv[1];
         $endDate = $argv[2];
         $dateArray = array($startDate, $endDate);
         return $dateArray;
         // echo $startDate . "\n" . $endDate . "\n";
      } else if(isset($_GET['startDate']) && isset($_GET['endDate'])){
         $startDate = $_GET['startDate'];
         $endDate = $_GET['endDate'];
         $dateArray = array($startDate, $endDate);
         return $dateArray;
         // echo $startDate . "\n" . $endDate . "\n";
      } else {
         // echo "No arguments passed, script ended\n";
         printLog("No arguments passed, script ended\n");
         exit();
      }
   }

   function connectToBlackBox(){
      global $endLine;

      $servername = "127.0.0.1:3307";
      $username = "whitebox";
      $password = "ivorycube";
      $database = "blackbox_production";

      //Create connection
      $conn = new mysqli($servername, $username, $password, $database);

      //Check connection
      checkConnection($conn);

      // if($conn->connect_error){
      //    die("Connection failed, please make sure you are connected to the server" . $endLine);
      //    exit();
      // }
      // echo "Connected to Blackbox!\n<br>**********************\n<br>";
      return $conn;
   }

   function connectToLocal($database){
      $servername = "127.0.0.1";
      $username = "root";
      $password = "26064228";

      //Create connection
      $conn = new mysqli($servername, $username, $password, $database);

      //Check connection
      if($conn->connect_error){
         die("FAILEDDDDDDDDD " . $conn->connect_error);
      }
      // echo "Connected to Blackbox!\n<br>**********************\n<br>";
      return $conn;
   }

   function disconnectServer($connection){
      $connection->close();
      // echo "*********************\n<br>Connection closed...\n<br>";
   }

   function deleteEventFromDate($conn, $date){
      //removes every row before the specified date from master_events table
      $query = "delete from master_events where created_at < '" . $date . "'";
      $conn->query($query);
   }

   function updateLocal($updateFileName){
      global $dbToUpdate;
      global $endLine;

      // echo $updateFileName . $endLine;
      if(file_exists($updateFileName)){
         $conn = connectToLocal($dbToUpdate);
         //Removes "_out.csv" to get table name
         $tableName = substr($updateFileName, 0, -8);
         // echo "Table is " . $tableName . $endLine;

         $updateLocal = "LOAD DATA LOCAL infile '" .$updateFileName. "'
                           into table " .$tableName. " 
                           fields  terminated by ',' 
                           enclosed by '\"' 
                           lines terminated by '\\n'";
                           //ignore 1 lines";
         // echo $updateLocal;
         // echo "Updating " . $updateFileName . " to " . $tableName . " table" . $endLine;
         printLog("Updating " . $updateFileName . " to " . $tableName . " table");
         $result = $conn->query($updateLocal);
         if($result){
            // echo "Update complete" . $endLine;
            printLog("Update complete");
            return TRUE;
         } else {
            // echo "Update failed " .$conn->error. $endLine;
            printLog("Update failed " .$conn->error);
            return FALSE;
         }
      } else {
         // echo $updateFileName . " does not exist" . $endLine;
         printLog($updateFileName . " does not exist");
         return FALSE;
      }
      disconnectServer($conn);
   }

   function getResult($connection, $query){
      global $endLine;

      checkConnection($connection);
      $results = $connection->query($query);
      if(!$results){
         die("Invalid query, please make sure query is correct with all the right parameters" . $endLine);
         exit();
      } 
      
      return $results;
   }

   function getResultArray($connection, $query, $field){
      $results = getResult($connection, $query);
      $resultArray = array();
      if($results != null){
         while($row = $results->fetch_assoc()){
            array_push($resultArray, $row[$field]);
         }
         mysqli_free_result($results);
      } 
      return $resultArray;
   }
   
   function objToArray($results, $colName){
      $resultArray = array();
      if($results != null){
         while($row = $results->fetch_assoc()){
            array_push($resultArray, $row[$colName]);
         }
      } 
      return $resultArray;
   }

   function getPid($connection){
      //Query
      $sql = "SELECT p.participant_identifier FROM (SELECT @experiment:='uwbmcss595') UNUSED, participant_identifiers_for_experiment p";
      
      $results = $connection->query($sql);
      $participantList = array();

      if($results->num_rows > 0){
         //output data of each row
         $i = 0;
         // while($row = $results->fetch_row()){
         while($row = $results->fetch_assoc()){
            //echo "ID: " . $row[0] . $endLine;

            $participantList[$i] = $row["participant_identifier"];
            // $participantList[$i] = $row[0];
            $i++;
         }
      } else {
         echo "No result\n<br>";
      }
      return $participantList;
   }

   function checkConnection($conn){
      global $endLine;
      // if ($conn->connect_error) {
      //    die("Connection failed: " . $conn->connect_error);
      //    exit();
      // } 
      if($conn->connect_error || !mysqli_ping($conn)){
         die("Connection failed, please make sure you are connected to the server" . $endLine);
         exit();
      }
   }

   function getTableContents($conn, $ids, $table, $field){
      global $endLine;

      if(count($ids) > 0){
         // $fileName = "output/". $table . "_out.csv";
         $fileName = $table . "_out.csv";
         printLog("Created CSV file with filename of " . $fileName);
         $fp = fopen($fileName, 'w');

         foreach($ids as $id){
            $query = "SELECT * From " . $table . " WHERE " . $field . "= '".$id."'";
            $results = getResult($conn, $query);

            if($results->num_rows > 0){
               //returns the result object if it's not null
               foreach ($results as $val) {
                  fputcsv($fp, $val);       
               }
            }
            // mysqli_free_result($results);
         }

         fclose($fp);
         printLog($table . " table download completed");
         return $fileName;
      }
   }

   function saveToCsv($filename, $results){
      if($filename != null && $results != null){
         $fp = fopen($filename, 'w');

         // Write field name
         $fieldNames = getFieldNames($results);
         fputcsv($fp, $fieldNames);

         foreach ($results as $val) {
            fputcsv($fp, $val); 
         }

         fclose($fp);
         echo "Output generated!\n<br>";
      } else {
         echo "Missing a filename or empty data passed in...\n<br>";
      }
   }

   function getBenchObjects($conn, $results){
      $fileCreated = getTableContents($conn, $results, "bench_objects", "package_id");
      return $fileCreated;
      /*
      if($results->num_rows > 0){
         $fileName = $table . "_out.csv";
         $fp = fopen($fileName, 'w');
         foreach($results as $session){
            // echo $user['user_id'] . $endLine;
            #$query = "SELECT * From bench_objects WHERE package_id= '".$session['package_id']."'";
            $query = "SELECT * From " . $table . " WHERE " . $field . "= '".$session['package_id']."'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getBenchObjectsFixture($conn, $results){
      // getBenchObjects($conn, $results, "bench_objects_fixtures", "bench_object_id");
      $fileCreated = getTableContents($conn, $results, "bench_objects_fixtures", "bench_object_id");
      return $fileCreated;
      /*if($results->num_rows > 0){
         $fp = fopen("bench_objects_fixture_out.csv", 'w');
         foreach($results as $session){
            // echo $session['id'] . $endLine;
            $query = "SELECT * From bench_objects_fixtures WHERE bench_object_id= '".$session['id']."'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }*/
   }

   function getBreakpoints($conn, $ids){
      $fileCreated = getTableContents($conn, $ids, "breakpoints", "source_file_id");
      return $fileCreated;
      /*
      if($results->num_rows > 0){
         $fp = fopen("breakpoints_out.csv", 'w');
         foreach($results as $sourceId){
            // echo $user['user_id'] . $endLine;
            $query = "SELECT * From breakpoints WHERE source_file_id= '".$sourceId['id']."'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getClientAddressId($conn, $results){
      $fileCreated = getTableContents($conn, $results, "client_addresses", "id");
      return $fileCreated;
      /*
      if($results->num_rows > 0){
         $fp = fopen("client_address_out.csv", 'w');
         foreach($results as $client){
            // echo $client['client_address_id'] . $endLine;
            $query = "SELECT * From client_addresses WHERE id= '".$client['client_address_id']."'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);

            printQueryResults($results);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getCodePadEvents($conn, $results){
      $fileCreated = getTableContents($conn, $results, "codepad_events", "id");
      return $fileCreated;
      /*
      if($results->num_rows > 0){
         $fp = fopen("codepad_events_out.csv", 'w');
         foreach($results as $event){
            echo $event['event_id'] . $endLine;
            $query = "SELECT * From codepad_events WHERE id = '".$event['event_id']."'";
            echo $query . $endLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getCompileEvents($conn, $results){
      $fileCreated = getTableContents($conn, $results, "compile_events", "id");
      return $fileCreated;
      /*
      if($results->num_rows > 0){
         $fp = fopen("compile_events_out.csv", 'w');
         foreach($results as $sourceId){
            echo $user['user_id'] . $endLine;
            $query = "SELECT * From compile_events WHERE id='".$sourceId['compile_event_id']."'";
            echo $query . $endLine;
            // $results = getResult($conn, $query);

            // foreach ($results as $val) {
            //    fputcsv($fp, $val);       
            // }
         }
         // fclose($fp);
      }
      */
   }

   function getCompileInputs($conn, $results){
      $fileCreated = getTableContents($conn, $results, "compile_inputs", "source_file_id");
      return $fileCreated;
      /*
      if($results->num_rows > 0){
         $fp = fopen("compile_inputs_out.csv", 'w');
         foreach($results as $sourceId){
            // echo $user['user_id'] . $endLine;
            $query = "SELECT * From compile_inputs WHERE source_file_id= '".$sourceId['id']."'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getCompileOutputs($conn, $results){
      $fileCreated = "compile_outputs_out.csv";
      if($results->num_rows > 0){
         $fp = fopen($fileCreated, 'w');
         foreach($results as $sourceId){
            // echo $user['user_id'] . $endLine;
            $query = "SELECT * From compile_outputs WHERE source_file_id= '".$sourceId['source_file_id']."' and compile_event_id='".$sourceId['compile_event_id']."'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      return $fileCreated;
   }

   function getDebuggerEvents($conn, $results){  
      $fileCreated = getTableContents($conn, $results, "debugger_events", "id");
      return $fileCreated;
      /*
      if($results->num_rows > 0){
         $fp = fopen("debugger_events_out.csv", 'w');
         foreach($results as $event){
            // echo $event['event_id'] . $endLine;
            $query = "SELECT * From debugger_events WHERE id = '".$event['event_id']."'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getDebuggerStackEntries($conn, $results){
      if(count($results) > 0){
         $fp = fopen("stack_entries_out.csv", 'w');
         printLog("Created CSV file with filename of stack_entries_out.csv");

         foreach($results as $event){
            $query = "SELECT * From stack_entries WHERE sub_event_type= 'DebuggerEvent' and sub_event_id= '".$event."'";
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
         printLog("stack_entries table with DebuggerEvent download completed");
      }
   }

   function getInvocationStackEntries($conn, $results){
      if(count($results) > 0){
         $fp = fopen("stack_entries_out.csv", 'w');
         printLog("Created CSV file with filename of stack_entries_out.csv");

         foreach($results as $event){
            $query = "SELECT * From stack_entries WHERE sub_event_type= 'Invocation' and sub_event_id= '".$event."'";
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
         printLog("stack_entries table with InvocationEvent download completed");
      }
   }

   function getExtensions($conn, $results){
      $fileCreated = getTableContents($conn, $results, "extensions", "master_event_id");
      return $fileCreated;
      /*
      if($results->num_rows > 0){
         $fp = fopen("extensions_out.csv", 'w');
         foreach($results as $event){
            // echo $event['id'] . $endLine;
            $query = "SELECT * From extensions WHERE master_event_id = '".$event['id']."'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);
            
            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getFieldNames($results){
      $fieldNames = array();

      if($results->num_rows > 0){
         $i = 0;
         while($fieldinfo = mysqli_fetch_field($results)){
            //echo $fieldinfo->name . " ";
            $fieldNames[$i] = $fieldinfo->name;
            $i++;
         }
         //echo "\n<br>";
      }
      // else {
      //    echo "No results\n";
      // }

      return $fieldNames;
   }

   function getFixtures($conn, $results){
      $fileCreated = getTableContents($conn, $results, "fixtures", "source_file_id");
      return $fileCreated;
      /*
      if($results->num_rows > 0){
         $fp = fopen("fixtures_out.csv", 'w');
         foreach($results as $session){
            // echo $user['user_id'] . $endLine;
            $query = "SELECT * From fixtures WHERE source_file_id= '".$session['id']."'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getIspectors($conn, $ids){
      $fileCreated = getTableContents($conn, $ids, "inspectors", "session_id");
      return $fileCreated;
      /*
      if($userIds->num_rows > 0){
         $fp = fopen("inspectors_out.csv", 'w');
         foreach($userIds as $session){
            echo "Querying for Session " . $session['id'] . "<br>\n";
            $query = "SELECT * From inspectors WHERE session_id= '".$session['id']."'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);

            echo "Writing Sesssion " . $session['id'] . "<br>\n";
            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getInstallations($conn, $results){
      $fileCreated = getTableContents($conn, $results, "installation_details", "id");
      return $fileCreated;
      /*
      if($results->num_rows > 0){
         $fp = fopen("installation_out.csv", 'w');
         foreach($results as $installation_details_id){
            // echo $user['user_id'] . $endLine;
            $query = "SELECT * From installation_details WHERE id= '".$installation_details_id['installation_details_id']."'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getInvocations($conn, $ids){
      $fileCreated = getTableContents($conn, $ids, "invocations", "session_id");
      return $fileCreated;
      /*
      //This event has a package_id field, which indicates which package window the method was invoked from
      if($results->num_rows > 0){
         $fp = fopen("invocation_out.csv", 'w');
         foreach($results as $session){
            // echo $user['user_id'] . $endLine;
            $query = "SELECT * From invocations WHERE session_id= '".$session['id']."'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getMasterEvents($conn, $userIds, $startDate, $endDate){
      $table = "master_events";
      $field = "user_id";
      // $fileCreated = getTableContents($conn, $userIds, $table, $field, $field);
      global $endLine;

      if(count($userIds) > 0){
         $fileName = $table . "_out.csv";
         echo "Created CSV file with filename of " . $fileName . $endLine;
         $fp = fopen($fileName, 'w');

         foreach($userIds as $id){
            // echo $result[$result_id] . $endLine;
            $query = "SELECT * From " . $table . " WHERE " . $field . "= '".$id."' and created_at between '" . $startDate . "' and '" . $endDate . "'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);

            if($results->num_rows > 0){
               //returns the result object if it's not null
               // echo "Writing " . $field ." ". $id[$result_id] . " to CSV file" . $endLine;
               foreach ($results as $val) {
                  fputcsv($fp, $val);       
               }
            }
         }
         fclose($fp);
         echo $table . " table download completed" . $endLine;
         return $fileName;
      } else {
         echo "Empty ids ..." . $endLine;
      }
      // return $fileCreated;
   }

   function getPackages($conn, $ids){
      $fileCreated = getTableContents($conn, $ids, "packages", "project_id");
      return $fileCreated;
      /*
      if($results->num_rows > 0){
         $fp = fopen("packages_out.csv", 'w');
         foreach($results as $project){
            // echo $user['user_id'] . $endLine;
            $query = "SELECT * From packages WHERE project_id= '".$project['project_id']."'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getProjects($conn, $results){
      $fileCreated = getTableContents($conn, $results, "projects", "user_id");
      return $fileCreated;
      /*
      if($results->num_rows > 0){
         $fp = fopen("projects_out.csv", 'w');
         foreach($results as $user){
            // echo $user['user_id'] . $endLine;
            $query = "SELECT * From projects WHERE user_id= '".$user['user_id']."'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getSessions($conn, $results){
      $fileCreated = getTableContents($conn, $results, "sessions", "id");
      return $fileCreated;
      /*
      if($results->num_rows > 0){
         $fp = fopen("sessions_out.csv", 'w');
         foreach($results as $session){
            // echo $user['user_id'] . $endLine;
            $query = "SELECT * From sessions WHERE id= '".$session['id']."'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getSourceFiles($conn, $results){
      $fileCreated = getTableContents($conn, $results, "source_files", "project_id");
      return $fileCreated;
      /*
      if($results->num_rows > 0){
         $fp = fopen("source_file_out.csv", 'w');
         foreach($results as $project){
            // echo $user['user_id'] . $endLine;
            $query = "SELECT * From source_files WHERE project_id= '".$project['project_id']."'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getSourceHashes($conn, $results){
      $fileCreated = getTableContents($conn, $results, "source_hashes", "id");
      return $fileCreated;
      /*
      if($results->num_rows > 0){
         $fp = fopen("source_hashes_out.csv", 'w');
         foreach($results as $project){
            // echo $user['user_id'] . $endLine;
            $query = "SELECT * From source_hashes WHERE id= '".$project['package_id']."'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getSourceHistories($conn, $ids){
      $fileCreated = getTableContents($conn, $ids, "source_histories", "source_file_id");
      return $fileCreated;
      /*
      $fileCreated = "source_histories_out.csv";

      if($ids->num_rows > 0){
         $fp = fopen("source_histories_out.csv", 'w');
         foreach($ids as $sourceId){
            // echo $user['user_id'] . $endLine;
            // $query = "SELECT id, master_event_id, source_file_id, source_history_type, old_source_file_id From source_histories WHERE source_file_id= '".$sourceId['id']."'";
            $query = "SELECT * From source_histories WHERE source_file_id= '".$sourceId['id']."'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);

            // printQueryResults($results); echo "<br><br>";

            foreach ($results as $val) {
               fputcsv($fp, $val);       
               // print_r($val);
            }
         }
         fclose($fp);
      }

      return $fileCreated;
      */
   }

   function getTests($conn, $results){
      $fileCreated = getTableContents($conn, $results, "tests", "session_id");
      return $fileCreated;
      /*
      if($results->num_rows > 0){
         $fp = fopen("tests_out.csv", 'w');
         foreach($results as $event){
            // echo $event['id'] . $endLine;
            $query = "SELECT * From tests WHERE session_id = '".$event['id']."'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);
            
            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getTestResults($conn, $results){
      $fileCreated = getTableContents($conn, $results, "test_results", "session_id");
      return $fileCreated;
      /*
      if($results->num_rows > 0){
         $fp = fopen("test_results_out.csv", 'w');
         foreach($results as $event){
            // echo $event['id'] . $endLine;
            $query = "SELECT * From test_results WHERE session_id = '".$event['id']."'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);
            
            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getUsers($conn, $userIds){
      $fileCreated = getTableContents($conn, $userIds, "users", "id");
      return $fileCreated;
      /*
      if($userIds->num_rows > 0){
         $fp = fopen("users_out.csv", 'w');
         foreach($userIds as $user){
            // echo $user['user_id'] . $endLine;
            $query = "SELECT * From users WHERE id= '".$user['user_id']."'";
            // echo $query . $endLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function printResultInTable($results){
      echo "<table border=1 class = 'sortable'>";
      $fields = getFieldNames($results);
      printArray($fields, true);
      printQueryResults($results);
      echo "</table><br>";  
   }

   function printQueryResults($results){
      // echo "<table border='1'>";
      if($results->num_rows > 0){
         // echo "Have ". mysqli_num_rows($results)." of results<br>";
         while($row = $results->fetch_assoc()){
            echo "<tr>";
            foreach($row as $field){
               if($field == null)
                  echo "<td>" . "null " . "</td>";
               else 
                  echo "<td>" . $field . "</td>";
            }
            echo "</tr>";
         }
      } 
      // else {
      //    echo "<tr>No result\n</tr>"; 
      // }
      // echo "</table>";
   }

   function printArray($results, $isFieldname){
      if($results != null){
         // if($isFieldname)
         //    echo "<table border='1'><tr>";
         // else 
         //    echo "<table border='1'>";

         foreach ($results as $p){
            if($isFieldname)
               echo "<td>" . $p . "</td>";
            else 
               echo "<tr><td>" . $p . "</td></tr>";
         }

         // if($isFieldname)
         //    echo "<tr></table>";
         // else 
         //    echo "</table>";

      } 
      // else {
      //    echo "<tr>No results\n</td>";
      // }
   }

   function isTableEmpty($conn, $tableName){
      $query = "Select count(id) as count from " . $tableName;
      $result = getResult($conn, $query);
      
      while($numOfRows = $result->fetch_assoc()){
         $row = $numOfRows['count'];
      }

      if($row > 0){
         return FALSE;
      } else {
         return TRUE;
      }
   }

   function printLog($str, $level=1){
      global $logLevel, $endLine;

      if ($level <= $logLevel)
         echo $str . ": " . date("Y-m-d h:i:a") . $endLine;
   }

   function saveToFile($fileName, $status) {
      file_put_contents($fileName, serialize($status));
   }

   function writeCheckpoint(&$checkPoint, $key){
      $checkPoint[$key] = 1;
      $checkPoint["started"] = 1;
      saveToFile("checkpoint", $checkPoint);
      // file_put_contents($fileName, serialize($status));
   }

   function restoreFromFile($fileName){
      $data = array();

      if(file_exists($fileName)){
         $data = file_get_contents($fileName);
         if(empty($data))
            $data = array();
         else
            $data = unserialize($data);
      }  
      return $data;
   }

   function readCheckpoint(){
      return restoreFromFile("checkpoint");
   }
?>