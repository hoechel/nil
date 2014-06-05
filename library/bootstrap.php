<?php
Bootstrap::Set_Error_Reporting();
Bootstrap::Remove_Magic_Quotes();
Bootstrap::Unregister_Globals();
Session::Start_Session();
Session::Store_Project_Constants();
$reflect = new ReflectionClass(DATABASE);
$database = $reflect->getName();
$database::Connect_Host();
$database::Store_Project_Vars();
Bootstrap::Call_Hook();