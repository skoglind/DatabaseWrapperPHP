<?php

    /**
     * Class HTTPHeaderHandler
     * @author Fredrik Skoglind, 2020
     */
    class HTTPHeaderHandler {
        protected $url;
        protected $header;
        protected $responseCodes;

        public function __construct( string $url ) {
            $this->url = $url;
            $this->header = get_headers( $url, 1 );

            $this->parseHeaderData();
        }

        /**
         * Initializes all internal parsers if header is valid
         */
        private function parseHeaderData() {
            if( is_array($this->header) ) {
                $this->parseResponseCodes();
            } else {
                throw new Exception('Invalid header data');
            }
        }

        /**
         * Parses out all the response codes from given header
         */
        private function parseResponseCodes() {
            foreach($this->header as $k => $v) {
                if( is_numeric($k) ) { 
                    preg_match_all( "/HTTP\/\d\.\d\s(\d{3})\s.*/", $v, $matches, PREG_SET_ORDER );
                    if( is_array($matches[0]) && count($matches[0]) > 1 ) {
                        $this->responseCodes[$k] = (int)$matches[0][1];
                    } else { $this->responseCodes[$k] = 0; }
                }
            }
        }

        /**
         * Returns the last response code, in case of multiple 301 moves
         * @return int
         */
        public function getResponseCode() : int {
            $lastKey = array_key_last( $this->responseCodes );
            return $this->responseCodes[$lastKey];
        }

        /**
         * Returns value of given key from the index in the header
         * @param string $key Case-Sensitive
         * @return string
         */
        public function getValue( string $key ) : string {
            if( $this->getResponseCode() == 200 ) {
                $contentData = isset($this->header[$key]) ? $this->header[$key] : null;
                if( is_array($contentData) ) {
                    $lastKey = array_key_last( $this->responseCodes );
                    $contentData = $contentData[$lastKey];
                }

                return trim(explode(';', $contentData)[0]);
            } else {
                throw new Exception('Invalid header data');
            }
        }

        /**
         * Return if the server responded with code 200
         * @return bool
         */
        public function isResponseOK() : bool {
            if( $this->getResponseCode() == 200 ) {
                return true;
            } return false;
        }
    }

?>