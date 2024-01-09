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

    function getValues()
    {
        return $this->_connection->getHistogram('Y-m-d H:i');
    }

    
}
?>