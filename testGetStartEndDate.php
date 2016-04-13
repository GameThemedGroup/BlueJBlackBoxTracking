<?php
   include 'CoreFunctions.php';
   $endLine = "\n";
   // print_r(getStartEndDate());
   // echo exec('whoami');
   // $file = "downloadStatus";
   // writeDownloadStatus($file, 5);
   // $stat = readDownloadStatus($file);
   // if($stat < 6 && $stat > 4)
   //    echo "true";

   // printLog("Get all SourceFileID from local source_files");
   // $connLocal = connectToLocal("capstoneLocalForQA");
   $conn = connectToBlackBox();
   // $colName = "id";
   // // $query = "SELECT id from source_files";
   // $query = "SELECT ".$colName." from source_files";
   // $sourceFileIdList = getResult($connLocal, $query);

   // if(is_a($sourceFileIdList, 'mysqli_result')){
   //    echo "its an object" . $endLine;
   // } else {
   //    echo "NOT an object" . $endLine;
   // }
   
   // $resultToArray = objToArray($sourceFileIdList, $colName);
   
   // if(is_a($resultToArray, 'mysqli')){
   //    echo "its an object" . $endLine;
   // } else {
   //    echo "NOT an object" . $endLine;
   // }

   $colName = "user_id";
   $query = "SELECT distinct s." .$colName. " FROM (SELECT @experiment:='uwbgtcs') unused, sessions_for_experiment s";
   // $query = "SELECT distinct s.user_id FROM (SELECT @experiment:='uwbgtcs') unused, sessions_for_experiment s";
   
   //Store returned mysqli results object into $useridList
   $userids = getResult($conn, $query);
   $useridList = objToArray($userids, $colName);

   $useridFile = 'useridFile';
   writeCheckpoint($useridFile, serialize($useridList));
   // echo serialize($useridList);

   $serializedId = readCheckpoint($useridFile);
   // print_r(unserialize($serializedId));
   $newArray = unserialize($serializedId);
   // print_r($resultToArray);
   $fileCreated = getProjects($conn, $newArray);
   
   disconnectServer($conn);

   // $conn = connectToBlackBox();
   // $conn = connectToLocal('capstoneLocalForQA');
   // $colName = "source_file_id";
   // $query = "SELECT " . $colName . " from breakpoints";
   // $result = getResult($conn, $query);

   // $test = objToArray($result, $colName);

   // print_r($test);

   /*test for serial and unserialize array*/
   // $serializedTest = serialize($test);
   // echo $serializedTest;

   // $unSerial = unserialize($serializedTest);
   // print_r($unSerial);

   /*Test for query with date range*/
   // $query = "SELECT distinct s.user_id FROM (SELECT @experiment:='uwbgtcs') unused, sessions_for_experiment s";
   // $useridList = getResult($conn, $query);
   // $fileCreated = getMasterEvents($conn, $useridList, '2015-05-28', '2015-06-01');
   // disconnectServer($conn);
?>