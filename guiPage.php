<!DOCTYPE html>
<html>
<head>
<!-- <link rel="stylesheet" type="text/css" href="datepicker/jquery.datepick.css">  -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
<!-- <script type="text/javascript" src="datepicker/jquery.plugin.js"></script> 
<script type="text/javascript" src="datepicker/jquery.datepick.js"></script> -->

<style type="text/css">
   html, body { height: 100%; padding: 0; margin: 0; }
   div {overflow: scroll;}
   table {
      border-collapse: collapse;
      table-layout: fixed;
      width: 100%;
   }
   td, th{
      border: 1px solid;
      word-wrap: break-word;
   }
   #left {width:20%; height:100%;float:left;background:#C0C0C0;}
   #right {width:80%; height:100%;float:left}
   #topLeft { background:#888888; height:40%;}
   /*#topRight { background:#C0C0C0; height:20%; }*/
   #bottomLeft {height:60%;}
   #bottomLeft table tr td:first-child, #bottomLeft table tr th:first-child {
      width: 80%;
   }
   #bottomLeft td, #bottomLeft th {
      width: 20%;
   }
   #bottomRight { background: white; height:auto;}
   #loading {
      position: absolute;
      left: 50%;
      top: 50%;
      display: none;
      width: 100px;
      height: 100px;
      z-index: 1000;
   }
</style>
</head>
<body>

<script type="text/javascript" src="fusioncharts/js/fusioncharts.js"></script>
<script type="text/javascript" src="fusioncharts/js/themes/fusioncharts.theme.fint.js"></script>

<img id="loading" src="loading.gif"/>

<?php
   // include 'CoreFunctions.php';
   // include 'questionsToAnswer.php';
   // include 'graphFunctions.php';
   // include("fusioncharts/fusioncharts.php");
?>
<div id="left">
   <div id="topLeft">
      Research Questions:<br><br>
      <form method="get" style="display:block">
         <input type="radio" name="question" value="numberOfGameExecution.php" checked>Number of Game Execution<br>
         <input type="radio" name="question" value="graphTotalSessionsPerUser.php">Total Sessions Per User<br>
         <input type="radio" name="question" value="topTenComileErrors.php">Top Ten Compile Errors<br>
         <input type="radio" name="question" value="occurrenceOfSessions.php">Occurance of Sessions<br>
         <input type="radio" name="question" value="lastFewEvents.php">Last few events before BlueJ closes<br>
         <input type="radio" name="question" value="durationOfSpaceSmasherAPI.php">Duration on SpaceSmasherAPI<br><br>
<!--          <input type="radio" name="question" value="participationRate.php">Participation Rate(TODO)<br>
         <input type="radio" name="question" value=""># of Compiles Per Todo File(TODO)<br>
         <input type="radio" name="question" value=""># of Compiles Per File(TODO)<br> -->
         <INPUT TYPE = "number" placeholder="User ID" NAME="userid" min="0">
         <INPUT TYPE = "number" placeholder="Participant ID" NAME="participantid" min="0">
         <INPUT TYPE = "Text" placeholder ="Start Date" NAME = "startDate">
         <INPUT TYPE = "Text" placeholder ="End Date" NAME = "endDate">
         <INPUT TYPE = submit name="Submit">
      </form>    
   </div>
   <div id="bottomLeft">
      
   </div>
</div>

<div id="right">
   <!-- <div id="topRight"></div> -->
   <div id="bottomRight">
      
   </div>
</div>

<script type="text/javascript">
   
   $("#topLeft form").submit(function() {
         $("#bottomLeft").html("");

         var startDate = $("#startDate").val();
         var endDate = $("#endDate").val();
         var url = "researchQuestions/" + $('input[name=question]:checked', '#topLeft form').val();

         $("#loading").css("display", "block");
         $.ajax({
            url: url + "?" + $("#topLeft form").serialize(),
            success: function(result) {
               var json = JSON.parse(result);
               if(json.error){
                  alert(json.error);
               } else {
                  var sideChart = json.sideChart;
                  $("#bottomLeft").html(sideChart);

                  var chart = new FusionCharts({
                     type: json.type,
                     renderAt: "bottomRight",
                     width:"100%",
                     height:"100%",
                     dataFormat: "json",
                     dataSource: json.data,
                  });
                  chart.render();
               }
               $("#loading").css("display", "none");
            }
         });
         return false;
   });
</script>

</body>
</html>