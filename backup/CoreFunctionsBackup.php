<script src="sorttable.js"></script>

<?php
   $newLine = "\n";

   function connectToBlackBox(){
      $servername = "127.0.0.1:3307";
      $username = "whitebox";
      $password = "ivorycube";
      $db = "blackbox_production";

      //Create connection
      $conn = new mysqli($servername, $username, $password, $db);

      //Check connection
      if($conn->connect_error){
         die("FAILEDDDDDDDDD " . $conn->connect_error);
      }
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

   function updateLocal($updateFileName, $tableName){
      $conn = connectToLocal('capstoneLocal');
      $updateLocal = "LOAD DATA LOCAL infile " .$updateFileName. "
                        into table " .$tableName. " 
                        fields  terminated by ',' 
                        enclosed by '\"' 
                        lines terminated by '\\n'";
                        //ignore 1 lines";

      // echo $updateLocal;
      $conn->query($updateLocal);
      disconnectServer($conn);
   }

   function getResult($connection, $query){
      $results = $connection->query($query);
      return $results;
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
            //echo "ID: " . $row[0] . $newLine;

            $participantList[$i] = $row["participant_identifier"];
            // $participantList[$i] = $row[0];
            $i++;
         }
      } else {
         echo "No result\n<br>";
      }
      return $participantList;
   }

   function getTableContents($conn, $results, $table, $field, $result_id){
      if($results->num_rows > 0){
         $fileName = $table . "_out.csv";
         $fp = fopen($fileName, 'w');
         foreach($results as $result){
            // echo $result[$result_id] . $newLine;
            $query = "SELECT * From " . $table . " WHERE " . $field . "= '".$result[$result_id]."'";
            // echo $query . $newLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
   }

   function getBenchObjects($conn, $results){
      getTableContents($conn, $results, "bench_objects", "package_id", "package_id")
      /*
      if($results->num_rows > 0){
         $fileName = $table . "_out.csv";
         $fp = fopen($fileName, 'w');
         foreach($results as $session){
            // echo $user['user_id'] . $newLine;
            #$query = "SELECT * From bench_objects WHERE package_id= '".$session['package_id']."'";
            $query = "SELECT * From " . $table . " WHERE " . $field . "= '".$session['package_id']."'";
            // echo $query . $newLine;
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
      getTableContents($conn, $results, "bench_objects_fixtures", "bench_object_id", "id");
      /*if($results->num_rows > 0){
         $fp = fopen("bench_objects_fixture_out.csv", 'w');
         foreach($results as $session){
            // echo $session['id'] . $newLine;
            $query = "SELECT * From bench_objects_fixtures WHERE bench_object_id= '".$session['id']."'";
            // echo $query . $newLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }*/
   }

   function getBreakpoints($conn, $results){
      getTableContents($conn, $results, "breakpoints", "source_file_id", "id");
      /*
      if($results->num_rows > 0){
         $fp = fopen("breakpoints_out.csv", 'w');
         foreach($results as $sourceId){
            // echo $user['user_id'] . $newLine;
            $query = "SELECT * From breakpoints WHERE source_file_id= '".$sourceId['id']."'";
            // echo $query . $newLine;
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
      getTableContents($conn, $results, "client_addresses", "id", "client_address_id");
      /*
      if($results->num_rows > 0){
         $fp = fopen("client_address_out.csv", 'w');
         foreach($results as $client){
            // echo $client['client_address_id'] . $newLine;
            $query = "SELECT * From client_addresses WHERE id= '".$client['client_address_id']."'";
            // echo $query . $newLine;
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
      getTableContents($conn, $results, "codepad_events", "id", "event_id");
      /*
      if($results->num_rows > 0){
         $fp = fopen("codepad_events_out.csv", 'w');
         foreach($results as $event){
            echo $event['event_id'] . $newLine;
            $query = "SELECT * From codepad_events WHERE id = '".$event['event_id']."'";
            echo $query . $newLine;
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
      getTableContents($conn, $results, "compile_events", "id", "compile_event_id");
      /*
      if($results->num_rows > 0){
         $fp = fopen("compile_events_out.csv", 'w');
         foreach($results as $sourceId){
            echo $user['user_id'] . $newLine;
            $query = "SELECT * From compile_events WHERE id='".$sourceId['compile_event_id']."'";
            echo $query . $newLine;
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
      getTableContents($conn, $results, "compile_inputs", "source_file_id", "id");
      /*
      if($results->num_rows > 0){
         $fp = fopen("compile_inputs_out.csv", 'w');
         foreach($results as $sourceId){
            // echo $user['user_id'] . $newLine;
            $query = "SELECT * From compile_inputs WHERE source_file_id= '".$sourceId['id']."'";
            // echo $query . $newLine;
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
      
      if($results->num_rows > 0){
         $fp = fopen("compile_outputs_out.csv", 'w');
         foreach($results as $sourceId){
            // echo $user['user_id'] . $newLine;
            $query = "SELECT * From compile_outputs WHERE source_file_id= '".$sourceId['source_file_id']."' and compile_event_id='".$sourceId['compile_event_id']."'";
            // echo $query . $newLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }

   }

   function getDebuggerEvents($conn, $results){  
      getTableContents($conn, $results, "client_addresses", "id", "client_address_id");
      /*
      if($results->num_rows > 0){
         $fp = fopen("debugger_events_out.csv", 'w');
         foreach($results as $event){
            // echo $event['event_id'] . $newLine;
            $query = "SELECT * From debugger_events WHERE id = '".$event['event_id']."'";
            // echo $query . $newLine;
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
      if($results->num_rows > 0){
         $fp = fopen("stack_entries_debugger_out.csv", 'w');
         foreach($results as $event){
            // echo $user['user_id'] . $newLine;
            $query = "SELECT * From stack_entries WHERE sub_event_type= 'DebuggerEvent' and sub_event_id= '".$event['event_id']."'";
            // echo $query . $newLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
   }

   function getExperimentSessions($connection){
      //Query
      $sql = "SELECT s.* FROM (SELECT @experiment:='uwbmcss595') unused, sessions_for_experiment s";
      
      $results = $connection->query($sql);

      // $relatedSessionList = array();

      // if($results->num_rows > 0){
      //    //output data of each row
      //    $i = 0;
      //    // while($row = $results->fetch_row()){
      //    while($row = $results->fetch_assoc()){
      //       //echo "ID: " . $row[0] . $newLine;

      //       $relatedSessionList[$i] = $row["participant_identifier"];
      //       // $participantList[$i] = $row[0];
      //       $i++;
      //    }
      // } else {
      //    echo "No result\n<br>";
      // }
      // return $relatedSessionList;
      return $results;
   }

   function getExtensions($conn, $results){
      getTableContents($conn, $results, "extensions", "master_event_id", "id");
      /*
      if($results->num_rows > 0){
         $fp = fopen("extensions_out.csv", 'w');
         foreach($results as $event){
            // echo $event['id'] . $newLine;
            $query = "SELECT * From extensions WHERE master_event_id = '".$event['id']."'";
            // echo $query . $newLine;
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
      getTableContents($conn, $results, "fixtures", "source_file_id", "id");
      /*
      if($results->num_rows > 0){
         $fp = fopen("fixtures_out.csv", 'w');
         foreach($results as $session){
            // echo $user['user_id'] . $newLine;
            $query = "SELECT * From fixtures WHERE source_file_id= '".$session['id']."'";
            // echo $query . $newLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getIspectors($conn, $userIds){
      getTableContents($conn, $results, "inspectors", "session_id", "id");
      /*
      if($userIds->num_rows > 0){
         $fp = fopen("inspectors_out.csv", 'w');
         foreach($userIds as $session){
            echo "Querying for Session " . $session['id'] . "<br>\n";
            $query = "SELECT * From inspectors WHERE session_id= '".$session['id']."'";
            // echo $query . $newLine;
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

   function getInstallationId($conn){
      $query = "SELECT distinct installation_details_id From sessions where user_id =1110282";

      // $results = getResult($conn, $query);   //get project ID
      $installationDetailList = getResult($conn, $query);   //get project ID
      // $fieldNames = getFieldNames($results);
      // $fieldNames = getFieldNames($installationDetailList);
      // $projectidList = array();

      // if($results->num_rows > 0){
      //    $i = 0;
      //    while($row = $results->fetch_assoc()){
      //       $projectidList[$i] = $row["project_id"];
      //       $i++;
      //    }
      // } else {
      //    echo "No results\n<br>";
      // }


      // echo "<table border='1'>";
      // printArray($fieldNames, true);
      // // printArray($projectidList);
      // printQueryResults($installationDetailList);
      // echo "<table>";
      return $installationDetailList;
   }

   function getInstallations($conn, $results){
      getTableContents($conn, $results, "installation_details", "id", "installation_details_id");
      /*
      if($results->num_rows > 0){
         $fp = fopen("installation_out.csv", 'w');
         foreach($results as $installation_details_id){
            // echo $user['user_id'] . $newLine;
            $query = "SELECT * From installation_details WHERE id= '".$installation_details_id['installation_details_id']."'";
            // echo $query . $newLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getInvocations($conn, $results){
      getTableContents($conn, $results, "invocations", "session_id", "id");
      /*
      //This event has a package_id field, which indicates which package window the method was invoked from
      if($results->num_rows > 0){
         $fp = fopen("invocation_out.csv", 'w');
         foreach($results as $session){
            // echo $user['user_id'] . $newLine;
            $query = "SELECT * From invocations WHERE session_id= '".$session['id']."'";
            // echo $query . $newLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getInvocationTypes(){
      $query = "SELECT count(result), result FROM invocations group by result order by count(result) desc";
   }

   function getInvocationStackEntries($conn, $results){
      if($results->num_rows > 0){
         $fp = fopen("stack_entries_invocation_out.csv", 'w');
         foreach($results as $event){
            // echo $user['user_id'] . $newLine;
            $query = "SELECT * From stack_entries WHERE sub_event_type= 'Invocation' and sub_event_id= '".$event['event_id']."'";
            // echo $query . $newLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
   }

   function getMasterEvents($conn, $userIds){
      getTableContents($conn, $results, "master_events", "user_id", "user_id");
      /*
      if($userIds->num_rows > 0){
         $fp = fopen("master_event_out.csv", 'w');
         foreach($userIds as $user){
            echo "Sending query for " . $user['user_id'] . "<br>\n";
            $query = "SELECT * From master_events WHERE user_id= '".$user['user_id']."'";
            // echo $query . $newLine;
            $results = getResult($conn, $query);

            echo "Writing result for " . $user['user_id'] . "<br>\n";
            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);

         //Need to verify if work
         // deleteEventFromDate($conn, "2016-01-01");
      */

      ////////////////OLD CODE//////////////
/*      $masterEvents = array();
      $i = 0;

      foreach($userIds as $id){
         //echo "Current User ID: " .$id. $newLine;
         $sql = "SELECT * From master_events where user_id ='".$id."' order by created_at desc limit 10";
         $results = $connection->query($sql);
         
         //get field names
         $fieldNames = getFieldNames($results);
         // if($i == 0){
         //    printArray($fieldNames);
         // }

         if($results->num_rows > 0){
            // $masterEvents[$i] = $results;
            $masterEvents[$id] = $results;
            // printQueryResults($results);
            // echo "From results<br>";
            // while($row = $results->fetch_assoc()){
            //    foreach($row as $field){
            //       echo $field." ";
            //    }
            //    echo $newLine;
            // }
         } else {
            echo "No result\n<br>"; 
         }
         $i++; */ 
      }

      // foreach ($masterEvents as $j) {
      //    echo "Index:<br>";
      //    printQueryResults($j);
      //    echo $newLine;
      // }

      // return $masterEvents;
   }

   function getPackages($conn, $results){
      getTableContents($conn, $results, "packages", "project_id", "project_id");
      /*
      if($results->num_rows > 0){
         $fp = fopen("packages_out.csv", 'w');
         foreach($results as $project){
            // echo $user['user_id'] . $newLine;
            $query = "SELECT * From packages WHERE project_id= '".$project['project_id']."'";
            // echo $query . $newLine;
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
      getTableContents($conn, $results, "projects", "user_id", "user_id");
      /*
      if($results->num_rows > 0){
         $fp = fopen("projects_out.csv", 'w');
         foreach($results as $user){
            // echo $user['user_id'] . $newLine;
            $query = "SELECT * From projects WHERE user_id= '".$user['user_id']."'";
            // echo $query . $newLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getResultsFromIds($connection, $idList, $tableName, $fieldToSearch){
      $resultList = array();
      $i = 0;

      foreach($idList as $id){
         // echo "Current User ID: " .$id. $newLine;
         $sql = "SELECT * From ". $tableName ." WHERE " . $fieldToSearch ."='".$id[$fieldToSearch]."'";
         // echo $sql . $newLine;
         $results = $connection->query($sql);

         //get field names
         $fieldNames = getFieldNames($results);
         if($results->num_rows > 0){
            // $resultList[$fieldNames] = $results;
            echo "<tr><td>". $fieldToSearch . ": " . $id[$fieldToSearch] . "</td></tr>";
            printArray($fieldNames, true);
            printQueryResults($results);

         // while($row = $results->fetch_assoc()){
         //    $sourceFileid[$i] = $row['session_id'];
         //    // foreach($row as $field){
         //    //    $sourceFileid[$i] = $row['id'];
         //    // }
         //    $i++;
         // }

         } 
         $i++;
      }

      return $resultList;
   }

   function getSessionid($conn){
      $query = "SELECT session_id From master_events where user_id =1110282 order by created_at desc";

      $sessionidList = getResult($conn, $query);   //get master events using list of user_id
      $fieldNames = getFieldNames($sessionidList);

      return $sessionidList;
   }

   function getSessions($conn, $results){
      getTableContents($conn, $results, "sessions", "id", "id");
      /*
      if($results->num_rows > 0){
         $fp = fopen("sessions_out.csv", 'w');
         foreach($results as $session){
            // echo $user['user_id'] . $newLine;
            $query = "SELECT * From sessions WHERE id= '".$session['id']."'";
            // echo $query . $newLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getSourceFileid($conn){
      //////////////////////////////////
      //establish connection and logging onto Whitebox MySql Server and selecting blackbox_production database
      $conn = connectToBlackBox();
      $query = "SELECT distinct project_id From master_events where user_id =1110282 order by created_at desc";

      //Get all project id from user id
      $projectidList = getResult($conn, $query);

      $sourceFileid = array();
      $i = 0;

      foreach($projectidList as $id){
         if($id["project_id"] != null){
            // echo "<tr><td>Project ID: ". $id["project_id"] . "</td></tr>";
            $sql = "SELECT id as 'Source File ID', name From source_files where project_id =" .$id["project_id"];
            // $sql = "SELECT * From source_files where project_id =" .$id["project_id"];
            $results = $conn->query($sql);
            $fieldNames = getFieldNames($results);
            // echo "<table border='1'>";
            // printArray($fieldNames, true);
            // printQueryResults($results);
            // echo "<table><br>";

            while($row = $results->fetch_assoc()){
               $sourceFileid[$i] = $row['Source File ID'];
               // $sourceFileid[$i] = $row['id'];
               // $soureceFileid[$i] = $row;

               // foreach($row as $field){
               //    $sourceFileid[$i] = $row['id'];
               // }
               $i++;
            }
         }
      }
      return $sourceFileid;
   }

   function getSourceFiles($conn, $results){
      getTableContents($conn, $results, "source_files", "project_id", "project_id");
      /*
      if($results->num_rows > 0){
         $fp = fopen("source_file_out.csv", 'w');
         foreach($results as $project){
            // echo $user['user_id'] . $newLine;
            $query = "SELECT * From source_files WHERE project_id= '".$project['project_id']."'";
            // echo $query . $newLine;
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
      getTableContents($conn, $results, "source_hashes", "id", "package_id");
      /*
      if($results->num_rows > 0){
         $fp = fopen("source_hashes_out.csv", 'w');
         foreach($results as $project){
            // echo $user['user_id'] . $newLine;
            $query = "SELECT * From source_hashes WHERE id= '".$project['package_id']."'";
            // echo $query . $newLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function getSourceHistories($conn, $results){
      getTableContents($conn, $results, "source_histories", "source_file_id", "id");
      /*
      if($results->num_rows > 0){
         $fp = fopen("source_histories_out.csv", 'w');
         foreach($results as $sourceId){
            // echo $user['user_id'] . $newLine;
            // $query = "SELECT id, master_event_id, source_file_id, source_history_type, old_source_file_id From source_histories WHERE source_file_id= '".$sourceId['id']."'";
            $query = "SELECT * From source_histories WHERE source_file_id= '".$sourceId['id']."'";
            // echo $query . $newLine;
            $results = getResult($conn, $query);

            // printQueryResults($results); echo "<br><br>";

            foreach ($results as $val) {
               // fputcsv($fp, $val);       
               print_r($val);
            }
         }
         // fclose($fp);
      }
      */
   }

   function getTests($conn, $results){
      getTableContents($conn, $results, "tests", "session_id", "id");
      /*
      if($results->num_rows > 0){
         $fp = fopen("tests_out.csv", 'w');
         foreach($results as $event){
            // echo $event['id'] . $newLine;
            $query = "SELECT * From tests WHERE session_id = '".$event['id']."'";
            // echo $query . $newLine;
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
      getTableContents($conn, $results, "test_results", "session_id", "id");
      /*
      if($results->num_rows > 0){
         $fp = fopen("test_results_out.csv", 'w');
         foreach($results as $event){
            // echo $event['id'] . $newLine;
            $query = "SELECT * From test_results WHERE session_id = '".$event['id']."'";
            // echo $query . $newLine;
            $results = getResult($conn, $query);
            
            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }
/*
   function getUserid($connection, $participantList){
      $userIds = array();

      $i = 0;
      foreach($participantList as $pL){
         // echo "Current participant: " .$pL. $newLine;
         $sql = "SELECT s.user_id FROM (SELECT @experiment:='uwbmcss595') UNUSED, sessions_for_experiment s where s.participant_identifier ='".$pL."' group by s.user_id";
         $results = $connection->query($sql);

         if($results->num_rows > 0){
            $fieldName = mysqli_fetch_field($results)->name;
            while($row = $results->fetch_assoc()){
               // echo $row[$fieldName] . $newLine;
               foreach($row as $field){
                  // echo $fieldName . ": " . $field . $newLine;
                  $userIds[$i] = $field;
               }
               // echo $newLine;
            }
         } else {
            echo "No result\n<br>"; 
         }
         $i++;
      }

      return $userIds;
   }
*/
   function downloadUsers($conn, $userIds){
      getTableContents($conn, $results, "users", "id", "user_id");
      /*
      if($userIds->num_rows > 0){
         $fp = fopen("users_out.csv", 'w');
         foreach($userIds as $user){
            // echo $user['user_id'] . $newLine;
            $query = "SELECT * From users WHERE id= '".$user['user_id']."'";
            // echo $query . $newLine;
            $results = getResult($conn, $query);

            foreach ($results as $val) {
               fputcsv($fp, $val);       
            }
         }
         fclose($fp);
      }
      */
   }

   function testArrary($results){
      $fieldNames = array();

      if($results->num_rows > 0){
         $i = 0;
         while($fieldinfo = mysqli_fetch_field($results)){
            //echo $fieldinfo->name . " ";
            $fieldNames[$i] = $fieldinfo->name;
            $i++;
         }
         //echo $newLine;
      }
      else {
         echo "No results\n<br>";
      }

      return $fieldNames;
   }

   function magic($connection, $userIds){

      $interestingStuff = array();
      $i = 0;

      foreach($userIds as $id){
         //echo "Current User ID: " .$id. $newLine;
         $sql = "SELECT * From sessions where user_id ='".$id."' order by created_at desc";
         $results = $connection->query($sql);
         
         if($results->num_rows > 0){
            $interestingStuff[$i] = $results;
            
            // echo "From results<br>";
            // while($row = $results->fetch_assoc()){
            //    foreach($row as $field){
            //       echo $field." ";
            //    }
            //    echo $newLine;
            // }
         } else {
            echo "No result\n<br>"; 
         }
         $i++;
      }

      foreach ($interestingStuff as $j) {
         echo "Index:\n<br>";
         printQueryResults($j);
         echo "\n<br>";
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
         echo "something went wrong...\n<br>";
      }
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
?>