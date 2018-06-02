<?php
    class ringgroup {
        private $db;

        public $id;
        public $prefix;

        public $properties = array();
        public $extensions = array();

        function __construct(&$db, $id, $prefix = '') {
            $this->db = $db;
            $this->id = $id;
            $this->prefix = $prefix;

            // Get properties
            $this->db->query('SELECT * FROM `ringgroups` WHERE `grpnum` = '.(int)$id);
            if (isset($this->db->result[0])) {
                $this->properties = $this->db->result[0];
            }

            // ID
            $this->properties['id'] = $this->properties['grpnum'];
            $this->properties['type_id'] = 4;

            // Get destinations
            $this->extensions = preg_split('/-/', $this->properties['grplist']);
        }

        public function __toString() {
            return $this->prefix."RINGGROUP: ".$this->id;
        }
    }
?>
