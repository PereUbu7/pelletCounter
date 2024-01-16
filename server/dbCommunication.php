<?php
class DbConnection
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

	function GetHistogram($bucket)
	{
		$all = $this->GetAll();

		$all = array_map(function ($a) use ($bucket) { return date($bucket, strtotime($a['timestamp'])); }, $all);

		$arr = array();

		$reduced = array_reduce($all, function ($carry, $item) 
		{
			$carry[$item] = !isset($carry[$item]) ? 1 : $carry[$item] + 1;
			return $carry;
		},
		array());

		return $reduced;
	}

	function GetLatest()
	{
		$stmt = $this->dbConnection->prepare( "SELECT * FROM stepperStart ORDER BY timestamp DESC LIMIT 1;");

		$stmt->execute();

		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}
}
?>
