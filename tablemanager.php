<?php

    /**
     * Class TableManager
     * @author Fredrik Skoglind, 2020
     */
    class TableManager {
        private $db;

    	public function __construct( $db ) {
            $this->db = $db;
    	}

        /**
         * getTables
         * @return array    List with all table names
         */
        public function getTables() : array {
            $result = $this->db->getResultAsArrayNoLimit( 'SHOW TABLES FROM ' . $this->db->getDatabaseName(), 0, 0, false );
            foreach( $result as $table ) { $returnArray[] = $table['Tables_in_' . $this->db->getDatabaseName()]; } 
            return isset($returnArray) ? $returnArray : array();
        }
        
        /**
         * getColumns
         * @param string    Table name
         * @return array    List with all column names
         */
    	public function getColumns( string $tableName ) : array {
            $result = $this->db->getResultAsArrayNoLimit( 'SHOW COLUMNS FROM ' . $this->db->escapeString($tableName), 0, 0, false );
            return isset($result) ? $result : array();
        }

        /**
         * tableExists
         * @param string    Table name
         * @return bool     If table exists
         */
    	public function tableExists( string $tableName ) : bool {
            $tables = $this->getTables();
            if( in_array( $tableName, $tables ) ) { return true; }
            return false;
        }

         /**
         * columnExists
         * @param string    Table name
         * @param string    Column name
         * @return bool     If table exists
         */
    	public function columnExists( string $tableName, string $columnName ) : bool {
            if( $this->tableExists( $tableName ) ) {
                $columns = $this->getColumns( $tableName );
                foreach( $columns as $column ) {
                    if( $column['Field'] == $columnName ) { return true; }
                } return false;
            } return false;
        }
        
    }

?>