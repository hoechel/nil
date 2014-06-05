<?php
Bootstrap::Set_Error_Reporting();
Bootstrap::Remove_Magic_Quotes();
Bootstrap::Unregister_Globals();
Session::Start_Session();
Session::Save_Project_Constants();