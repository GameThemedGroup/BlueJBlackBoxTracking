<!DOCTYPE html>
<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>

<!-- CSS style settings for the page -->
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
   /*settings for each <div> in the page */
   /*the page is divided into a left and right section*/
   /*right section displays the graph*/
   /*left section contains Research Questions to display and adjustable parameters*/
   /*left section also display statistics of the research questions when applicable*/
   #left {width:20%; height:100%;float:left;background:#C0C0C0;}
   #right {width:80%; height:100%;float:left}
   #topLeft { background:#888888; height:40%;}
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
<!-- library for generating graphs -->
<script type="text/javascript" src="Implementations/visualization/fusioncharts/js/fusioncharts.js"></script>
<script type="text/javascript" src="Implementations/visualization/fusioncharts/js/themes/fusioncharts.theme.fint.js"></script>
<!-- loading image to tell user process is occuring, disappears when graphs are ready to be shown -->
<img id="loading" src="Implementations/visualization/loading.gif"/>

<!-- Top Left sectin of the page contain Research Question in a checkbox fashion -->
<div id="left">
   <div id="topLeft">
      Research Questions:<br><br>
      <form method="get" style="display:block">
         <input type="radio" name="question" value="numberOfGameExecution.php" checked>Number of Game Execution<br>
         <input type="radio" name="question" value="graphTotalSessionsPerUser.php">Total Sessions Per User<br>
         <input type="radio" name="question" value="topTenCompilerErrors.php">Top Ten Compile Errors<br>
         <input type="radio" name="question" value="occurrenceOfSessions.php">Occurance of Sessions<br>
         <input type="radio" name="question" value="lastFewEvents.php">Last few events before BlueJ closes<br>
         <input type="radio" name="question" value="durationOfSpaceSmasherAPI.php">Duration on SpaceSmasherAPI<br>
         <input type="radio" name="question" value="participationRate.php">Participation Rate<br><br>
         <INPUT TYPE = "number" placeholder="User ID" NAME="userid" min="0">
         <INPUT TYPE = "number" placeholder="Participant ID" NAME="participantid" min="0">
         <INPUT TYPE = "Text" placeholder ="Start Date" NAME = "startDate" value="2016-01-01">
         <INPUT TYPE = "Text" placeholder ="End Date" NAME = "endDate" value="2016-01-25">
         <INPUT TYPE = submit name="Submit">
      </form>    
   </div>
   <div id="bottomLeft">
      
   </div>
</div>

<div id="right">
   <div id="bottomRight">
      
   </div>
</div>

<!-- Handler for displaying graph and other message generated from individual research questions -->
<script type="text/javascript">
   $("#topLeft form").submit(function(e) {
         e.preventDefault();
         $("#bottomLeft").html("");

         var startDate = $("#startDate").val();
         var endDate = $("#endDate").val();
         var url = "Implementations/researchQuestions/" + $('input[name=question]:checked', '#topLeft form').val();

         $("#loading").css("display", "block");
         $.ajax({
            url: url + "?" + $("#topLeft form").serialize(),
            success: function(result) {
               $("#loading").css("display", "none");
               try {
                  var json = JSON.parse(result);
               } catch (e) {
                  alert(result);
                  return;
               }
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
            }
         });
         return false;
   });
</script>

</body>
</html>