<?php
   //Note to self when recreating output and checkpoint folders
   //
   //chmod 775 [directory name]
   //Change contents in folder to 
   //chmod 664 [directory name]/*
   ini_set('memory_limit', '512M');

   include 'CoreFunctions.php';
   date_default_timezone_set("America/Los_Angeles");
   printLog("Download and updating.....Start");

   // //Read status from downloadStatus file
   $useridFile = "useridFile";
   $sessionidFile = "sessionidFile";
   $projectidFile = "projectidFile";
   $sourceFileidFile = "sourceFileidFile";

   $checkPoint = readCheckpoint();

   if(array_key_exists("started", $checkPoint) && $checkPoint["started"] == 1){
      printLog("Resuming from Checkpoint");
      // $useridList = restoreFromFile($useridFile);
      // $sessionidList = restoreFromFile($sessionidFile);
      // $projectidList = restoreFromFile($projectidFile);
      // $sourceFileIdList = restoreFromFile($sourceFileidFile);
   }

   // //Get start and end date for the data to download
   $dateRange = getStartEndDate();
   $startDate = $dateRange[0];
   $endDate = $dateRange[1];
   
   //establish connection and logging onto Whitebox MySql Server and selecting blackbox_production database
   $conn = connectToBlackBox();

   $key = "init";
   if(!$checkPoint[$key]){
      //Get user_id using experiment_identifier (e.g.'uwbgtcs')
      //The following query returns all UNIQUE user_id using the experiment_identifier = uwbgtcs
      $query = "SELECT distinct s.user_id FROM (SELECT @experiment:='uwbgtcs') unused, sessions_for_experiment s";
      
      //Store returned mysqli results object into $useridList
      $useridList = getResultArray($conn, $query, "user_id");
      saveToFile($useridFile, $useridList);

      //Get session_id using experiment_identifier (e.g.'uwbgtcs')
      //This and the above query are the MOST important queries, as these are THE only way to tie the data to our research just like the user_id query
      // //This query only returns session_id that are retrievable by experiment identifier
      // $query = "SELECT s.id FROM (SELECT @experiment:='uwbgtcs') unused, sessions_for_experiment s";

      // //This query returns all sessions USING user_id(s) found with experiment identifier
      $query = "SELECT id from sessions where user_id IN (" . implode(',', $useridList) . ") order by user_id";

      //Store returned mysqli results object into $sessionidList
      $sessionidList = getResultArray($conn, $query, "id");
      saveToFile($sessionidFile, $sessionidList);
   
      writeCheckpoint($checkPoint, $key);
   } else {
      $useridList = restoreFromFile($useridFile);
      $sessionidList = restoreFromFile($sessionidFile);
   }

   $key = "inspectors";
   if(!$checkPoint[$key]){
      //Inspector 
      printLog("Downloading inspectors to local");
      $sessionidList = restoreFromFile($sessionidFile);
      $fileCreated = getIspectors($conn, $sessionidList);
      updateLocal($fileCreated);
      unset($sessionidList);
   
      writeCheckpoint($checkPoint, $key);
   }

   $key = "invocations";
   if(!$checkPoint[$key]){
      // //Use sessionidList to populate local invocations table
      printLog("Start populating local invocations with session_id");
      $sessionidList = restoreFromFile($sessionidFile);
      $fileCreated = getInvocations($conn, $sessionidList);
      updateLocal($fileCreated);
      unset($sessionidList);
   
      writeCheckpoint($checkPoint, $key);
   }

   $key = "sessions";
   if(!$checkPoint[$key]){
      // //Use sessionidList to populate local sessions
      printLog("Start populating local sessions with session_id");
      $sessionidList = restoreFromFile($sessionidFile);
      $fileCreated = getSessions($conn, $sessionidList);
      updateLocal($fileCreated);
      unset($sessionidList);
      
      writeCheckpoint($checkPoint, $key);
   }

   $key = "master_events";
   if(!$checkPoint[$key]){
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
   
      $query = "SELECT distinct project_id From master_events";
      $projectidList = getResultArray($connLocal, $query, "project_id");
      saveToFile($projectidFile, $projectidList);
      
      disconnectServer($connLocal);

      writeCheckpoint($checkPoint, $key);
   } else {
      $projectidList = restoreFromFile($projectidFile);
   }

   $key = "users";
   if(!$checkPoint[$key]){
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
   if(!$checkPoint[$key]){
      // //Use useridList to populate local projects table
      printLog("Start populating local projects");
      $useridList = restoreFromFile($useridFile);
      $fileCreated = getProjects($conn, $useridList);
      updateLocal($fileCreated);
      unset($useridList);

      writeCheckpoint($checkPoint, $key);
   } 

   $key = "packages";
   if(!$checkPoint[$key]){
      // //Use local projectidList from projects table to populate local packages table
      printLog("Start populating local packages");
      $projectidList = restoreFromFile($projectidFile);
      $fileCreated = getPackages($conn, $projectidList);
      updateLocal($fileCreated);
      unset($projectidList);
      
      writeCheckpoint($checkPoint, $key);
   }

   $key = "source_files";
   if(!$checkPoint[$key]){
      // //Populate source_files table using projectiList from local master_events table
      printLog("Start populating local source_files");
      $projectidList = restoreFromFile($projectidFile);
      $fileCreated = getSourceFiles($conn, $projectidList);
      updateLocal($fileCreated);
      unset($projectidList);
      
      writeCheckpoint($checkPoint, $key);
   }

   $key = "sourceIds";
   if(!$checkPoint[$key]){
      // //Get all SourceFileID from local source_files table if source_file table has been retrieved
      printLog("Get all SourceFileID from local source_files");
      $connLocal = connectToLocal("capstoneLocal");
      
      $query = "SELECT id from source_files";
      $sourceFileIdList = getResultArray($connLocal, $query, "id");
      
      saveToFile($sourceFileidFile, $sourceFileIdList);
      disconnectServer($connLocal);
      
      writeCheckpoint($checkPoint, $key);
   } else {
      $sourceFileIdList = restoreFromFile($sourceFileidFile);
   }

   $key = "breakpoints";
   if(!$checkPoint[$key]){
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
   if(!$checkPoint[$key]){
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
   if(!$checkPoint[$key]){  
      //fixtures table
      //Populate local fixtures table with sourceFileIdList
      printLog("Downloading fixtures to local");
      $sourceFileIdList = restoreFromFile($sourceFileidFile);
      $fileCreated = getFixtures($conn, $sourceFileIdList);
      updateLocal($fileCreated);
      unset($sourceFileIdList);
      
      writeCheckpoint($checkPoint, $key);
   }

   //////////////////////////////////////////////////////////////////////
   // //source_histories table
   // //Populate local source_histories table with sourceFileIdList 
   // //TODO: needs rework on handling special characters
   // $conn = connectToBlackBox();
   // getSourceHistories($conn, $sourceFileIdList);
   // disconnectServer($conn);
   // //update source_histories table of local server
   // updateLocal("'source_histories_out.csv'", "source_histories");
   //////////////////////////////////////////////////////////////////////

   $key = "compile_events_and_outputs";
   if(!$checkPoint[$key]){
      // //compile_outputs table
      // //Populate local compile_outputs table with compile_event_id and source_file_id from local compile_inputs
      printLog("Getting compile_event_id(s)");
      $connLocal = connectToLocal("capstoneLocal");
      $query = "SELECT compile_event_id, source_file_id from compile_inputs";
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
   if(!$checkPoint[$key]){
      //source_hashes table
      //Populate local source_hashes table with package_id from local server
      printLog("Getting package_id(s)");
      $connLocal = connectToLocal("capstoneLocal");
      $query = "SELECT distinct package_id from master_events";
      $packageList = getResultArray($connLocal, $query, "package_id");
      disconnectServer($connLocal);

      printLog("Downloading source_hashes to local");
      $fileCreated = getSourceHashes($conn, $packageList);
      updateLocal($fileCreated);

      unset($packageList);
      
      writeCheckpoint($checkPoint, $key);
   }

   $key = "stack_entries";
   if(!$checkPoint[$key]){
      //stack_entries table
      //Get all event_id from local master_events where event_type is Invocation or DebuggerEvent
      //The retrieved event_id will be used to populate stack_entries table
      $connLocal = connectToLocal("capstoneLocal");
      
      printLog("Getting all event_id(s) where event_type is Invoation");
      //select all event_id with type invocation
      $query = "SELECT event_id from master_events where event_type = 'Invocation'";
      $invocationEventList = getResultArray($connLocal, $query, "event_id");

      printLog("Getting all event_id(s) where event_type is DebuggerEvent");
      //select all event_id with type debuggerEvent
      $query = "SELECT distinct event_id from master_events where event_type = 'DebuggerEvent'";
      $debuggerEventList = getResultArray($connLocal, $query, "event_id");
      disconnectServer($connLocal);

      printLog("Downloading stack_entries with invocation as event to local");
      // retrieve all stack_entries of type Invocation and the event_id
      getInvocationStackEntries($conn, $invocationEventList);
      updateLocal("stack_entries_out.csv");

      printLog("Downloading stack_entries with debugger as event to local");
      //retrieve all stack_entries of type DebuggerEvent and the event_id
      getDebuggerStackEntries($conn, $debuggerEventList);
      updateLocal("stack_entries_out.csv");

      //Debugger Events
      printLog("Downloading degbugger_events to local");
      $fileCreated = getDebuggerEvents($conn, $debuggerEventList);
      updateLocal($fileCreated);

      unset($invocationEventList);
      unset($debuggerEventList);
      
      writeCheckpoint($checkPoint, $key);
   }

   $key = "bench_objects";
   if(!$checkPoint[$key]){
      //bench_objects table
      //Populate bench_objects using package_id
      printLog("Getting all package_id(s) where event_type is BenchObject");
      $connLocal = connectToLocal("capstoneLocal");
      $query = "SELECT distinct package_id from master_events where event_type = 'BenchObject'";
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
   if(!$checkPoint[$key]){
      //Bench object fixture
      printLog("Getting all id(s) from bench_objects");
      $connLocal = connectToLocal("capstoneLocal");
      $query = "SELECT distinct id from bench_objects";
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

   $key = "codepad_events";
   if(!$checkPoint[$key]){
      //Codepad Events
      //select all event_id with type CodepadEvent
      printLog("Getting event_id(s) from master_events");
      $connLocal = connectToLocal("capstoneLocal");
      $query = "SELECT distinct event_id from master_events where event_type = 'CodepadEvent'";
      $codePadEventList = getResultArray($connLocal, $query, "event_id");

      disconnectServer($connLocal);

      printLog("Downloading codepad_events to local");
      // $conn = connectToBlackBox();
      $fileCreated = getCodePadEvents($conn, $codePadEventList);
      // disconnectServer($conn);
      updateLocal($fileCreated);

      unset($codePadEventList);
      
      writeCheckpoint($checkPoint, $key);
   }

   // $key = "extensions";
   // if(!$checkPoint[$key]){
   //    //Extensions
   //    //select all master_event_id from local master_events
   //    //At this point this is the table that takes the longest time to download
   //    //Have encountered exceeding PHP preset memory while retrieving data
   //    //Therefore, we could leave this download out since it only contains extensions used in a project
   //    //Which in our case there will be no extensions
      
   //    printLog("Getting id(s) from master_events");
   //    $connLocal = connectToLocal("capstoneLocal");
   //    $query = "SELECT distinct id from master_events";
   //    $masterEventIdList = getResultArray($connLocal, $query, "id");

   //    disconnectServer($connLocal);

   //    printLog("Downloading extensions to local");
   //    // $conn = connectToBlackBox();
   //    $fileCreated = getExtensions($conn, $masterEventIdList);
   //    // disconnectServer($conn);
   //    updateLocal($fileCreated);

   //    unset($masterEventIdList);
      
   //    writeCheckpoint($checkPoint, $key);
   // }

   // $key = "tests";
   // if(!$checkPoint[$key]){
   //    //Test
   //    printLog("Getting session_id(s) from master_events");
   //    $connLocal = connectToLocal("capstoneLocal");
   //    $query = "SELECT distinct session_id from master_events where event_type = 'Test'";
   //    $testidList = getResultArray($connLocal, $query, "session_id");

   //    disconnectServer($connLocal);

   //    printLog("Downloading tests to local");
   //    // $conn = connectToBlackBox();
   //    $fileCreated = getTests($conn, $testidList);
   //    // disconnectServer($conn);
   //    updateLocal($fileCreated);

   //    unset($testidList);

   //    writeCheckpoint($checkPoint, $key);
   // }

   // $key = "test_results";
   // if(!$checkPoint[$key]){
   //    //Test Results
   //    printLog("Getting session_id(s) from master_events");
   //    $conn = connectToLocal("capstoneLocal");
   //    $query = "SELECT distinct session_id from master_events where event_type = 'TestResult'";
   //    $testResultList = getResultArray($conn, $query, "session_id");
   //    disconnectServer($conn);

   //    printLog("Downloading test_results to local");
   //    // $conn = connectToBlackBox();
   //    $fileCreated = getTestResults($conn, $testResultList);
   //    // disconnectServer($conn);
   //    updateLocal($fileCreated);

   //    unset($testResultList);
      
   //    writeCheckpoint($checkPoint, $key);
   // }

   // //////////////////////////////////////////////////////////////////////
   // //Use updated local master_events to retrieve all client_address_id related to experiment
   // //Contains ip address of users
   // //Client_address is a hidden table, and therefore could not be accessed directly.
   // //This table doesn't have interesting data related to the research
   // echo "Start retrieving clident_address: " . date("Y-m-d h:i:a") . "<br>\n";
   // $conn = connectToLocal("capstoneLocal");
   // $query = "SELECT distinct client_address_id From master_events";
   // $clientIdList = getResult($conn, $query);
   // disconnectServer($conn);
   // $conn = connectToBlackBox();
   // $addressIdList = getClientAddressId($conn, $clientIdList);
   // disconnectServer($conn);
   // updateLocal("'client_address_out.csv'", "client_addresses");
   // echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   // //////////////////////////////////////////////////////////////////////

   // //////////////////////////////////////////////////////////////////////
   // //Use updated local Sessions to populate local installation_details table
   // //Contains information such as OS type and Java version
   // //No interesting data related to the research
   // echo "Start populating local installation_details: " . date("Y-m-d h:i:a") . "<br>\n";
   // $conn = connectToLocal("capstoneLocal");
   // $query = "SELECT distinct installation_details_id From sessions";
   // $installationidList = getResult($conn, $query);
   // disconnectServer($conn);

   // //connect to BlackBox to retrieve installation_detail data
   // $conn = connectToBlackBox();
   // getInstallations($conn, $installationidList);
   // disconnectServer($conn);
   // // update invocations table from local server with csv
   // updateLocal("'installation_out.csv'", "installation_details");
   // echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   // //////////////////////////////////////////////////////////////////////

   // disconnectServer($conn);
   
   printLog("Download and updating EVERYTHING.....Done");
?>