<?php
   include 'CoreFunctions.php';
   date_default_timezone_set("America/Los_Angeles"); 
   printLog("Download and updating.....Start");

   // //Read status from downloadStatus file
   $checkpointFile = "checkpoint";
   $useridFile = "useridFile";
   $sessionidFile = "sessionidFile";
   $projectidFile = "projectidFile";
   $sourceFileidFile = "sourceFileidFile";

   $currentStatus = readCheckpoint($checkpointFile);
   $checkPoint = 0;

   if($currentStatus > 0){
      printLog("Resuming from Checkpoint");
      $useridList = unserialize(readCheckpoint($useridFile));
      $sessionidList = unserialize(readCheckpoint($sessionidFile));
      $projectidList = unserialize(readCheckpoint($projectidFile));
      $sourceFileIdList = unserialize(readCheckpoint($sourceFileidFile));
   }

   // //Get start and end date for the data to download
   $dateRange = getStartEndDate();
   $startDate = $dateRange[0];
   $endDate = $dateRange[1];
   
   //establish connection and logging onto Whitebox MySql Server and selecting blackbox_production database
   $conn = connectToBlackBox();

   if($currentStatus < ++$checkPoint){
      //Get user_id using experiment_identifier (e.g.'uwbgtcs')
      //The following query returns all UNIQUE user_id using the experiment_identifier = uwbgtcs
      $colName = "user_id";
      $query = "SELECT distinct s." .$colName. " FROM (SELECT @experiment:='uwbgtcs') unused, sessions_for_experiment s";
      // $query = "SELECT distinct s.user_id FROM (SELECT @experiment:='uwbgtcs') unused, sessions_for_experiment s";
      
      //Store returned mysqli results object into $useridList
      $userids = getResult($conn, $query);
      $useridList = objToArray($userids, $colName);
      writeCheckpoint($useridFile, serialize($useridList));

      //Get session_id using experiment_identifier (e.g.'uwbgtcs')
      //This is the MOST important query, as this is THE only way to tie the data to our research just like the user_id query
      $colName = "id";
      $query = "SELECT s." .$colName. " FROM (SELECT @experiment:='uwbgtcs') unused, sessions_for_experiment s";
      // $query = "SELECT s.id FROM (SELECT @experiment:='uwbgtcs') unused, sessions_for_experiment s";
      
      //Store returned mysqli results object into $sessionidList
      $sessionids = getResult($conn, $query);
      $sessionidList = objToArray($sessionids, $colName);
      writeCheckpoint($sessionidFile, serialize($sessionidList));
   
      //disconnect from Blackbox's Whitebox MySql server
      // disconnectServer($conn);
   }

   if($currentStatus < ++$checkPoint){
      //Inspector 
      printLog("Downloading inspectors to local");
      // // $conn = connectToBlackBox();
      $fileCreated = getIspectors($conn, $sessionidList);
      // // disconnectServer($conn);
      updateLocal($fileCreated);
   
      writeCheckpoint($checkpointFile, $checkPoint);
   }

   if($currentStatus < ++$checkPoint){
      // //Use sessionidList to populate local invocations table
      printLog("Start populating local invocations with session_id");
      // // $conn = connectToBlackBox();
      $fileCreated = getInvocations($conn, $sessionidList);
      // // disconnectServer($conn);
      updateLocal($fileCreated);
   
      writeCheckpoint($checkpointFile, $checkPoint);
   }

   if($currentStatus < ++$checkPoint){
      // //Use sessionidList to populate local sessions
      printLog("Start populating local sessions with session_id");
      // // $conn = connectToBlackBox();
      $fileCreated = getSessions($conn, $sessionidList);
      // // disconnectServer($conn);
      updateLocal($fileCreated);
      
      writeCheckpoint($checkpointFile, $checkPoint);  
   }

   if($currentStatus < ++$checkPoint){
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
      $colName = "project_id";
      $query = "SELECT distinct ".$colName." From master_events";
      $projectid = getResult($connLocal, $query);
      $projectidList = objToArray($projectid, $colName);
      writeCheckpoint($projectidFile, serialize($projectidList));
      disconnectServer($connLocal);

      writeCheckpoint($checkpointFile, $checkPoint);
   }

   if($currentStatus < ++$checkPoint){
      // //users table
      // //The table can be populated simply by query for every user_id from useridList retrieved earlier

      printLog("Start populating local users");
      $fileCreated = getUsers($conn, $useridList);
      updateLocal($fileCreated);

      writeCheckpoint($checkpointFile, $checkPoint);
   }

   if($currentStatus < ++$checkPoint){
      // //Use useridList to populate local projects table
      printLog("Start populating local projects");
      // // $conn = connectToBlackBox();
      $fileCreated = getProjects($conn, $useridList);
      // // disconnectServer($conn);
      updateLocal($fileCreated);

      writeCheckpoint($checkpointFile, $checkPoint);
   }

   if($currentStatus < ++$checkPoint){
      // //Use local projectidList from projects table to populate local packages table
      printLog("Start populating local packages");
      // // $conn = connectToBlackBox();
      $fileCreated = getPackages($conn, $projectidList);
      // // disconnectServer($conn);
      updateLocal($fileCreated);
      
      writeCheckpoint($checkpointFile, $checkPoint);
   }

   if($currentStatus < ++$checkPoint){
      // //Populate source_files table using projectiList from local master_events table
      printLog("Start populating local source_files");
      // // $conn = connectToBlackBox();
      $fileCreated = getSourceFiles($conn, $projectidList);
      // // disconnectServer($conn);
      updateLocal($fileCreated);
      
      writeCheckpoint($checkpointFile, $checkPoint);
   }

   if($currentStatus < ++$checkPoint){
      // //Get all SourceFileID from local source_files table if source_file table has been retrieved
      printLog("Get all SourceFileID from local source_files");
      $connLocal = connectToLocal("capstoneLocal");
      $colName = "id";
      // $query = "SELECT id from source_files";
      $query = "SELECT ".$colName." from source_files";
      $sourceFileId = getResult($connLocal, $query);
      $sourceFileIdList = objToArray($sourceFileId, $colName);
      writeCheckpoint($sourceFileidFile, serialize($sourceFileIdList));
      disconnectServer($connLocal);
      
      writeCheckpoint($checkpointFile, $checkPoint);
   }

   if($currentStatus < ++$checkPoint){
      // //breakpoints table
      // //Populate local breakpoints using sourceFileIdList
      printLog("Start populating local breakpoints");
      // // $conn = connectToBlackBox();
      $fileCreated = getBreakpoints($conn, $sourceFileIdList);
      // // disconnectServer($conn);
      updateLocal($fileCreated);
      
      writeCheckpoint($checkpointFile, $checkPoint);
   }

   if($currentStatus < ++$checkPoint){
      // //compile_inputs table
      // //Populate local compile_inputs table with sourceFileIdList
      printLog("Start populating local compile_inputs");
      // // $conn = connectToBlackBox();
      $fileCreated = getCompileInputs($conn, $sourceFileIdList);
      // // disconnectServer($conn);
      updateLocal($fileCreated);
      
      writeCheckpoint($checkpointFile, $checkPoint);
   }

   if($currentStatus < ++$checkPoint){  
      //fixtures table
      //Populate local fixtures table with sourceFileIdList
      printLog("Downloading fixtures to local");
      // // $conn = connectToBlackBox();
      $fileCreated = getFixtures($conn, $sourceFileIdList);
      // // disconnectServer($conn);
      updateLocal($fileCreated);
      
      writeCheckpoint($checkpointFile, $checkPoint);
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

   if($currentStatus < ++$checkPoint){
      // //compile_outputs table
      // //Populate local compile_outputs table with compile_event_id and source_file_id from local compile_inputs
      printLog("Getting compile_event_id(s)");
      $connLocal = connectToLocal("capstoneLocal");
      $query = "SELECT compile_event_id, source_file_id from compile_inputs";
      $compileEventIdList = getResult($connLocal, $query);
      disconnectServer($connLocal);

      printLog("Downloading compile_output to local");
      // $conn = connectToBlackBox();
      $fileCreated = getCompileOutputs($conn, $compileEventIdList);
      // disconnectServer($conn);
      // // update compile_outputs table of local server
      updateLocal($fileCreated);

      // //compile_events table
      // //Populate local compile_events table with compile_event_id from local compile_inputs
      printLog("Downloading compile_events to local");
      // // $conn = connectToBlackBox();
      $fileCreated = getCompileEvents($conn, $compileEventIdList);
      // // disconnectServer($conn);
      updateLocal($fileCreated);
      
      writeCheckpoint($checkpointFile, $checkPoint);
   }

   if($currentStatus < ++$checkPoint){
      //source_hashes table
      //Populate local source_hashes table with package_id from local server
      printLog("Getting package_id(s)");
      $connLocal = connectToLocal("capstoneLocal");
      $query = "SELECT distinct package_id from master_events";
      $packageList = getResult($connLocal, $query);
      disconnectServer($connLocal);

      printLog("Downloading source_hashes to local");
      // $conn = connectToBlackBox();
      $fileCreated = getSourceHashes($conn, $packageList);
      // disconnectServer($conn);
      updateLocal($fileCreated);
      
      writeCheckpoint($checkpointFile, $checkPoint);
   }

   if($currentStatus < ++$checkPoint){
      //stack_entries table
      //Get all event_id from local master_events where event_type is Invocation or DebuggerEvent
      //The retrieved event_id will be used to populate stack_entries table
      $connLocal = connectToLocal("capstoneLocal");
      
      printLog("Getting all event_id(s) where event_type is Invoation");
      //select all event_id with type invocation
      $query = "SELECT event_id from master_events where event_type = 'Invocation'";
      $invocationEventList = getResult($connLocal, $query);
      // disconnectServer($connLocal);

      printLog("Getting all event_id(s) where event_type is DebuggerEvent");
      //select all event_id with type debuggerEvent
      // $connLocal = connectToLocal("capstoneLocal");
      $query = "SELECT distinct event_id from master_events where event_type = 'DebuggerEvent'";
      $debuggerEventList = getResult($connLocal, $query);
      disconnectServer($connLocal);

      printLog("Downloading stack_entries with invocation as event to local");
      // retrieve all stack_entries of type Invocation and the event_id
      // $conn = connectToBlackBox();
      getInvocationStackEntries($conn, $invocationEventList);
      // disconnectServer($conn);
      updateLocal("'stack_entries_invocation_out.csv'", "stack_entries");

      printLog("Downloading stack_entries with debugger as event to local");
      //retrieve all stack_entries of type DebuggerEvent and the event_id
      // $conn = connectToBlackBox();
      getDebuggerStackEntries($conn, $debuggerEventList);
      // disconnectServer($conn);
      updateLocal("'stack_entries_debugger_out.csv'", "stack_entries");
      
      writeCheckpoint($checkpointFile, $checkPoint);
   }

   if($currentStatus < ++$checkPoint){
      //bench_objects table
      //Populate bench_objects using package_id
      printLog("Getting all package_id(s) where event_type is BenchObject");
      $connLocal = connectToLocal("capstoneLocal");
      $query = "SELECT distinct package_id from master_events where event_type = 'BenchObject'";
      $benchPackageList = getResult($connLocal, $query);
      disconnectServer($connLocal);

      printLog("Downloading bench_objects to local");
      // $conn = connectToBlackBox();
      $fileCreated = getBenchObjects($conn, $benchPackageList);
      // disconnectServer($conn);
      updateLocal($fileCreated);
      
      writeCheckpoint($checkpointFile, $checkPoint);
   }

   if($currentStatus < ++$checkPoint){
      //Bench object fixture
      printLog("Getting all id(s) from bench_objects");
      $connLocal = connectToLocal("capstoneLocal");
      $query = "SELECT distinct id from bench_objects";
      $benchObjectList = getResult($connLocal, $query);
      disconnectServer($connLocal);

      printLog("Downloading bench_objects_fixture to local");
      // $conn = connectToBlackBox();
      $fileCreated = getBenchObjectsFixture($conn, $benchObjectList);
      // disconnectServer($conn);
      updateLocal($fileCreated);
      
      writeCheckpoint($checkpointFile, $checkPoint);
   }

   if($currentStatus < ++$checkPoint){
      //Debugger Events
      printLog("Downloading degbugger_events to local");
      // // $conn = connectToBlackBox();
      $fileCreated = getDebuggerEvents($conn, $debuggerEventList);
      // // disconnectServer($conn);
      updateLocal($fileCreated);
      
      writeCheckpoint($checkpointFile, $checkPoint);
   }

   if($currentStatus < ++$checkPoint){
      //Codepad Events
      //select all event_id with type CodepadEvent
      printLog("Getting event_id(s) from master_events");
      $connLocal = connectToLocal("capstoneLocal");
      $query = "SELECT distinct event_id from master_events where event_type = 'CodepadEvent'";
      $codePadEventList = getResult($connLocal, $query);
      disconnectServer($connLocal);

      printLog("Downloading codepad_events to local");
      // $conn = connectToBlackBox();
      $fileCreated = getCodePadEvents($conn, $codePadEventList);
      // disconnectServer($conn);
      updateLocal($fileCreated);
      
      writeCheckpoint($checkpointFile, $checkPoint);
   }

   if($currentStatus < ++$checkPoint){
      //Extensions
      //select all master_event_id from local master_events
      printLog("Getting id(s) from master_events");
      $connLocal = connectToLocal("capstoneLocal");
      $query = "SELECT distinct id from master_events";
      $masterEventIdList = getResult($connLocal, $query);
      disconnectServer($connLocal);

      printLog("Downloading extensions to local");
      // $conn = connectToBlackBox();
      $fileCreated = getExtensions($conn, $masterEventIdList);
      // disconnectServer($conn);
      updateLocal($fileCreated);
      
      writeCheckpoint($checkpointFile, $checkPoint);
   }

   if($currentStatus < ++$checkPoint){
      //Test
      printLog("Getting session_id(s) from master_events");
      $connLocal = connectToLocal("capstoneLocal");
      $query = "SELECT distinct session_id from master_events where event_type = 'Test'";
      $testidList = getResult($connLocal, $query);
      disconnectServer($connLocal);

      printLog("Downloading tests to local");
      // $conn = connectToBlackBox();
      $fileCreated = getTests($conn, $testidList);
      // disconnectServer($conn);
      updateLocal($fileCreated);

      writeCheckpoint($checkpointFile, $checkPoint);
   }

   if($currentStatus < ++$checkPoint){
      //Test Results
      printLog("Getting session_id(s) from master_events");
      $conn = connectToLocal("capstoneLocal");
      $query = "SELECT distinct session_id from master_events where event_type = 'TestResult'";
      $testResultList = getResult($conn, $query);
      disconnectServer($conn);

      printLog("Downloading tests_results to local");
      // $conn = connectToBlackBox();
      $fileCreated = getTests($conn, $testResultList);
      // disconnectServer($conn);
      updateLocal($fileCreated);
      
      writeCheckpoint($checkpointFile, $checkPoint);
   }

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