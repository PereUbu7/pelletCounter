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

    function getValues($bucket)
    {
        $histogram = $this->_connection->getHistogram($bucket);
        
        # Sort by keys (buckets)
        ksort($histogram);

        return $histogram;
    }

    
}
?>