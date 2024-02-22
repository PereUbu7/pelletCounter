<?php
ini_set('display_errors', 1);
require 'dbCommunication.php';

$settings = parse_ini_file("appsettings.ini", true);

$db = new DbConnection;
$db->Connect($settings["database"]["path"]);

header('Content-Type: application/json; charset=utf-8');

if( $_SERVER["REQUEST_METHOD"] == "GET" )
{
	$from = isset($_GET["from"]) ? $_GET["from"] : null;
	$to = isset($_GET["to"]) ? $_GET["to"] : null;

	if( !empty($_GET["version"]) &&
		!empty($_GET["type"]) &&
		$_GET["type"] == "pellets" &&
		!empty($_GET["count"]))
	{
		echo json_encode($db->InsertStepperStart($_GET["version"], $_GET['count']));
	}
	else if( !empty($_GET["version"]) &&
		!empty($_GET["type"]) &&
		$_GET["type"] == "furnceRoomSensors" &&
		!empty($_GET["json_data"]))
	{
		echo json_encode($db->InsertSensorStats($_GET["version"], $_GET['json_data']));
	}
	else if( !empty($_GET["all"]))
	{
		echo json_encode($db->GetAll($from, $to));
	}
	else if( !empty($_GET["histogram"]))
	{
		echo json_encode($db->GetHistogram($_GET["histogram"], $from, $to));
	}
	else if( !empty($_GET["latest"]))
	{
		echo json_encode($db->GetLatest());
	}
}
?>
