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
        return $this->_connection->getHistogram($bucket);
    }

    
}
?>