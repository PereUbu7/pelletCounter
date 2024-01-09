<?php
require_once('databaseCommuniction.php');

$config = parse_ini_file('appsettings.ini', true);

class autoRepository
{
    private $_connection;

    function __construct()
    {
        $_connection = new DbConnection;

        $filename = $config['databases']['path'];

        $this->_connection->Connect($filename);
    }

    
}
?>