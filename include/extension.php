<?php
    class extension {
        private $db;

        public $ext;
        public $prefix;

        public $properties = array();
        public $sip_properties = array();
        public $queues = array();
        public $ringgroups = array();

        function __construct(&$db, $ext, $prefix= '') {
            $this->db = $db;
            $this->ext = $ext;
            $this->prefix = $prefix;

            // Get properties
            $this->db->query('SELECT * FROM `devices` WHERE `id` = '.(int)$ext);
            if (isset($this->db->result[0])) {
                $this->properties = $this->db->result[0];
            }

            // ID
            $this->properties['type_id'] = 2;

            // Get properties from sip table
            $this->db->query('SELECT * FROM `sip` WHERE `id` = '.(int)$ext);
            if (isset($this->db->result[0])) {
                foreach ($this->db->result as $r) {
                    $this->sip_properties[$r['keyword']] = $r['data'];
                }
            }

            // Get queues
            $this->db->query('SELECT `id` FROM `queues_details` WHERE `keyword` = "member" AND `data` REGEXP "[^0-9]'.(int)$ext.'[^0-9]"');
            if (isset($this->db->result[0])) {
                foreach ($this->db->result as $r) {
                    $this->queues[] = $r['id'];
                }
            }

            // Get ring groups
            $this->db->query('SELECT `grpnum` FROM `ringgroups` WHERE `grplist` REGEXP  "([^0-9]|^)'.(int)$ext.'([^0-9]|$)"');
            if (isset($this->db->result[0])) {
                foreach ($this->db->result as $r) {
                    $this->ringgroups[] = $r['grpnum'];
                }
            }
        }

        public function __toString() {
            return $this->prefix."EXTENSION: ".$this->ext;
        }
    }
?>
