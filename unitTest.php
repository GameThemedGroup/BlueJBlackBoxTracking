<?php
   include 'CoreFunctions.php';

   class unitTest extends PHPUnit_Framework_TestCase{
      private static $conn;
      private static $useridList;
      private static $sessionidList;
      private static $packageidList;
      private static $projectidList;
      private static $compileEventIdList;
      private static $sourceFileidList;
      private static $statusFile = "downloadStatus";
      private static $downloadStatus;
      private static $testArray = array(1,2,3,4);
      private static $fileCreated;

      public static function setUpBeforeClass()
      {
         self::$conn = connectToLocal("capstoneTest");

         // $query = "SELECT distinct s.user_id FROM (SELECT @experiment:='uwbgtcs') unused, sessions_for_experiment s";
         $query = "SELECT distinct user_id FROM master_events";
   
         self::$useridList = getResult(self::$conn, $query);

         // $query = "SELECT s.id FROM (SELECT @experiment:='uwbgtcs') unused, sessions_for_experiment s";
         $query = "SELECT id FROM sessions";
   
         self::$sessionidList = getResult(self::$conn, $query);

         if(!isTableEmpty(self::$conn, "master_events")){
            // echo "table not empty";
            $query = "SELECT distinct project_id From master_events";
            self::$projectidList = getResult(self::$conn, $query);
            $query = "SELECT distinct package_id From master_events";
            self::$packageidList = getResult(self::$conn, $query);
         }

         if(!isTableEmpty(self::$conn, "source_files")){
            // echo "table not empty";
            $query = "SELECT id From source_files";
            self::$sourceFileidList = getResult(self::$conn, $query);
         }

         if(!isTableEmpty(self::$conn, "compile_inputs")){
            // echo "table not empty";
            $query = "SELECT compile_event_id, source_file_id from compile_inputs";
            self::$compileEventIdList = getResult(self::$conn, $query);
         }
      }

      public function GetMasterTable(){
         self::$fileCreated = getMasterEvents(self::$conn, self::$useridList);
         // echo self::$fileCreated;
         $this->assertTrue(file_exists(self::$fileCreated), "File has not been created");
      }

      public function GetUserTable(){
         self::$fileCreated = getUsers(self::$conn, self::$useridList);
         $this->assertTrue(file_exists(self::$fileCreated), "File has not been created");
      }

      public function GetProjectTable(){
         self::$fileCreated = getProjects(self::$conn, self::$useridList);
         $this->assertTrue(file_exists(self::$fileCreated), "File has not been created");
      }

      public function GetSessionTable(){
         self::$fileCreated = getSessions(self::$conn, self::$sessionidList);
         $this->assertTrue(file_exists(self::$fileCreated), "File has not been created");
      }

      public function GetInvocationTable(){
         self::$fileCreated = getSessions(self::$conn, self::$sessionidList);
         $this->assertTrue(file_exists(self::$fileCreated), "File has not been created");
      }

      public function GetPackageTable(){
         self::$fileCreated = getPackages(self::$conn, self::$projectidList);
         $this->assertTrue(file_exists(self::$fileCreated), "File has not been created");
      }

      public function GetSourceFileTable(){
         self::$fileCreated = getSourceFiles(self::$conn, self::$projectidList);
         $this->assertTrue(file_exists(self::$fileCreated), "File has not been created");
      }

      public function GetSourceHistoryTable(){
         self::$fileCreated = getSourceHistories(self::$conn, self::$sourceFileidList);
         $this->assertTrue(file_exists(self::$fileCreated), "File has not been created");
      }

      public function GetBreakPointTable(){
         self::$fileCreated = getBreakpoints(self::$conn, self::$sourceFileidList);
         $this->assertTrue(file_exists(self::$fileCreated), "File has not been created");
      }

      public function GetCompileInputTable(){
         self::$fileCreated = getCompileInputs(self::$conn, self::$sourceFileidList);
         $this->assertTrue(file_exists(self::$fileCreated), "File has not been created");
      }

      public function GetCompileOutputTable(){
         self::$fileCreated = getCompileOutputs(self::$conn, self::$compileEventIdList);
         $this->assertTrue(file_exists(self::$fileCreated), "File has not been created");
      }

      public function GetCompileEventTable(){
         self::$fileCreated = getCompileEvents(self::$conn, self::$compileEventIdList);
         $this->assertTrue(file_exists(self::$fileCreated), "File has not been created");
      }

      public function GetInspectorTable(){
         self::$fileCreated = getIspectors(self::$conn, self::$sessionidList);
         $this->assertTrue(file_exists(self::$fileCreated), "File has not been created");
      }

      public function GetSourceHashesTable(){
         self::$fileCreated = getSourceHashes(self::$conn, self::$packageidList);
         $this->assertTrue(file_exists(self::$fileCreated), "File has not been created");
      }

      public function GetFixtureTable(){
         self::$fileCreated = getFixtures(self::$conn, self::$sourceFileidList);
         $this->assertTrue(file_exists(self::$fileCreated), "File has not been created"); 
      }

      public function GetBenchObjectTable(){
         $query = "SELECT distinct package_id from master_events where event_type = 'BenchObject'";
         $benchPackageList = getResult(self::$conn, $query);
         self::$fileCreated = getBenchObjects(self::$conn, $benchPackageList);
         $this->assertTrue(file_exists(self::$fileCreated), "File has not been created"); 
      }

      public function GetDebuggerTable(){
         $query = "SELECT distinct event_id from master_events where event_type = 'DebuggerEvent'";
         $debuggerEventList = getResult(self::$conn, $query);
         self::$fileCreated = getDebuggerEvents(self::$conn, $debuggerEventList);
         $this->assertTrue(file_exists(self::$fileCreated), "File has not been created"); 
      }

      public function GetExtensionTable(){
         $query = "SELECT distinct id from master_events";
         $masterEventIdList = getResult(self::$conn, $query);
         self::$fileCreated = getExtensions(self::$conn, $masterEventIdList);
         $this->assertTrue(file_exists(self::$fileCreated), "File has not been created"); 
      }

      public function GetInstallationTable(){
         $query = "SELECT distinct installation_details_id From sessions";
         $installationidList = getResult(self::$conn, $query);
         self::$fileCreated = getInstallations(self::$conn, $installationidList);
         $this->assertTrue(file_exists(self::$fileCreated), "File has not been created"); 
      }

      public function GetCodepadEventsTable(){}
      public function GetBenchObjectFixtureTable(){}
      public function GetTestTable(){}
      public function GetTestResultTable(){}
      public function GetClientTable(){}
      
      public function writeDownloadStatus(){
         $status = "0\n";
         writeCheckpoint(self::$statusFile, $status);
         // $status = "5\n";
         // writeDownloadStatus(self::$statusFile, $status);
         // $status = "10\n";
         // writeDownloadStatus(self::$statusFile, $status);
      }
      
      /**
       *@test
       */
      public function readDownloadStatus(){
         $test = readCheckpoint(self::$statusFile);
         print_r($test);
      }

      public function UpdateTable(){
         $updateStatus = updateLocal(self::$fileCreated);
         $this->assertTrue($updateStatus, "Update Failed");
      }

      public static function tearDownAfterClass()
      {
         disconnectServer(self::$conn);
      }
   }
?>