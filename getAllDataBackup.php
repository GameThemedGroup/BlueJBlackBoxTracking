<?php
   include 'CoreFunctions.php';
   date_default_timezone_set("America/Los_Angeles"); 
   echo "Download and updating.....Start: " . date("Y-m-d h:i:a") . "<br>\n";

   //////////////////////////////////
   //establish connection and logging onto Whitebox MySql Server and selecting blackbox_production database
   $conn = connectToBlackBox();
   
   // $query = "SELECT * From master_events where user_id =1110282 order by created_at desc";
   // The above query was a test user_id used to track ourself

   //1. Get user_id using experiment_identifier (e.g.'uwbgtcs')
   //The following query returns all UNIQUE user_id using the experiment_identifier = uwbgtcs
   $query = "SELECT distinct s.user_id FROM (SELECT @experiment:='uwbgtcs') unused, sessions_for_experiment s";
   
   //Store returned mysqli results object into $useridList
   $useridList = getResult($conn, $query);

   //Get session_id using experiment_identifier (e.g.'uwbgtcs')
   //This is the MOST important query, as this is THE only way to tie the data to our research just like the user_id query
   $query = "SELECT s.id FROM (SELECT @experiment:='uwbgtcs') unused, sessions_for_experiment s";
   
   //Store returned mysqli results object into $sessionidList
   $sessionidList = getResult($conn, $query);
   
   //////////////////////////////////
   //disconnect from Whitebox MySql server
   disconnectServer($conn);
