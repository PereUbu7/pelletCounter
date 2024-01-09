<?php
    require_once('../logAnything/databaseCommunication.php');

    $config = parse_ini_file('appsettings.ini', true);

    class manualRepository
    {
        private $_connection;

        function __construct()
        {
            $this->_connection = new DatabaseConnection;

            $filename = $config['databases']['manualPelletPath'];

            $this->_connection->connect($filename);
        }

        function getValues()
        {
            return $this->_connection->GetValuesByKey('pellet');
        }
    }
?>