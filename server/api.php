<?php
ini_set('display_errors', 1);
require 'databaseCommunication.php';

$settings = parse_ini_file("appsettings.ini", true);

$db = new DatabaseConnection;
$db->Connect($settings["database"]["path"]);

$argumentFound = false;

if( $_SERVER["REQUEST_METHOD"] == "GET" )
{
	if( !empty($_GET["version"]))
	{
		if( !empty($_GET["version"]))
			{
				echo json_encode($db->GetLogsForNode($_GET["node"], $_GET["from"]));
				$argumentFound = true;
			}
	}
}

if(!$argumentFound)
{
	echo "<h1>Error</h1>";
}
?>
