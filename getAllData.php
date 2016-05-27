<?php
   include 'CoreFunctions.php';
   // //defines the root directory
   $root = './';
   // //Use to test smaller download by limiting number of result for queries
   $limitResult = '';
   date_default_timezone_set("America/Los_Angeles");
   printLog("Download and updating.....Start");

   $checkPoint = readCheckpoint();

   if(array_key_exists("started", $checkPoint) && $checkPoint["started"] == 1){
      printLog("Resuming from Checkpoint");
      
   }

   // //Get start and end date for the data to download
   $dateRange = getStartEndDate();
   $startDate = $dateRange[0];
   $endDate = $dateRange[1];
   
   //establish connection and logging onto Whitebox MySql Server and selecting blackbox_production database
   $conn = connectToBlackBox();

   $key = "init";
   if(!array_key_exists($key, $checkPoint) || !$checkPoint[$key]){
      //Get user_id using experiment_identifier (e.g.'uwbgtcs')
      //The following query returns all UNIQUE user_id and participant_id using the experiment_identifier = uwbgtcs
      $query = "SELECT distinct s.user_id, s.participant_id, s.participant_identifier FROM (SELECT @experiment:='uwbgtcs') unused, sessions_for_experiment s where created_at between '" .$startDate. "' and '" .$endDate. "'" . $limitResult;
      
      //Store returned mysqli results object into $useridList
      $results = getResult($conn, $query);
      $useridList = array();

      if($results != null){
         while($row = $results->fetch_assoc()){
            $pIdentifier = strtolower($row['participant_identifier']);
            $pIdentifier = str_replace(' ', '', $pIdentifier);
            $pIdentifier = str_replace('skl', '', $pIdentifier);
            array_push($useridList, array('user_id' => $row['user_id'], 'participant_id' => $row['participant_id'], 'participant_identifier' => $pIdentifier));
         }
      } 

      mysqli_free_result($results);
      saveToFile($useridFile, $useridList);
   
      writeCheckpoint($checkPoint, $key);
   } else {
      $useridList = restoreFromFile($useridFile);
   }

   $key = "master_events";
   if(!array_key_exists($key, $checkPoint) || !$checkPoint[$key]){
      // //master_events table
      // //Use the above useridList to retrieve all related participants master events from the Whitebox server
      // //Populating the master_events table has to be the first table to downloaded because
      // //all other table will be related from it.
      
      printLog("Start populating local master_events with user_id");
      $fileCreated = getMasterEvents($conn, $useridList, $startDate, $endDate);
      updateLocal($fileCreated);

      // //Use updated local master_events to retrieve all project_id related to experiment if master_events table has been downloaded
      printLog("Start retrieving all project_id related to experiment");
      $connLocal = connectToLocal("capstoneLocal");
   
      $query = "SELECT distinct project_id from master_events" . $limitResult;
      $projectidList = getResultArray($connLocal, $query, "project_id");
      printlog("Acquired Project ID List, saving to file...");
      saveToFile($projectidFile, $projectidList);
      printlog("Saving to file ".$projectidFile." complete...");
      unset($projectidList);

      // //This query returns all sessions USING user_id(s) found with experiment identifier
      $query = "SELECT distinct session_id from master_events" . $limitResult;
      $sessionidList = getResultArray($connLocal, $query, "session_id");
      printlog("Acquired Session ID List, saving to file...");
      saveToFile($sessionidFile, $sessionidList);
      printlog("Saving to file ".$sessionidFile." complete...");

      unset($sessionidList);

      disconnectServer($connLocal);

      writeCheckpoint($checkPoint, $key);
      
   } else {
      $projectidList = restoreFromFile($projectidFile);
      $sessionidList = restoreFromFile($sessionidFile);
   }

   $key = "inspectors";
   if(!array_key_exists($key, $checkPoint) || !$checkPoint[$key]){
      //Inspector 
      printLog("Downloading inspectors to local");
      $sessionidList = restoreFromFile($sessionidFile);
      $fileCreated = getIspectors($conn, $sessionidList);
      updateLocal($fileCreated);
      unset($sessionidList);
   
      writeCheckpoint($checkPoint, $key);
      
   }

   $key = "invocations";
   if(!array_key_exists($key, $checkPoint) || !$checkPoint[$key]){
      // //Use sessionidList to populate local invocations table
      printLog("Start populating local invocations with session_id");
      $sessionidList = restoreFromFile($sessionidFile);
      $fileCreated = getInvocations($conn, $sessionidList);
      updateLocal($fileCreated);
      unset($sessionidList);
   
      writeCheckpoint($checkPoint, $key);
      
   }

   $key = "sessions";
   if(!array_key_exists($key, $checkPoint) || !$checkPoint[$key]){
      // //Use sessionidList to populate local sessions
      printLog("Start populating local sessions with session_id");
      $sessionidList = restoreFromFile($sessionidFile);
      $fileCreated = getSessions($conn, $sessionidList);
      updateLocal($fileCreated);
      unset($sessionidList);
      
      writeCheckpoint($checkPoint, $key);
      
   }

   $key = "users";
   if(!array_key_exists($key, $checkPoint) || !$checkPoint[$key]){
      // //users table
      // //The table can be populated simply by query for every user_id from useridList retrieved earlier
      printLog("Start populating local users");
      $useridList = restoreFromFile($useridFile);
      $fileCreated = getUsers($conn, $useridList);
      updateLocal($fileCreated);
      unset($useridList);

      writeCheckpoint($checkPoint, $key);
      
   }

   $key = "projects";
   if(!array_key_exists($key, $checkPoint) || !$checkPoint[$key]){
      // //Use useridList to populate local projects table
      printLog("Start populating local projects");
      $useridList = restoreFromFile($useridFile);
      $fileCreated = getProjects($conn, $useridList);
      updateLocal($fileCreated);
      unset($useridList);

      writeCheckpoint($checkPoint, $key);
      
   } 

   $key = "packages";
   if(!array_key_exists($key, $checkPoint) || !$checkPoint[$key]){
      // //Use local projectidList from projects table to populate local packages table
      printLog("Start populating local packages");
      $projectidList = restoreFromFile($projectidFile);
      $fileCreated = getPackages($conn, $projectidList);
      updateLocal($fileCreated);
      unset($projectidList);
      
      writeCheckpoint($checkPoint, $key);
      
   }

   $key = "source_files";
   if(!array_key_exists($key, $checkPoint) || !$checkPoint[$key]){
      // //Populate source_files table using projectiList from local master_events table
      printLog("Start populating local source_files");
      $projectidList = restoreFromFile($projectidFile);
      $fileCreated = getSourceFiles($conn, $projectidList);
      updateLocal($fileCreated);
      unset($projectidList);
      
      writeCheckpoint($checkPoint, $key);
      
   }

   $key = "sourceIds";
   if(!array_key_exists($key, $checkPoint) || !$checkPoint[$key]){
      // //Get all SourceFileID from local source_files table if source_file table has been retrieved
      printLog("Get all SourceFileID from local source_files");
      $connLocal = connectToLocal("capstoneLocal");
      
      $query = "SELECT id from source_files" . $limitResult;
      $sourceFileIdList = getResultArray($connLocal, $query, "id");
      
      saveToFile($sourceFileidFile, $sourceFileIdList);
      disconnectServer($connLocal);
      
      writeCheckpoint($checkPoint, $key);
      
   } else {
      $sourceFileIdList = restoreFromFile($sourceFileidFile);
   }

   $key = "breakpoints";
   if(!array_key_exists($key, $checkPoint) || !$checkPoint[$key]){
      // //breakpoints table
      // //Populate local breakpoints using sourceFileIdList
      printLog("Start populating local breakpoints");
      $sourceFileIdList = restoreFromFile($sourceFileidFile);
      $fileCreated = getBreakpoints($conn, $sourceFileIdList);
      updateLocal($fileCreated);
      unset($sourceFileIdList);
      
      writeCheckpoint($checkPoint, $key);
      
   }

   $key = "compile_inputs";
   if(!array_key_exists($key, $checkPoint) || !$checkPoint[$key]){
      // //compile_inputs table
      // //Populate local compile_inputs table with sourceFileIdList
      printLog("Start populating local compile_inputs");
      $sourceFileIdList = restoreFromFile($sourceFileidFile);
      $fileCreated = getCompileInputs($conn, $sourceFileIdList);
      updateLocal($fileCreated);
      unset($sourceFileIdList);
      
      writeCheckpoint($checkPoint, $key);
      
   }

   $key = "fixtures";
   if(!array_key_exists($key, $checkPoint) || !$checkPoint[$key]){
      //fixtures table
      //Populate local fixtures table with sourceFileIdList
      printLog("Downloading fixtures to local");
      $sourceFileIdList = restoreFromFile($sourceFileidFile);
      $fileCreated = getFixtures($conn, $sourceFileIdList);
      updateLocal($fileCreated);
      unset($sourceFileIdList);
      
      writeCheckpoint($checkPoint, $key);
      
   }

   $key = "compile_events_and_outputs";
   if(!array_key_exists($key, $checkPoint) || !$checkPoint[$key]){
      // //compile_outputs table
      // //Populate local compile_outputs table with compile_event_id and source_file_id from local compile_inputs
      printLog("Getting compile_event_id(s)");
      $connLocal = connectToLocal("capstoneLocal");
      $query = "SELECT compile_event_id, source_file_id from compile_inputs" . $limitResult;
      $compileEventIdList = getResult($connLocal, $query);
      disconnectServer($connLocal);

      // //compile_output table
      printLog("Downloading compile_output to local");
      $fileCreated = getCompileOutputs($conn, $compileEventIdList);
      updateLocal($fileCreated);
      // //compile_events table
      printLog("Downloading compile_events to local");
      $fileCreated = getCompileEvents($conn, $compileEventIdList);
      updateLocal($fileCreated);

      mysqli_free_result($compileEventIdList);
      
      writeCheckpoint($checkPoint, $key);
      
   }

   $key = "source_hashes";
   if(!array_key_exists($key, $checkPoint) || !$checkPoint[$key]){
      //source_hashes table
      //Populate local source_hashes table with package_id from local server
      printLog("Getting package_id(s)");
      $connLocal = connectToLocal("capstoneLocal");
      $query = "SELECT distinct package_id from master_events" . $limitResult;
      $packageList = getResultArray($connLocal, $query, "package_id");
      disconnectServer($connLocal);

      printLog("Downloading source_hashes to local");
      $fileCreated = getSourceHashes($conn, $packageList);
      updateLocal($fileCreated);

      unset($packageList);
      
      writeCheckpoint($checkPoint, $key);
      
   }

   $key = "stack_entries";
   if(!array_key_exists($key, $checkPoint) || !$checkPoint[$key]){
      //stack_entries table and debugger_events table
      //Get all event_id from local master_events where event_type is Invocation or DebuggerEvent
      //The retrieved event_id will be used to populate stack_entries table
      $connLocal = connectToLocal("capstoneLocal");
      
      printLog("Getting all event_id(s) where event_type is Invoation");
      //select all event_id with type invocation
      $query = "SELECT event_id from master_events where event_type = 'Invocation'" . $limitResult;
      $invocationEventList = getResultArray($connLocal, $query, "event_id");

      printLog("Getting all event_id(s) where event_type is DebuggerEvent");
      //select all event_id with type debuggerEvent
      $query = "SELECT distinct event_id from master_events where event_type = 'DebuggerEvent'" . $limitResult;
      $debuggerEventList = getResultArray($connLocal, $query, "event_id");
      
      disconnectServer($connLocal);

      //Debugger Events Table
      printLog("Downloading degbugger_events to local");
      $fileCreated = getDebuggerEvents($conn, $debuggerEventList);
      updateLocal($fileCreated);

      printLog("Downloading stack_entries with debugger as event to local");
      //retrieve all stack_entries of type DebuggerEvent and the event_id
      $fileCreated = getDebuggerStackEntries($conn, $debuggerEventList);
      updateLocal($fileCreated);

      printLog("Downloading stack_entries with invocation as event to local");
      // retrieve all stack_entries of type Invocation and the event_id
      $fileCreated = getInvocationStackEntries($conn, $invocationEventList);
      updateLocal($fileCreated);

      unset($invocationEventList);
      unset($debuggerEventList);
      
      writeCheckpoint($checkPoint, $key);
      
   }

   $key = "bench_objects";
   if(!array_key_exists($key, $checkPoint) || !$checkPoint[$key]){
      //bench_objects table
      //Populate bench_objects using package_id
      printLog("Getting all package_id(s) where event_type is BenchObject");
      $connLocal = connectToLocal("capstoneLocal");
      $query = "SELECT distinct package_id from master_events where event_type = 'BenchObject'" . $limitResult;
      $benchPackageList = getResultArray($connLocal, $query, "package_id");

      disconnectServer($connLocal);

      printLog("Downloading bench_objects to local");
      // $conn = connectToBlackBox();
      $fileCreated = getBenchObjects($conn, $benchPackageList);
      // disconnectServer($conn);
      updateLocal($fileCreated);

      unset($benchPackageList);
      
      writeCheckpoint($checkPoint, $key);
      
   }

   $key = "bench_objects_fixture";
   if(!array_key_exists($key, $checkPoint) || !$checkPoint[$key]){
      //Bench object fixture
      printLog("Getting all id(s) from bench_objects");
      $connLocal = connectToLocal("capstoneLocal");
      $query = "SELECT distinct id from bench_objects" . $limitResult;
      $benchObjectList = getResultArray($connLocal, $query, "id");

      disconnectServer($connLocal);

      printLog("Downloading bench_objects_fixture to local");
      // $conn = connectToBlackBox();
      $fileCreated = getBenchObjectsFixture($conn, $benchObjectList);
      // disconnectServer($conn);
      updateLocal($fileCreated);

      unset($benchObjectList);
      
      writeCheckpoint($checkPoint, $key);

   }

   // //Disconnect from Blackbox
   disconnectServer($conn);
   
   printLog("Download and updating.....Done");
?>