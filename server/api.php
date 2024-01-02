<?php
ini_set('display_errors', 1);
require 'databaseCommunication.php';

$settings = parse_ini_file("appsettings.ini", true);

$db = new DatabaseConnection;
$db->Connect($settings["database"]["path"]);

header('Content-Type: application/json; charset=utf-8');

if( $_SERVER["REQUEST_METHOD"] == "GET" )
{
	if( !empty($_GET["version"]))
	{
		echo json_encode($db->InsertStepperStart($_GET["version"]));
	}
	else if( !empty($_GET["all"]))
	{
		echo json_encode($db->GetAll());
	}
}
?>
