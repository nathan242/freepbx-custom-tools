<?php
    class queue {
        private $db;

        public $id;
        public $prefix;

        public $properties = array();
        public $config_properties = array();
        public $extensions = array();

        function __construct(&$db, $id, $prefix = '') {
            $this->db = $db;
            $this->id = $id;
            $this->prefix = $prefix;

            // Get properties
            $this->db->query('SELECT * FROM `queues_config` WHERE `extension` = '.(int)$id);
            if (isset($this->db->result[0])) {
                $this->properties = $this->db->result[0];
                if (isset($this->properties['descr'])) { $this->properties['description'] = $this->properties['descr']; }
            }

            // ID
            $this->properties['id'] = $this->properties['extension'];
            $this->properties['type_id'] = 3;

            // Get config properties
            $this->db->query('SELECT * FROM `queues_details` WHERE `id` = '.(int)$id);
            if (isset($this->db->result[0])) {
                foreach ($this->db->result as $r) {
                    $keyword = $r['keyword'];
                    $keyword_key = $keyword;
                    $i = 0;
                    while (isset($this->config_properties[$keyword_key])) {
                        $keyword_key = $keyword.++$i;
                    }
                    $this->config_properties[$keyword_key] = $r['data'];
                }
            }

            // Get extensions
            foreach ($this->config_properties as $k => $v) {
                if (preg_match('/^member[0-9]*$/', $k)) {
                    preg_match('/[0-9]+/', $v, $e);
                    $this->extensions[] = $e[0];
                }
            }
        }

        public function __toString() {
            return $this->prefix."QUEUE: ".$this->id;
        }
    }
?>
