<!DOCTYPE html>
<html>
<body>

<style type="text/css">
   html, body { height: 100%; padding: 0; margin: 0; }
   div {overflow: scroll;}
   #topLeft { background: #DDD; width:20%; height:20%; float:left;}
   #topRight { background: #AAA; width:80%; height:20%; float:right;}
   #bottomLeft { background: #777; width:20%; height:80%; float:left;}
   #bottomRight { background: white; width:80%; height:80%; float:right;}
</style>   
<!-- 
<script src="charts4php/lib/js/jquery.min.js"></script>
<script src="charts4php/lib/js/chartphp.js"></script>
<script src="//code.jquery.com/ui/1.11.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="charts4php/lib/js/chartphp.css">
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css">
 -->

<script type="text/javascript" src="fusioncharts/js/fusioncharts.js"></script>
<script type="text/javascript" src="fusioncharts/js/themes/fusioncharts.theme.fint.js"></script>

<?php
   include 'CoreFunctions.php';
   include 'questionsToAnswer.php';
   include 'graphFunctions.php';
   // require_on  ce("phpChart_Lite/conf.php");
   include("fusioncharts/fusioncharts.php");

   // function getUserIdByType($typeOfInfo, $infoId){
   //    switch ($typeOfInfo){
   //       case "master_event_id":
   //          $query = "select distinct user_id from master_events where id =" .$infoId."";
   //          getByOtherID($query);
   //          break;
   //       case "session_id":
   //          $query = "select distinct user_id from master_events where session_id =" .$infoId."";
   //          getByOtherID($query);
   //          break;
   //       case "package_id":
   //          $query = "select distinct user_id from master_events where package_id =" .$infoId."";
   //          getByOtherID($query);
   //          break;
   //       case "project_id":
   //          $query = "select distinct user_id from master_events where project_id =" .$infoId."";
   //          getByOtherID($query);
   //          break;
   //       case "source_file_id":
   //          $query = "select distinct project_id from source_files where id =" .$infoId."";
   //          $projectId = getByOtherID($query);
   //          if($projectId->num_rows > 0){
   //             foreach($projectId as $project){
   //                $query = "select distinct user_id from master_events where project_id =" .$project[project_id].""; 
   //                $userId = getByOtherID($query);
   //             }
   //          }
   //          printResultInTable($userId);
   //          break;
   //    }
   // }

   // function getByOtherID($query){
   //    $conn = connectToLocal("capstoneLocal");
   //    $useridList = getResult($conn, $query);
   //    disconnectServer($conn);
   //    return $useridList;
   // }

?>

<div id="topLeft">
   <?php
      // include 'CoreFunctions.php';
      // listOfUserId();
      //Returns all user_id from local database with current data
      getUserList();

      function listOfUserId(){
         $conn = connectToLocal('capstoneLocal');
         $query = "select id from users order by id asc";
         $useridList = getResult($conn, $query);
         // $startDate = '2016-01-01';
         // $endDate = '2016-01-25';

         if($useridList->num_rows > 0){
            echo "Total Users: " . $useridList->num_rows . "<br>";?>
            <form name="form1" method="POST" action="<?php $_SERVER['PHP_SELF'];?>">
            <select name="user_id">
            <option value=""></option> <!--Can have or not, to be tested-->
            <?php
            foreach($useridList as $user){
               echo "<option value='" . $user[id] . "'>" . $user[id] . "</option>";
            }
            echo "</select><br>";   
         }
      }
   ?>
   <input type="submit" name="submit2" value="Select User ID">

   <?php   
      if(isset($_POST['submit2'])){
         $name = $_POST['user_id'];
         echo "<br>Selected User ID: " . $name . "<br>";
      }
   ?>
   <a href='guiPage.php?graphTotalSessionsPerUser=true'>Total Sessions Per User</a></br>
   <a href='guiPage.php?invocationsPerUser=true'>Total Invocations Per User</a></br>
   <a href='guiPage.php?numberOfCompilePerTodo=true'>Number of Compiles Per Todo File</a></br>
   <a href='guiPage.php?numberOfCompilePerFile=true'>Number of Compiles Per File</a></br>
   <a href='guiPage.php?topTenComileErrors=true'>Top Ten Compile Errors</a></br>
   <!-- <a href='guiPage.php?durationBetweenTodo=true'>Duration Between Todos</a></br> -->
   <a href='guiPage.php?occuranceOfSessions=true'>Occurance of Sessions</a></br>
   <a href='guiPage.php?numberOfGameExecution=true'>Number of Game Execution</a></br>
   <a href='guiPage.php?participationRate=true'>Participation Rate</a></br>
   <a href='guiPage.php?lastFewEvents=true'>Last few events before BlueJ closes</a></br>
   <a href='guiPage.php?durationOfSpaceSmasherAPI=true'>Duration on SpaceSmasherAPI</a></br>
   
</div>

<div id="topRight">
   <?php

   ?>
</div>

<div id="bottomLeft">
   <?php
      // if(isset($_GET['graph'])){
      //    graphTotalSessionsPerUser();
      // }
      // echo "Get user id by session_id<br>";
      // getUserIdByType("session_id",10307663);
      // echo "Get user id by master_event_id<br>";
      // getUserIdByType("master_event_id",720551340);
      // echo "Get user id by package_id<br>";
      // getUserIdByType("package_id",4511332);
      // echo "Get user id by project_id<br>";
      // getUserIdByType("project_id",4236079);
      // echo "Get user id by source_file_id<br>";
      // getUserIdByType("source_file_id",28209599);
      // numberOfCompilePerTodo();

      if(isset($_GET['graphTotalSessionsPerUser'])){
         graphTotalSessionsPerUser();
      }
      if(isset($_GET['numberOfCompilePerFile'])){
         numberOfCompilePerFile();
      }
      if(isset($_GET['numberOfCompilePerTodo'])){
         numberOfCompilePerTodo();
      }
      if(isset($_GET['invocationsPerUser'])){
         invocationsPerUser();
      }
      if(isset($_GET['topTenComileErrors'])){
         topTenComileErrors();
      }
      if(isset($_GET['durationBetweenTodo'])){
         durationBetweenTodo();
      }
      if(isset($_GET['occuranceOfSessions'])){
         occuranceOfSessions();
      }
      if(isset($_GET['numberOfGameExecution'])){
         numberOfGameExecution();
      }
      if(isset($_GET['participationRate'])){
         participationRate();
      }
      if(isset($_GET['lastFewEvents'])){
         lastFewEvents();
      }
      if(isset($_GET['durationOfSpaceSmasherAPI'])){
         durationOfSpaceSmasherAPI();
      }

   ?>
</div>

<div id="bottomRight">
   <?php
      // numberOfCompilePerFile();
   ?>
</div>

</body>
</html>