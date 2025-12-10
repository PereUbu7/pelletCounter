<?php
    require_once('../../logAnything/databaseCommunication.php');

    class manualRepository
    {
        private $_connection;

        function __construct($path)
        {
            $this->_connection = new DatabaseConnection;

            $this->_connection->connect($path);
        }

        function getValues($autoValues)
        {
            $times = array_keys($autoValues);

            $start = min($times);
            $end = max($times);
            return $this->_connection->GetValuesByKeyAndRange('pellets', $start, $end);
        }
    }
?>