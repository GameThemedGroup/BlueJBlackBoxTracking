<?php
<!-- \subsubsection{Top Ten Compiler Errors} -->

$query = "SELECT distinct message, count(message) as count 
               from compile_outputs 
               group by message order by count(message) desc limit 10";

<!-- \subsubsection{Last Few Events Before Closing BlueJ} -->

$query = "SELECT distinct name 
               from master_events 
               where name != 'bluej_start' 
               and user_id = ".$userid." 
               and participant_id = ".$participantid." 
               and created_at BETWEEN '" . $startDate . "' and '" . $endDate . "'";

$query = "SELECT session_id, sequence_num 
               from master_events 
               where name = 'bluej_finish' 
               and user_id = ".$userid." 
               and participant_id = ".$participantid." 
               and created_at BETWEEN '".$startDate."' and '".$endDate."' order by id desc";

$query = "SELECT name 
               From master_events 
               WHERE session_id= '".$bluejClose['session_id']. "'" . " 
               and sequence_num between " . $min . " AND " . $max;

<!-- \subsubsection{Number of Game Calls} -->

$query = "SELECT event_id 
               From master_events 
               WHERE event_type='Invocation' 
               and created_at BETWEEN '".$startDate. "' and '" .$endDate. "' 
               and user_id=".$userId." 
               and participant_id=". $participantId;

$query = "SELECT code 
               From invocations 
               where code like '%main%' and id=" . $event;

<!-- \subsubsection{Total Number of Sessions Per User} -->

$query = "SELECT user_id, count(user_id) as count 
               from sessions 
               where participant_id != 1 
               and created_at BETWEEN '".$startDate ."' and '".$endDate."' group by user_id";

<!-- \subsubsection{Occurrence of Sessions} -->

$query = "SELECT count(id) as totalSessions 
               from sessions 
               where user_id = " . $userid . " 
               and participant_id = " .$participantid. " 
               and created_at BETWEEN'" . $startDate->format('Y-m-d') . "' and '" .$nextDay->format('Y-m-d'). "'";

<!-- \subsubsection{Duration Spent In SpaceSmasherAPI} -->

$query = "SELECT distinct project_id 
               from master_events 
               where user_id=" . $userId ." 
               and participant_id =" . $participantId . " order by project_id asc";

$query = "SELECT id 
               from packages
               where project_id =".$project." 
               and name LIKE '%SpaceSmasher%' order by project_id asc";

$query = "SELECT created_at 
               from master_events 
               WHERE created_at BETWEEN '".$startDate. "' and '" .$endDate. "' 
               and name = 'package_opening' 
               and package_id= '".$package."' 
               and project_id = '".$project."' order by created_at asc";

$query = "SELECT created_at 
               From master_events 
               WHERE created_at BETWEEN '".$startDate. "' and '" .$endDate. "' 
               and name = 'package_closing' 
               and package_id= '".$package."' 
               and project_id = '".$project."' order by created_at asc";

<!-- \subsubsection{Participation Rate Per Instructor} -->

$query = "SELECT distinct s.user_id, s.participant_id, s.participant_identifier 
               FROM (SELECT @experiment:='uwbgtcs') unused, sessions_for_experiment s 
               where created_at between '" .$startDate. "' and '" .$endDate. "'";


?>