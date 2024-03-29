<?php
require_once('dbCommunication.php');

class autoRepository
{
    private $_connection;

    function __construct($path)
    {
        $this->_connection = new DbConnection;

        $this->_connection->Connect($path);
    }

    function getValues($bucket, $from = null, $to = null)
    {
        $histogram = $this->_connection->getHistogram($bucket, $from, $to);
        
        # Sort by keys (buckets)
        ksort($histogram);

        return $histogram;
    }

    function GetAllSensors($bucket, $from = null, $to = null)
    {
        return $this->_connection->GetAllSensors($bucket, $from, $to);
    }

    function getSensorValues($bucket, $from = null, $to = null)
    {
        $histogram = $this->_connection->getSensorHistogram($bucket, $from, $to);

        # Sort by keys (buckets)
        ksort($histogram);

        return $histogram;
    }
}
?>