/*
   echo "Start populating local bench_objects: " . date("Y-m-d h:i:a") . "<br>\n";
   $conn = connectToLocal("capstoneLocal");
   $query = "SELECT distinct package_id from master_events where event_type = 'BenchObject'";
   $benchPackageList = getResult($conn, $query);
   disconnectServer($conn);

   $conn = connectToBlackBox();
   getBenchObjects($conn, $benchPackageList);
   disconnectServer($conn);
   // update bench_objects from local server with csv
   updateLocal("'bench_objects_out.csv'", "bench_objects");
   echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   exit();
*/

   // $masterEvents = getResult($conn, $query);   //get master events using list of user_id
   // $fieldNames = getFieldNames($masterEvents);

   // echo "<table border='1'>";
   // printArray($fieldNames, true);
   // printQueryResults($masterEvents);
   // echo "<table>";

   // $fieldNames = getFieldNames($userAndSessionId);
   // saveToCsv('outputCsv/userAndSessionId.csv', $userAndSessionId);   

   // echo "<table border='1'>";
   // printArray($fieldNames, true);
   // printQueryResults($userAndSessionId);
   // echo "<table>";

   // //////////////////////////////////////////////////////////////////////
   // //master_events table
   // //Use the above useridList to retrieve all related participants master events from the Whitebox server
   // //Populating the master_events table has to be the first table to downloaded because
   // //all other table will be related from it.

   // echo "Start populating local master_events with user_id: " . date("Y-m-d h:i:a") . "<br>\n";
   // $conn = connectToBlackBox();
   // getMasterEvents($conn, $useridList);
   // disconnectServer($conn);
   // //update master_events of local server
   // updateLocal("'master_event_out.csv'", "master_events");
   // echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   // //////////////////////////////////////////////////////////////////////

   // //////////////////////////////////////////////////////////////////////
   // //users table
   // //The table can be populated simply by query for every user_id from useridList retrieved earlier
   // echo "Start populating local users: " . date("Y-m-d h:i:a") . "<br>\n";
   // $conn = connectToBlackBox();
   // getUsers($conn, $useridList);
   // disconnectServer($conn);
   // // update users table from local server with csv
   // updateLocal("'users_out.csv'", "users");
   // echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   // //////////////////////////////////////////////////////////////////////

   // //////////////////////////////////////////////////////////////////////
   // //Use updated local master_events to retrieve all project_id related to experiment
   // echo "Start retrieving all project_id related to experiment: " . date("Y-m-d h:i:a") . "<br>\n";
   // $conn = connectToLocal("capstoneLocal");
   // $query = "SELECT distinct project_id From master_events";
   // $projectidList = getResult($conn, $query);
   // disconnectServer($conn);
   // echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   // //////////////////////////////////////////////////////////////////////

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
   // //Use sessionidList to populate local invocations table
   // echo "Start populating local invocations with session_id: " . date("Y-m-d h:i:a") . "<br>\n";
   // $conn = connectToBlackBox();
   // $invocationsList = getInvocations($conn, $sessionidList);
   // disconnectServer($conn);
   // // update invocations table from local server with csv
   // updateLocal("'invocation_out.csv'", "invocations");
   // echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   // //////////////////////////////////////////////////////////////////////

   // //////////////////////////////////////////////////////////////////////
   // //Use sessionidList to populate local sessions
   // echo "Start populating local sessions with session_id: " . date("Y-m-d h:i:a") . "<br>\n";
   // $conn = connectToBlackBox();
   // getSessions($conn, $sessionidList);
   // disconnectServer($conn);
   // // update invocations to local server with csv
   // updateLocal("'sessions_out.csv'", "sessions");
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

   // //////////////////////////////////////////////////////////////////////
   // //Use useridList to populate local projects table
   // echo "Start populating local projects: " . date("Y-m-d h:i:a") . "<br>\n";
   // $conn = connectToBlackBox();
   // getProjects($conn, $useridList);
   // disconnectServer($conn);
   // //update projects from local server
   // updateLocal("'projects_out.csv'", "projects");
   // //////////////////////////////////////////////////////////////////////

   // //////////////////////////////////////////////////////////////////////
   // //Use local projectidList from projects table to populate local packages table
   // echo "Start populating local packages: " . date("Y-m-d h:i:a") . "<br>\n";
   // $conn = connectToBlackBox();
   // getPackages($conn, $projectidList);
   // disconnectServer($conn);
   // //update packages table of local server
   // updateLocal("'packages_out.csv'", "packages");
   // echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   // //////////////////////////////////////////////////////////////////////

   // //////////////////////////////////////////////////////////////////////
   // //Populate source_files table using projectiList from local master_events table
   // echo "Start populating local source_files: " . date("Y-m-d h:i:a") . "<br>\n";
   // $conn = connectToBlackBox();
   // getSourceFiles($conn, $projectidList);
   // disconnectServer($conn);
   // //update source_files table of local server
   // updateLocal("'source_file_out.csv'", "source_files");
   // echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   // //////////////////////////////////////////////////////////////////////

   // //////////////////////////////////////////////////////////////////////
   // //Get all SourceFileID from local source_files table
   // echo "Get all SourceFileID from local source_files: " . date("Y-m-d h:i:a") . "<br>\n";
   // $conn = connectToLocal("capstoneLocal");
   // $query = "SELECT id from source_files";
   // $sourceFileIdList = getResult($conn, $query);
   // disconnectServer($conn);
   // echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   // //////////////////////////////////////////////////////////////////////

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

   // //////////////////////////////////////////////////////////////////////
   // //breakpoints table
   // //Populate local breakpoints using sourceFileIdList
   // echo "Start populating local breakpoints: " . date("Y-m-d h:i:a") . "<br>\n";
   // $conn = connectToBlackBox();
   // getBreakpoints($conn, $sourceFileIdList);
   // disconnectServer($conn);
   // //update breakpoints table of local server
   // updateLocal("'breakpoints_out.csv'", "breakpoints");
   // echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   // //////////////////////////////////////////////////////////////////////

   // //////////////////////////////////////////////////////////////////////
   // //compile_inputs table
   // //Populate local compile_inputs table with sourceFileIdList
   // echo "Start populating local compile_inputs: " . date("Y-m-d h:i:a") . "<br>\n";
   // $conn = connectToBlackBox();
   // getCompileInputs($conn, $sourceFileIdList);
   // disconnectServer($conn);
   // //update compile_inputs table of local server
   // updateLocal("'compile_inputs_out.csv'", "compile_inputs");
   // echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   // //////////////////////////////////////////////////////////////////////

   // //////////////////////////////////////////////////////////////////////
   // //compile_outputs table
   // //Populate local compile_outputs table with compile_event_id and source_file_id from local compile_inputs
   // echo "Start populating local compile_output: " . date("Y-m-d h:i:a") . "<br>\n";
   // $conn = connectToLocal("capstoneLocal");
   // $query = "SELECT compile_event_id, source_file_id from compile_inputs";
   // $compileEventIdList = getResult($conn, $query);
   // disconnectServer($conn);

   // // $conn = connectToBlackBox();
   // // getCompileOutputs($conn, $compileEventIdList);
   // // disconnectServer($conn);
   // // // update compile_outputs table of local server
   // // updateLocal("'compile_outputs_out.csv'", "compile_outputs");
   // // echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   // // //////////////////////////////////////////////////////////////////////

   // //////////////////////////////////////////////////////////////////////
   // //compile_events table
   // //Populate local compile_events table with compile_event_id from local compile_inputs
   // echo "Start populating local compile_events: " . date("Y-m-d h:i:a") . "<br>\n";
   // $conn = connectToBlackBox();
   // getCompileEvents($conn, $compileEventIdList);
   // disconnectServer($conn);
   // //update compile_events table of local server
   // updateLocal("'compile_events_out.csv'", "compile_events");
   // echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   // //////////////////////////////////////////////////////////////////////

   //////////////////////////////////////////////////////////////////////
   //Inspector 
   echo "Start populating local inspectors: " . date("Y-m-d h:i:a") . "<br>\n";
   $conn = connectToBlackBox();
   getIspectors($conn, $sessionidList);
   disconnectServer($conn);
   // update invocations to local server with csv
   updateLocal("'inspectors_out.csv'", "inspectors");
   echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   //////////////////////////////////////////////////////////////////////

   //////////////////////////////////////////////////////////////////////
   //source_hashes table
   //Populate local source_hashes table with package_id from local server
   echo "Start populating local source_hashes: " . date("Y-m-d h:i:a") . "<br>\n";
   $conn = connectToLocal("capstoneLocal");
   $query = "SELECT distinct package_id from master_events";
   $packageList = getResult($conn, $query);
   disconnectServer($conn);

   $conn = connectToBlackBox();
   getSourceHashes($conn, $packageList);
   disconnectServer($conn);
   //update source_hashes table of local server
   updateLocal("'source_hashes_out.csv'", "source_hashes");
   echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   //////////////////////////////////////////////////////////////////////

   //////////////////////////////////////////////////////////////////////
   //stack_entries table
   //Get all event_id from local master_events where event_type is Invocation or DebuggerEvent
   //The retrieved event_id will be used to populate stack_entries table
   echo "Getting all event_id where event_type is Invoation or DebuggerEvent: " . date("Y-m-d h:i:a") . "<br>\n";
   $conn = connectToLocal("capstoneLocal");
   
   //select all event_id with type invocation
   $query = "SELECT event_id from master_events where event_type = 'Invocation'";
   $invocationEventList = getResult($conn, $query);
   disconnectServer($conn);

   //select all event_id with type debuggerEvent
   $conn = connectToLocal("capstoneLocal");
   $query = "SELECT distinct event_id from master_events where event_type = 'DebuggerEvent'";
   $debuggerEventList = getResult($conn, $query);
   disconnectServer($conn);

   // retrieve all stack_entries of type Invocation and the event_id
   $conn = connectToBlackBox();
   getInvocationStackEntries($conn, $invocationEventList);
   disconnectServer($conn);
   //update stack_entries from local server
   updateLocal("'stack_entries_invocation_out.csv'", "stack_entries");
   echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";

   //retrieve all stack_entries of type DebuggerEvent and the event_id
   $conn = connectToBlackBox();
   getDebuggerStackEntries($conn, $debuggerEventList);
   disconnectServer($conn);
   //update stack_entries of local server
   updateLocal("'stack_entries_debugger_out.csv'", "stack_entries");
   echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   //////////////////////////////////////////////////////////////////////
   
   //////////////////////////////////////////////////////////////////////
   //fixtures table
   //Populate local fixtures table with sourceFileIdList
   echo "Start populating local fixtures: " . date("Y-m-d h:i:a") . "<br>\n";
   $conn = connectToBlackBox();
   getFixtures($conn, $sourceFileIdList);
   disconnectServer($conn);
   // update invocations to local server with csv
   updateLocal("'fixtures_out.csv'", "fixtures");
   echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   //////////////////////////////////////////////////////////////////////

   //////////////////////////////////////////////////////////////////////
   //bench_objects table
   //Populate bench_objects using package_id
   echo "Start populating local bench_objects: " . date("Y-m-d h:i:a") . "<br>\n";
   $conn = connectToLocal("capstoneLocal");
   $query = "SELECT distinct package_id from master_events where event_type = 'BenchObject'";
   $benchPackageList = getResult($conn, $query);
   disconnectServer($conn);

   $conn = connectToBlackBox();
   getBenchObjects($conn, $benchPackageList);
   disconnectServer($conn);
   // update bench_objects from local server with csv
   updateLocal("'bench_objects_out.csv'", "bench_objects");
   echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   //////////////////////////////////////////////////////////////////////

   //////////////////////////////////////////////////////////////////////
   //Bench object fixture
   echo "Start populating local bench_object_fixtures: " . date("Y-m-d h:i:a") . "<br>\n";
   $conn = connectToLocal("capstoneLocal");
   $query = "SELECT distinct id from bench_objects";
   $benchObjectList = getResult($conn, $query);
   disconnectServer($conn);

   $conn = connectToBlackBox();
   getBenchObjectsFixture($conn, $benchObjectList);
   disconnectServer($conn);
   // update bench_objects_fixtures to local server with csv
   updateLocal("'bench_objects_fixture_out.csv'", "bench_objects_fixtures");
   echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   //////////////////////////////////////////////////////////////////////

   //////////////////////////////////////////////////////////////////////
   //Debugger Events
   echo "Start populating local debugger_events: " . date("Y-m-d h:i:a") . "<br>\n";
   $conn = connectToBlackBox();
   getDebuggerEvents($conn, $debuggerEventList);
   disconnectServer($conn);
   // update invocations to local server with csv
   updateLocal("'debugger_events_out.csv'", "debugger_events");
   echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   //////////////////////////////////////////////////////////////////////

   //////////////////////////////////////////////////////////////////////
   //Codepad Events
   //select all event_id with type CodepadEvent
   echo "Start populating local codepad_events: " . date("Y-m-d h:i:a") . "<br>\n";
   $conn = connectToLocal("capstoneLocal");
   $query = "SELECT distinct event_id from master_events where event_type = 'CodepadEvent'";
   $codePadEventList = getResult($conn, $query);
   disconnectServer($conn);

   $conn = connectToBlackBox();
   getCodePadEvents($conn, $codePadEventList);
   disconnectServer($conn);
   // update invocations to local server with csv
   updateLocal("'codepad_events_out.csv'", "codepad_events");
   echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   //////////////////////////////////////////////////////////////////////

   ////////////////////////////////////////////////////////////////////
   //Extensions
   //select all master_event_id from local master_events
   echo "Start populating local extensions: " . date("Y-m-d h:i:a") . "<br>\n";
   $conn = connectToLocal("capstoneLocal");
   $query = "SELECT distinct id from master_events";
   $masterEventIdList = getResult($conn, $query);
   disconnectServer($conn);

   $conn = connectToBlackBox();
   getExtensions($conn, $masterEventIdList);
   disconnectServer($conn);
   // update invocations to local server with csv
   updateLocal("'extensions_out.csv'", "extensions");
   echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   ////////////////////////////////////////////////////////////////////

   ////////////////////////////////////////////////////////////////////
   //Test
   echo "Start populating local tests: " . date("Y-m-d h:i:a") . "<br>\n";
   $conn = connectToLocal("capstoneLocal");
   $query = "SELECT distinct session_id from master_events where event_type = 'Test'";
   $testidList = getResult($conn, $query);
   disconnectServer($conn);

   $conn = connectToBlackBox();
   getTests($conn, $testidList);
   disconnectServer($conn);
   // update tests to local server with csv
   updateLocal("'tests_out.csv'", "tests");
   echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";

   //Test Results
   echo "Start populating local test_results: " . date("Y-m-d h:i:a") . "<br>\n";
   $conn = connectToLocal("capstoneLocal");
   $query = "SELECT distinct session_id from master_events where event_type = 'TestResult'";
   $testResultList = getResult($conn, $query);
   disconnectServer($conn);
   
   $conn = connectToBlackBox();
   getTests($conn, $testResultList);
   disconnectServer($conn);
   // update test_results to local server with csv
   updateLocal("'test_results_out.csv'", "test_results");
   echo "Download and updating.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
   ////////////////////////////////////////////////////////////////////

   echo "Download and updating everything.....Done: " . date("Y-m-d h:i:a") . "<br>\n";
?>