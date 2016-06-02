<?php 
   include '../CoreFunctions.php';
   include 'result.php';

   class MockConn{
      function query($statement){
         $fileName; 
         if (strpos($statement, '@experiment')) {
            $fileName = $useridList;
         } else if(strpos($statement, 'distinct'))){
            $pieces = explode(" ", $statement)
            $fileName = $pieces[4];
         } else {
            $pieces = explode(" ", $statement)
            $fileName = $pieces[3];
         }

         return new MockResult ($fileName);
      }
      function close(){}
   }
   
?>