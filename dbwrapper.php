<?php

    /**
     * Class DatabaseWrapper
     * @author Fredrik Skoglind, 2020
     */
    class DatabaseWrapper {
        const STANDARD_LIMIT = 500;

        private $db;

        private $db_server_address;
        private $db_username;
        private $db_password;
        private $db_database_name;

        private $lastErrorMessage;
        private $lastError;
        
        public function __construct( string $server_address, 
                                     string $username, 
                                     string $password, 
                                     string $database_name ) {
            $this->db_server_address = $server_address;
            $this->db_username = $username;
            $this->db_password = $password;
            $this->db_database_name = $database_name;
        }

        public function __destruct() { }

        /**
         * Connect      Establish database connection
         * @return bool
         */
        public function connect() : bool {
            if( !$this->isConnected() ) {  
                @$this->db = new mysqli( $this->db_server_address, 
                                            $this->db_username, 
                                            $this->db_password, 
                                            $this->db_database_name );
                if( $this->db->connect_errno ) {
                    $this->lastErrorMessage = $this->db->connect_error;
                    $this->lastError = $this->db->connect_errno;
                } else {
                    $this->db->set_charset('utf8');
                    return true;
                }
            } return false;
        }

        /**
         * Close        Disconnect database connection
         * @return bool
         */
        public function disconnect() : bool {
            if( $this->isConnected() ) { 
                $this->db->close(); 
                unset($this->db); 
                return true;
            } return false;
        }

        /**
         * isConnected  Check if database has active connection
         * @return bool
         */
        public function isConnected() : bool {
            if( !isset($this->db) ) { return false; }
            if( $this->db->connect_errno ) { return false; }
            return true; 
        }

        /**
         * doQuery      Run SQL-Query
         * @return bool
         */
        public function doQuery( string $sql ) : bool {
            if( $this->isConnected() ) {
                if( $this->db->query( $sql ) ) { return true; }
                else {
                    $this->$lastErrorMessage = $this->db->connect_error;
                    $this->$lastError = $this->db->connect_errno; 
                }
            } return false;
        }

        /**
         * getResultsAsArray Get results as associative array, multiple rows
         * @param string    SQL-Query 
         * @param int       Start from row
         * @param int       Max rows
         * @param bool      If limit is active 
         * @return array
         */
        public function getResultsAsArray( string $sql,
                                           int $startAt = 0, 
                                           int $limit = self::STANDARD_LIMIT,
                                           bool $hasLimit = true ) : array {
            if( $hasLimit ) { $sql .= ' LIMIT ' . $startAt . ', ' . $limit; }
            if( $this->isConnected() ) {
                $result = $this->db->query( $sql );
                if( $result ) {
                    $i = 0;
                    while( $data = $result->fetch_array(MYSQLI_ASSOC) ) {
                        if( $hasLimit && $i >= ($startAt + $limit) ) { break; } $i++; // Add limit to loop
                        $resultArray[] = $data;
                    }
                    $result->free();
                    return isset($resultArray) ? $resultArray : array();
                }
            } return array();
        }

        /**
         * getResultAsArray Get result as associative array, only fetch first row
         * @param string    SQL-Query 
         * @return array
         */
        public function getResultAsArray( string $sql ) : array { return $this->getResultsAsArray( $sql, 0, 1 ); }

        /**
         * getResultAsArrayNoLimit Get result as associative array, don't limit rows
         * @param string    SQL-Query
         * @return array
         */
        public function getResultAsArrayNoLimit( string $sql ) : array { return $this->getResultsAsArray( $sql, 0, 0, false ); }

        /** 
         * escapeString Protect string from SQL-injections
         * @param string
         * @return string
         */
        public function escapeString( string $val ) : string { return $this->db->real_escape_string( $val ); }

        /**
         * getLastErrorMessage  Return last error message, if not set return empty string
         * @return string
         */
        public function getLastErrorMessage() : string { return isset($this->getLastErrorMessage) ? $this->getLastErrorMessage : ''; }

        /**
         * getLastError    Return last error no, if not set return 0
         * @return int
         */
        public function getLastError() : int { return isset($this->getLastError) ? intval($this->getLastError) : 0; }

        /**
         * getLastID    Return last inserted ID, if not set return -1
         * @return int
         */
        public function getLastID() : int { return isset($this->db->insert_id) ? intval($this->db->insert_id) : -1; }

        /**
         * getDatabaseName
         * @return string
         */
        public function getDatabaseName() : string { return $this->db_database_name; }
    }

?>