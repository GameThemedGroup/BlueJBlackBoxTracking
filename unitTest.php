<?php
   include '../CoreFunctions.php';
   include 'mocks/conn.php';

   class unitTest extends PHPUnit_Framework_TestCase{
      private static $conn;
      private static $useridList;
      private static $sessionidList;
      private static $packageidList;
      private static $projectidList;
      private static $compileEventIdList;
      private static $sourceFileidList;
      private static $statusFile;
      private static $downloadStatus;
      private static $testArray = array(1,2,3,4);
      private static $fileCreated;

      public static function setUpBeforeClass()
      {
         global $root;
         $root = "tests/";
         // self::$conn = connectToLocal("capstoneTest");
         self::$conn = new MockConn();

      }

      public function testInspector(){
         // $fileCreated = getInspector()

         // assert size;
         // assert filecreated with expected files;

      }
      
      public function writeDownloadStatus(){
         $status = "test";
         $checkPoint = array();
         writeCheckpoint($checkPoint, $status);
      }
   
      public function readDownloadStatus(){
         $this->writeDownloadStatus();
         $checkPoint = readCheckpoint(self::$statusFile);
         $this->assertEquals($checkPoint['test'], 1, "Checkpoint not set");
      }
      /**
       *@test
       */
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