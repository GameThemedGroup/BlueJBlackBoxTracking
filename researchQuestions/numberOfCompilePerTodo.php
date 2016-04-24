<?php
   include "../CoreFunctions.php";
   include "../graphFunctions.php";

   // //Question:
   // //Total time per "to-do".
   // //We might be able to tell which files do students spend more time on by the times they are compiled
   
   // //Answer: 
   // //Compute the number of times a TODO file is compiled between the date 2016-01-01 to 2016-01-25 on a per user base
   
   // //Implication of answer:
   // //Some To-do files have higher number of compilation as much as 100 times.
   // //Which could be an indicator of the difficulties student encounters when learning introductory programming class or
   // //could mean students are more engaged into experimentation of the file. 
   // //The number of compilation decreases over the future To-do(s) which could mean students are learning and finding the later 
   // //tasks easier to solve or the Todo works became easier. 
   
   // //Answer's correctness: 
   // //Total time spent on a file can not be identified because an entry in the master_events shows when the file was compiled, but doesn't track 
   // //anything which identifies as end of a file edit (the only event says the file is being work on)
   
   // //Methods for improving correctness: 
   
   
   $conn = connectToLocal($db);
   $query = "select id from users order by id asc";
   $useridList = getResult($conn, $query);

   $dateRange = getStartEndDate();
   $startDate = $dateRange[0];
   $endDate = $dateRange[1];

   if($useridList->num_rows > 0){
      echo "Total Users: " . $useridList->num_rows . "<br>";
      foreach($useridList as $user){
         $arrayOfFileNames = array();
         //echo "user_id: ".$user[id]."<br>";
         //using user_id, query for all event_id and session_id events where event_type = CompileEvents between the date 2016-01-01 to 2016-01-25
         //which is the time up till the first assignment due date
         $query = "SELECT event_id From master_events WHERE event_type='CompileEvent' and created_at BETWEEN '".$startDate. "' and '" .$endDate. "' and user_id=".$user['id'];
         // echo $query;
         $compileEvents = getResult($conn, $query);

         //To retrieve source_file name we need to match compile_input.source_file_id to source_files.id
         //Then pick out those matches by selecting rows in compile_inputs.compile_event_id that is found from the above query
         //that is event_id when event_type = CompileEvent from the master_events
         if($compileEvents->num_rows > 0){
            echo "user_id: ".$user['id']."<br>";
            foreach($compileEvents as $event){
               $query = "SELECT source_files.name From source_files JOIN compile_inputs on compile_inputs.source_file_id=source_files.id where compile_inputs.compile_event_id='".$event['event_id']."' and name LIKE '%TODO%' group by source_files.name";
               $results = getResult($conn, $query);
               // echo "event_id= " .$event[event_id] . "<br>";
               while($row = $results->fetch_assoc()){
                  foreach($row as $field){
                     // echo "File name= " . $field . "<br>";
                     if(!array_key_exists($field, $arrayOfFileNames)){
                        $arrayOfFileNames[$field] = 1;
                     } else {
                        $arrayOfFileNames[$field]++;
                     }
                  }
               }
            }
            if(count($arrayOfFileNames)>0){
               ksort($arrayOfFileNames);
               echo "<pre>";
               print_r($arrayOfFileNames);
               echo "</pre>"; 
            } else {
               echo "No Compile Event<br><br>";
            }
         }
      }

      disconnectServer($conn);
   }
?>