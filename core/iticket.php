<?php
interface ITicket
{
    // Force Extending class to define this method
   public function Sync();
   public function Get($cve_number);
 
 
}


?>