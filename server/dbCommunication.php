<?php

require('utils.php');

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

	function InsertSensorStats($version, $data)
	{
		$stmt = $this->dbConnection->prepare( "INSERT INTO sensorStats
        (timestamp, version, json) VALUES (?, ?, ?);");

        $timestamp = date("Y-m-d H:i:s");
		
		$params = [$timestamp, $version, $data];
		$stmt->execute($params);

		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	function InsertStepperStart($version, $count)
	{
		$stmt = $this->dbConnection->prepare( "INSERT INTO stepperStartHist
        (timestamp, version, count) VALUES (?, ?, ?);");

        $timestamp = date("Y-m-d H:i:s");
		
		$params = [$timestamp, $version, $count];
		$stmt->execute($params);

		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	function GetAll($from, $to)
	{
		$stmt = $this->dbConnection->prepare( "SELECT * FROM stepperStartHist WHERE (? OR timestamp > ?) AND (? OR timestamp < ?);");

		$params = [is_null($from), $from, is_null($to), $to];
		$stmt->execute($params);

		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	function GetAllSensors($bucket, $from, $to)
	{
		$stmt = $this->dbConnection->prepare( "SELECT * FROM sensorStats WHERE (? OR timestamp > ?) AND (? OR timestamp < ?);");

		$params = [is_null($from), $from, is_null($to), $to];
		$stmt->execute($params);

		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$resDeserialized = array_map(function ($row) { $row['json'] = json_decode($row['json'], true); return $row; }, $res);

		$resDeserialized = array_map(function ($a) use ($bucket) 
		{ 
			return array(
				't'=>date($bucket, strtotime($a['timestamp'])), 
				'json'=>$a['json']); 
		}, $resDeserialized);

		$collectedPerBucket = array_reduce($resDeserialized, function($carry, $item)
		{
			if(isset($carry[$item['t']]))
			{
				array_push($carry[$item['t']], $item['json']);
			} 
			else 
			{
				$carry[$item['t']] = array($item['json']);
			}
			return $carry;

		}, array());

		$meanPerBucket = array_map(function($timestamp) use ($collectedPerBucket)
		{
			return array(
				'timestamp' => $timestamp,
				'ATP5' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['ATP5']; }), 
				'ATP50' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['ATP50']; }), 
				'ATP95' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['ATP95']; }), 

				'AHP5' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['AHP5']; }), 
				'AHP50' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['AHP50']; }), 
				'AHP95' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['AHP95']; }),

				'AE' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['AE']; }), 

				'DS' => array( 
					array(
						'P5' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['DS'][0]['P5']; }), 
						'P50' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['DS'][0]['P50']; }), 
						'P95' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['DS'][0]['P95']; }),
						'E' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['DS'][0]['E']; })
					),
					array(
						'P5' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['DS'][1]['P5']; }), 
						'P50' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['DS'][1]['P50']; }), 
						'P95' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['DS'][1]['P95']; }),
						'E' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['DS'][1]['E']; })
					),
					array(
						'P5' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['DS'][2]['P5']; }), 
						'P50' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['DS'][2]['P50']; }), 
						'P95' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['DS'][2]['P95']; }),
						'E' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['DS'][2]['E']; })
					),
					array(
						'P5' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['DS'][3]['P5']; }), 
						'P50' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['DS'][3]['P50']; }), 
						'P95' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['DS'][3]['P95']; }),
						'E' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['DS'][3]['E']; })
					),
					array(
						'P5' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['DS'][4]['P5']; }), 
						'P50' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['DS'][4]['P50']; }), 
						'P95' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['DS'][4]['P95']; }),
						'E' => ArrayReduction::Mean($collectedPerBucket[$timestamp], function ($v) { return $v['DS'][4]['E']; })
					)
				)
			);
		}, array_keys($collectedPerBucket));

		return $meanPerBucket;
	}

	function GetHistogram($bucket, $from, $to)
	{
		$all = $this->GetAll($from, $to);
		
		$all = array_map(function ($a) use ($bucket) { return array('t'=>date($bucket, strtotime($a['timestamp'])), 'count'=>$a['count']); }, $all);

		$reduced = array_reduce($all, function ($carry, $item) 
		{
			$carry[$item['t']] = !isset($carry[$item['t']]) ? $item['count'] : $carry[$item['t']] + $item['count'];
			return $carry;
		},
		array());

		return $reduced;
	}

	function GetLatest()
	{
		$stmt = $this->dbConnection->prepare( "SELECT * FROM stepperStartHist	 ORDER BY timestamp DESC LIMIT 1;");

		$stmt->execute();

		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}

	function getSensorHistogram($bucket, $from, $to)
	{
		$all = $this->GetAllSensors($from, $to);
		
		$all = array_map(function ($a) use ($bucket) { return array('t'=>date($bucket, strtotime($a['timestamp'])), 'count'=>$a['count']); }, $all);

		$arr = array();

		$reduced = array_reduce($all, function ($carry, $item) 
		{
			$carry[$item['t']] = !isset($carry[$item['t']]) ? $item['count'] : $carry[$item['t']] + $item['count'];
			return $carry;
		},
		array());

		return $reduced;
	}
}
?>
