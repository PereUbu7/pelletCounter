<?php
class DatabaseConnection
{
	private $dbConnection;
	
	function Connect($fileName)
	{
		// Create a DSN for the database using its filename
		$dsn = "sqlite:$fileName";
			
		// Open the database file and catch the exception if it fails.
		try 
		{
			$this->dbConnection = new PDO($dsn);
			$this->dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
			$stmt = $this->dbConnection->prepare( "PRAGMA foreign_keys = ON;" );
			$stmt->execute();
		} 
		catch (PDOException $e) 
		{
			echo "Failed to connect to the database using DSN:<br>$dsn<br>";
			throw $e;
		}
	}

	function InsertStepperStart($version)
	{
		$stmt = $this->dbConnection->prepare( "INSERT INTO stepperStart
        (timestamp, version) VALUES (?, ?);");

        $timestamp = date("Y-m-d H:i:s");
		
		$params = [$timestamp, $version];
		$stmt->execute($params);

		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	function GetAll()
	{
		$stmt = $this->dbConnection->prepare( "SELECT * FROM stepperStart;");

		$stmt->execute();

		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}
}
?>
