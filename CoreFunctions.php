<?php
   date_default_timezone_set("America/Los_Angeles");
   $endLine = "\n";
   // //Higher level outputs finer debug messages
   $logLevel = 1;
   $dbToUpdate = 'capstoneLocal';

   $root = '../';
   $directory = 'csv/';

   // //Read status from downloadStatus files
   $useridFile = "useridFile";
   $nonUniqueUserFile = "nonUniqueUser";
   $sessionidFile = "sessionidFile";
   $projectidFile = "projectidFile";
   $sourceFileidFile = "sourceFileidFile";

   //////UTILITY//////
   // //Connects to Blackbox
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

      return $conn;
   }

   // //Connects to local database
   function connectToLocal($database){
      $servername = "127.0.0.1";
      $username = "root";
      $password = "26064228";

      //Create connection
      $conn = new mysqli($servername, $username, $password, $database);

      //Check connection
      checkConnection($conn);

      return $conn;
   }

   // //Disconnect from server
   function disconnectServer($connection){
      $connection->close();
      printLog("Connection closed...",2);
   }

   // //Checks connection
   function checkConnection($conn){
      global $endLine;

      if($conn->connect_error || !mysqli_ping($conn)){
         die("Connection failed, please make sure you are connected to the server");
         exit();
      }
   }

   // //Check if date is valid
   function isRealDate($date){
      if(false === strtotime($date)){
         return false;
      } else {
         // //splits $date into individual parts
         list($year, $month, $day) = explode('-',$date);
         if(false === checkdate($month, $day, $year)){
            return false;
         }
      }
      return true;
   }

   // //Handles invalid date entered in GUI page
   function checkInputDate($startDate, $endDate){
      if(isRealDate($startDate) && isRealDate($endDate)){
         $dateArray = array($startDate, $endDate);
         return $dateArray;
      } else {
         printLog("Invalid date entered, please enter date in the format year-month-day (e.g. 2016-01-01)");
         
         //For outputing onto GUI using JSON
         echo '{"error":"Invalid Date"}';
         exit();
      }
   }

   // //Obtain start and end date to download data
   function getStartEndDate(){
      global $argv;
      if(count($argv) == 3){
         //Obtain date from terminal
         $startDate = $argv[1];
         $endDate = $argv[2];
         return checkInputDate($startDate, $endDate);
      } else if(isset($_GET['startDate']) && isset($_GET['endDate'])){
         //Obtain date from brower
         $startDate = $_GET['startDate'];
         $endDate = $_GET['endDate'];
         return checkInputDate($startDate, $endDate);
      } else {
         printLog("No arguments passed, script ended\n");
         // For outputing onto the GUI page via JSON
         echo '{"error":"Invalid Date"}';
         exit();
      }
   }

   // //Populate tables in local database with csv file
   function updateLocal($updateFileName){
      global $dbToUpdate;
      global $endLine;
      global $root;
      global $directory;

      // //Use prefix to trim down updateFileName to get table name
      $prefix = $root . $directory;
   
      printlog("Update file name is: " . $updateFileName . $endLine, 2);

      if(file_exists($updateFileName)){
         $conn = connectToLocal($dbToUpdate);
         // Removes "_out.csv" to get table name
         $tableName = substr($updateFileName, strlen($prefix), -8);
         echo "Table is " . $tableName . $endLine;

         $updateLocal = "LOAD DATA LOCAL infile '" .$updateFileName. "'
                           into table " .$tableName. " 
                           fields  terminated by ',' 
                           enclosed by '\"' 
                           lines terminated by '\\n'";
                           //ignore 1 lines";
         
         printLog("Updating " . $updateFileName . " to " . $tableName . " table") . $endLine;
         
         // //Updates Local Database using query
         $result = $conn->query($updateLocal);

         if($result){
            printLog("Update complete");
            return TRUE;
         } else {
            printLog("Update failed " . $conn->error);
            return FALSE;
         }
      } else {
         printLog($updateFileName . " does not exist");
         return FALSE;
      }
      disconnectServer($conn);
   }

   // //Returns query result set as MySQLi object
   function getResult($connection, $query){
      global $endLine;

      checkConnection($connection);
      $results = $connection->query($query);
      if(!$results){
         die("Invalid query, please make sure query is correct with all the right parameters: " . $query . $endLine);
         exit();
      } 
      printLog("Got results", 2);
      
      return $results;
   }

   // //Returns query result as array
   function getResultArray($connection, $query, $field){
      $results = getResult($connection, $query);
      $resultArray = array();
      if($results != null){
         while($row = $results->fetch_assoc()){
            array_push($resultArray, $row[$field]);
         }
         mysqli_free_result($results);
         printLog("Free result memory", 2);
      } 
      return $resultArray;
   }

   // //Return table content and write to csv file 
   function getTableContents($conn, $ids, $table, $field){
      global $endLine;
      global $root;
      global $directory;

      if(count($ids) > 0){
         $fileName = $root . $directory . $table . "_out.csv";
         printLog("Created CSV file with filename of " . $fileName );
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
         }

         fclose($fp);
         printLog($table . " table download completed" );
         return $fileName;
      }
   }

   // //Obtain field name from a MySQLi result data object in an array
   function getFieldNames($results){
      $fieldNames = array();

      if($results->num_rows > 0){
         $i = 0;
         while($fieldinfo = mysqli_fetch_field($results)){
            $fieldNames[$i] = $fieldinfo->name;
            $i++;
         }
      }

      return $fieldNames;
   }

   // //Log function, set level to higher number for different granularity of debugging
   function printLog($str='', $level=1){
      global $logLevel, $endLine;

      if ($level <= $logLevel){
         $logStr = $str . ": " . date("Y-m-d h:i:a");
         if ($level > 1)
            $logStr .= " " . round(memory_get_usage()/1048576,2) . "MB";

         echo $logStr . $endLine;
      }
   }

   // //Writes to file
   function saveToFile($fileName, $status) {
      global $root;
      // //Data is serialized before writing to file
      file_put_contents($root . "checkpoints/" . $fileName, serialize($status));
   }

   // //Update checkpoint array to file
   function writeCheckpoint(&$checkPoint, $key){
      global $endLine;
      $checkPoint[$key] = 1;
      $checkPoint["started"] = 1;
      saveToFile("checkpoint", $checkPoint);
      
      // To ensure completion of one table and the beginning of the next table in terminal   
      echo $endLine;
   }

   // //Read from a file 
   function restoreFromFile($fileName){
      global $root;

      $data = array();
      // //Restore will be from checkpoint folder
      $fileName = $root . "checkpoints/" . $fileName;
      printlog("Restoring from " . $fileName, 2);

      if(file_exists($fileName)){
         printlog("Found file " . $fileName . ", restoring...", 2);
         $data = file_get_contents($fileName);
         if(empty($data))
            $data = array();
         else
            // //Unserialize data when reading from file
            $data = unserialize($data);
      }  
      return $data;
   }

   // //Read checkpoint file
   function readCheckpoint(){
      return restoreFromFile("checkpoint");
   }

   // //Calculate duration of any given two times
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

   //////Table Downloads//////

      // //Download master_events table
   function getMasterEvents($conn, $userIds, $startDate, $endDate){
      $table = "master_events";
      $field = "user_id";
      
      global $endLine;
      global $root;
      global $directory;

      if(count($userIds) > 0){
         $fileName = $root . $directory . $table . "_out.csv";
         
         printlog("Created CSV file with filename of " . $fileName );
         
         $fp = fopen($fileName, 'w');

         // //Iterate through each userid to obtain their triggered events in the master_events table
         foreach($userIds as $id){
            $query = "SELECT * From " . $table . " WHERE " . $field . "= '".$id[$field]."' and participant_id = '".$id['participant_id'] . "' and created_at between '" .$startDate. "' and '" .$endDate. "'";
            $results = getResult($conn, $query);

            if($results->num_rows > 0){
               // //Returns the result object if it's not null
               foreach ($results as $val) {
                  fputcsv($fp, $val);       
               }
            }
         }
         fclose($fp);
         printlog($table . " table download completed");
         // //Free up php memory
         mysqli_free_result($results);

         return $fileName;
      } else {
         printlog("Empty ids ...");
      }
   }
   
   // //Download bench_objects table
   function getBenchObjects($conn, $results){
      $fileCreated = getTableContents($conn, $results, "bench_objects", "package_id");
      return $fileCreated;
   }

   // //Download bench_objects_fixtures table
   function getBenchObjectsFixture($conn, $results){
      $fileCreated = getTableContents($conn, $results, "bench_objects_fixtures", "bench_object_id");
      return $fileCreated;
   }

   // //Download breakpoints table
   function getBreakpoints($conn, $ids){
      $fileCreated = getTableContents($conn, $ids, "breakpoints", "source_file_id");
      return $fileCreated;
   }

   // //Download client_addresses table
   function getClientAddressId($conn, $results){
      $fileCreated = getTableContents($conn, $results, "client_addresses", "id");
      return $fileCreated;
   }

   // //Download codepad_events table
   function getCodePadEvents($conn, $results){
      $fileCreated = getTableContents($conn, $results, "codepad_events", "id");
      return $fileCreated;
   }

   // //Download compile_events table
   function getCompileEvents($conn, $results){
      global $root;
      global $directory;

      $fileCreated = $root . $directory . "compile_events_out.csv";
      if($results->num_rows > 0){
         // //Opens filestream 
         $fp = fopen($fileCreated, 'w');
         foreach($results as $sourceId){
            $query = "SELECT * From compile_events WHERE id='".$sourceId['compile_event_id']."'";
            $results = getResult($conn, $query);

            // //Writes to stream
            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      return $fileCreated;
   }

   // //Download compile_inputs table
   function getCompileInputs($conn, $results){
      $fileCreated = getTableContents($conn, $results, "compile_inputs", "source_file_id");
      return $fileCreated;
   }

   // //Download compile_outputs table
   function getCompileOutputs($conn, $results){
      global $root;
      global $directory;

      $fileCreated = $root . $directory . "compile_outputs_out.csv";
      if($results->num_rows > 0){
         // //Open filestream
         $fp = fopen($fileCreated, 'w');
         foreach($results as $sourceId){
            $query = "SELECT * From compile_outputs WHERE source_file_id= '".$sourceId['source_file_id']."' and compile_event_id='".$sourceId['compile_event_id']."'";
            $results = getResult($conn, $query);

            // //Writes to stream
            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      return $fileCreated;
   }

   // //Download debugger_events table
   function getDebuggerEvents($conn, $results){  
      $fileCreated = getTableContents($conn, $results, "debugger_events", "id");
      return $fileCreated;
   }

   // //Download stack_entries table with debuggerEvent in it
   function getDebuggerStackEntries($conn, $results){
      global $root;
      global $directory;

      if(count($results) > 0){
         $fileName = $root . $directory . "stack_entries_out.csv";
         printLog("Created CSV file with filename of " . $fileName);
         // //Open filestream
         $fp = fopen($fileName, 'w');

         foreach($results as $event){
            $query = "SELECT * From stack_entries WHERE sub_event_type= 'DebuggerEvent' and sub_event_id= '".$event."'";
            $results = getResult($conn, $query);

            // //Writes to stream
            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
         printLog("stack_entries table with DebuggerEvent download completed");
         return $fileName;
      }
   }

   // //Download stack_entries table with Invocation in it
   function getInvocationStackEntries($conn, $results){
      global $root;
      global $directory;

      if(count($results) > 0){
         $fileName = $root . $directory . "stack_entries_out.csv";
         printLog("Created CSV file with filename of " . $fileName);
         // //Open filestream
         $fp = fopen($fileName, 'w');

         foreach($results as $event){
            $query = "SELECT * From stack_entries WHERE sub_event_type= 'Invocation' and sub_event_id= '".$event."'";
            $results = getResult($conn, $query);

            // //Writes to stream
            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
         printLog("stack_entries table with InvocationEvent download completed");
         return $fileName;
      }
   }

   // //Download extensions table
   function getExtensions($conn, $results){
      $fileCreated = getTableContents($conn, $results, "extensions", "master_event_id");
      return $fileCreated;
   }

   // //Download fixtures table
   function getFixtures($conn, $results){
      $fileCreated = getTableContents($conn, $results, "fixtures", "source_file_id");
      return $fileCreated;
   }

   // //Download inspectors table
   function getIspectors($conn, $ids){
      $fileCreated = getTableContents($conn, $ids, "inspectors", "session_id");
      return $fileCreated;
   }

   // //Download installation_details table
   function getInstallations($conn, $results){
      $fileCreated = getTableContents($conn, $results, "installation_details", "id");
      return $fileCreated;
   }

   // //Download invocations table
   function getInvocations($conn, $ids){
      $fileCreated = getTableContents($conn, $ids, "invocations", "session_id");
      return $fileCreated;
   }

   // //Download packages table
   function getPackages($conn, $ids){
      $fileCreated = getTableContents($conn, $ids, "packages", "project_id");
      return $fileCreated;
   }

   // //Download projects table
   function getProjects($conn, $results){
      global $root;
      global $endLine;
      global $directory;

      $table = 'projects';
      $field = 'user_id';

      if(count($results) > 0){
         $fileName = $root . $directory .$table . "_out.csv";
         printLog("Created CSV file with filename of " . $fileName);
         // //Open filestream
         $fp = fopen($fileName, 'w');

         // iterate through each MySQLi result data to obtain project table
         foreach($results as $id => $value){
            $query = "SELECT * From " . $table . " WHERE " . $field . "= '".$value[$field]."'";
            $results = getResult($conn, $query);

            if($results->num_rows > 0){
               // //Returns the result object if it's not null
               // //Writes to stream
               foreach ($results as $val) {
                  fputcsv($fp, $val);       
               }
            }
         }

         fclose($fp);
         printLog($table . " table download completed");

         // //Free up php memory
         unset($results);

         return $fileName;
      } else {
         printLog($table . " table was not downloaded");
      }
   }

   // //Download sessions table
   function getSessions($conn, $results){
      $fileCreated = getTableContents($conn, $results, "sessions", "id");
      return $fileCreated;
   }

   // //Download source_files table
   function getSourceFiles($conn, $results){
      $fileCreated = getTableContents($conn, $results, "source_files", "project_id");
      return $fileCreated;
   }

   // //Download source_hashes table
   function getSourceHashes($conn, $results){
      $fileCreated = getTableContents($conn, $results, "source_hashes", "id");
      return $fileCreated;
   }

   // //Download source_histories table
   function getSourceHistories($conn, $ids){
      $fileCreated = getTableContents($conn, $ids, "source_histories", "source_file_id");
      return $fileCreated;
   }

   // //Download tests table
   function getTests($conn, $results){
      $fileCreated = getTableContents($conn, $results, "tests", "session_id");
      return $fileCreated;
   }

   // //Download test_results table
   function getTestResults($conn, $results){
      $fileCreated = getTableContents($conn, $results, "test_results", "session_id");
      return $fileCreated;
   }

   // //Download users table
   function getUsers($conn, $userIds){
      global $endLine;
      global $root;
      global $directory;

      $table = 'users';
      $field = 'id';

      if(count($userIds) > 0){
         $fileName = $root . $directory . $table . "_out.csv";
         printLog("Created CSV file with filename of " . $fileName);
         // //Open filestream
         $fp = fopen($fileName, 'w');

         foreach($userIds as $id => $value){
            $query = "SELECT * From " . $table . " WHERE " . $field . "= '".$value['user_id']."'";
            $results = getResult($conn, $query);

            if($results->num_rows > 0){
               //returns the result object if it's not null
               foreach ($results as $val) {
                  // //Writes to stream
                  fputcsv($fp, $val);       
               }
            }
         }

         fclose($fp);
         printLog($table . " table download completed");
         return $fileName;
      } else {
         printLog($table . " table was not downloaded");
      }
   }

   //not using
   function printResultInTable($results){
      echo "<table border=1 class = 'sortable'>";
      $fields = getFieldNames($results);
      printArray($fields, true);
      printQueryResults($results);
      echo "</table><br>";  
   }

   //not using
   function printQueryResults($results){
      if($results->num_rows > 0){
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
   }

   //not using
   function printArray($results, $isFieldname){
      if($results != null){
         foreach ($results as $p){
            if($isFieldname)
               echo "<td>" . $p . "</td>";
            else 
               echo "<tr><td>" . $p . "</td></tr>";
         }
      } 
   }

   //not using
   // //Check if table in the database is empty
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

   //Not using 
   function getNonUniqueUsers($conn){
      $query = "SELECT distinct user_id, participant_id from master_events";
      
      $results = getResult($conn, $query);
      $nonUniqueUserList = array();
      if($results != null){
         while($row = $results->fetch_assoc()){
            array_push($nonUniqueUserList, array('user_id' => $row['user_id'], 'participant_id' => $row['participant_id']));
         }
      } 
      
      mysqli_free_result($results);
      return $nonUniqueUserList;
   }

   //Not using
   function getUniqueUsers($conn){
      $query = "SELECT distinct s.user_id, s.participant_id, s.participant_identifier FROM (SELECT @experiment:='uwbgtcs') unused, sessions_for_experiment s order by s.participant_id";
      
      $results = getResult($conn, $query);
      $uniqueUserList = array();
      if($results != null){
         while($row = $results->fetch_assoc()){
            array_push($uniqueUserList, array('user_id' => $row['user_id'], 'participant_id' => $row['participant_id']));
         }
      } 

      mysqli_free_result($results);
      return $uniqueUserList;
   }

   // //not using
   function deleteEventFromDate($conn, $date){
      //removes every row before the specified date from master_events table
      $query = "delete from master_events where created_at < '" . $date . "'";
      $conn->query($query);
   }
   
   //not using
   function objToArray($results, $colName){
      $resultArray = array();
      if($results != null){
         while($row = $results->fetch_assoc()){
            array_push($resultArray, $row[$colName]);
         }
      } 
      return $resultArray;
   }

   //not using
   function getPid($connection){
      $sql = "SELECT p.participant_identifier FROM (SELECT @experiment:='uwbmcss595') UNUSED, participant_identifiers_for_experiment p";
      
      $results = $connection->query($sql);
      $participantList = array();

      if($results->num_rows > 0){
         //output data of each row
         $i = 0;
         
         while($row = $results->fetch_assoc()){
            $participantList[$i] = $row["participant_identifier"];

            $i++;
         }
      } else {
         echo "No result\n<br>";
      }
      return $participantList;
   }

   //not using
   function saveToCsv($filename, $results){
      if($filename != null && $results != null){
         $fp = fopen($filename, 'w');

         // Write field name
         $fieldNames = getFieldNames($results);
         // Open filestream
         fputcsv($fp, $fieldNames);

         foreach ($results as $val) {
            fputcsv($fp, $val); 
         }

         fclose($fp);
         printLog("Output generated!\n<br>");
      } else {
         printLog("Missing a filename or empty data passed in...\n<br>");
      }
   }
?>