<?php

	/*

		Utvecklat av Fredrik Skoglind 2010
		En wrapper för databaskopplingar i PHP, 
		för att hantera både MySQL- och MSAccess-databaser.

		Database
		* Skapar ett databasobjekt att jobba mot
			Database( hostname[host/accessfile], database, username, password, engine[MYSQLI/ODBC] )
			- get()
				Engine
				Connection
				Connection_ErrNo
				Connection_ErrMsg
			- rows ( query )
			- row ( query )
			- quote ( string )
			- query ( query )
			- lasterror ( )
			- lastid ( )
			
	*/

	class Database {
		private $_db;
		private $_engine;
		private $_connection;
		private $_connection_errno;
		private $_connection_errmsg;

		function __construct( $dataArray ) {
			switch(trim(strtolower($dataArray['engine']), "\t\n\r\0\x0B")) {
				case 'access':
				case 'msaccess':
				case 'ms access':
				case 'odbc':
					$this->_engine = 'ODBC';
					break;
				default:
					$this->_engine = 'MYSQLI';
			}

			switch($this->_engine) {
				case 'MYSQLI':
					@$this->_db = new mysqli( $data_hostname, $data_username, $data_password, $data_database );
					if($this->_db->connect_errno) { 
						$this->_connection = 0; 
						$this->_connection_errno = $this->_db->connect_errno; 
						$this->_connection_errmsg = $this->_db->connect_error; 
					} else { 
						$this->_db->set_charset('utf8'); 
						//$this->query(''); echo 'CHARSET:' . $this->_db->character_set_name(); 
						$this->_connection = 1; 
					}
					break;
				case 'ODBC':
					@$this->_db = odbc_connect('Driver={Microsoft Access Driver (*.mdb)};Dbq=' . $data_hostname, $data_username, $data_password);
					if(odbc_error()) {
						$this->_connection = 0; 
						$this->_connection_errno = odbc_error(); 
						$this->_connection_errmsg = odbc_errormsg(); 
					} else { 
						$this->_connection = 1; 
					}
			}
		}

		function __destruct() {
			switch($this->_engine) {
				case 'MYSQLI':
					if($this->_connection) { $this->_db->close(); }
					break;
				case 'ODBC':
					if($this->_connection) { odbc_close($this->_db); }
			}
		}

		/* # Funktioner # */
			// Hämta flera rader ur databasen
			public function rows( $query = '', $limitstart = 0, $limit = 250) {
				$rows = array();
				if($this->_connection) {
					switch($this->_engine) {
					case 'MYSQLI':
						$result = $this->_db->query( $query . ' LIMIT ' . $limitstart . ', ' . $limit );
						if($result) {
							$i = 0;
							while($row = $result->fetch_array(MYSQLI_ASSOC)) {
								$i++; if($i > $limit) { break; }
								$rows[] = $row;
							}
							$result->free();
						}
						break;
					case 'ODBC':
						@$result = odbc_exec( $this->_db, utf8_decode($query) );

						if(odbc_error()) { 
							die('<div class="db_error"><strong> Anslutningsfel: ' . odbc_error() . '</strong><br> ' . utf8_encode(odbc_errormsg( $this->_db )) . '</div>');
						}

						if($result) {
							$limitstart++;
							$limitEnd = $limitstart + $limit;

							for($i = $limitstart; $i < $limitEnd; $i++) {
								$row = utf8_class(odbc_fetch_object($result, $i));
								if($row) { $rows[] = $row; }
							}

							odbc_free_result($result);
						}
					}
				} else { return false; }

				return $rows;
			}

			// Hämta en rad ur databasen
			public function row( $query ) {
				$row = array();

				if($this->_connection) {
					switch($this->_engine) {
					case 'MYSQLI':
						$result = $this->_db->query( $query );
						if($result) {
							$row = $result->fetch_array(MYSQLI_ASSOC);
							$result->free();
						}
						break;
					case 'ODBC':
						@$result = odbc_exec( $this->_db, utf8_decode($query) );

						if(odbc_error()) { 
							die('<div class="db_error"><strong> Anslutningsfel: ' . odbc_error() . '</strong><br> ' . utf8_encode(odbc_errormsg( $this->_db )) . '</div>');
						}

						if($result) {
							$row = utf8_class(odbc_fetch_object($result));
							odbc_free_result($result);
						}

					}
				} else { return false; }

				return $row;
			}

			// Citera en sträng så att den blir säkrare att posta i en SQL-fråga
			public function quote( $string ) {
				return $this->_db->real_escape_string( $string );
			}

			// Skicka in en SQL-fråga till databasen
			public function query( $query ) {
				if($this->_connection) {
					switch($this->_engine) {
					case 'MYSQLI':
						if($this->_db->query( $query )) {
							return true;
						} else { return false; }
						break;
					case 'ODBC':
						if(odbc_exec( $this->_db, utf8_decode($query) )) {
							return true;
						} else { return false; }
					}
				} else { return false; }
			}

			// Få fram det senaste felet
			public function lasterror() {
				return $this->_db->error;
			}

			// Få fram det senaste IDt
			public function lastid() {
				return $this->_db->insert_id;
			}
		/* # - - - - # */

		/* # Variabler # */
			private function getEngine() {
				return $this->_engine;
			}

			public function getConnection() {
				return $this->_connection;
			}

			private function getConnection_ErrNo() {
				return $this->_connection_errno;
			}

			private function getConnection_ErrMsg() {
				return $this->_connection_errmsg;
			}
		/* # - - - - # */

		/* # GET / SET # */
			public function get($varName) {
				if (method_exists($this, $MethodName = 'get' . $varName)) {
					return $this->$MethodName();
				} else {
					trigger_error($varName . ' is not avaliable .',E_USER_ERROR);
				}
			}

			public function set($varName, $value) {
				if (method_exists($this, $MethodName = 'set' . $varName)) {
					return $this->$MethodName($value);
				} else {
					trigger_error($varName . ' is not avaliable .',E_USER_ERROR);
				}
			}
		/* # - - - - # */


	}

?>