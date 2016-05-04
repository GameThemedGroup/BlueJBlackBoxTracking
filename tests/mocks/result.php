<?php
   include '../CoreFunctions.php';


   class MockResult implements IteratorAggregate{ 
      function __construct($fileName){
         self::$data = restoreFromFile($fileName);
         self::$num_rows = count(self::$data);
      }

      public function getIterator(){
         return new ArrayIterator(self::$data);
      }
      
   }
?>