<?php
    class blackhole {
        private $db;

        public $type;
        public $prefix;

        public $properties = array();

        function __construct(&$db, $type, $prefix = '') {
            $this->db = $db;
            $this->type = $type;
            $this->prefix = $prefix;

            // Get properties
            switch ($type) {
                case 'hangup':
                    $this->properties['description'] = 'HANGUP';
                    break;
                case 'busy':
                    $this->properties['description'] = 'BUSY';
                    break;
                default:
                    $this->properties['description'] = 'UNKNOWN ('.$type.')';
            }

            // ID
            $this->properties['id'] = 0;
            $this->properties['type_id'] = 0;
        }

        public function __toString() {
            return $this->prefix."BLACKHOLE ";
        }
    }
?>
