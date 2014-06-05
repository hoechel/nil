<?php
/**
 * NIL /config/bootstrap.ini.php
 * 
 * boot strapping of the program.
 * 
 * @todo long description
 * 
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * 
 * @package NIL Core
 * @author Frank Hoechel <hoechel@gmail.com>
 * @copyright 2013 Frank Hoechel
 * @license GPL <http://opensource.org/licenses/GPL-3.0>
 * @version 0.3
 */
 
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