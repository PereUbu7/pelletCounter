<?php
require_once('dbCommunication.php');

class autoRepository
{
    private $_connection;

    function __construct($path)
    {
        $_connection = new DbConnection;

        $this->_connection->Connect($path);
    }

    
}
?>