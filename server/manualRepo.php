<?php
    require_once('../../FuelLogger/databaseCommunication.php');

    class manualRepository
    {
        private $_connection;

        function __construct($path)
        {
            $this->_connection = new DatabaseConnection;

            $this->_connection->connect($path);
        }

        function getValues()
        {
            return $this->_connection->GetValuesByKey('pellet');
        }
    }
?>