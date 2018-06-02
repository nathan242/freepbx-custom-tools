<?php
    class incoming {
        private $db;

        public $ext;
        public $prefix;

        public $properties = array();

        function __construct(&$db, $ext, $prefix = '') {
            $this->db = $db;
            $this->ext = $ext;
            $this->prefix = $prefix;

            // Get properties
            $this->db->query('SELECT * FROM `incoming` WHERE `extension` = '.(int)$ext);
            if (isset($this->db->result[0])) {
                $this->properties = $this->db->result[0];
            }

            // ID
            $this->properties['original_id'] = $this->properties['id'];
            $this->properties['id'] = $this->properties['extension'];
            $this->properties['type_id'] = 1;
        }

        public function get_dest_obj() {
            $destdata = preg_split('/,/', $this->properties['destination']);
            if (preg_match('/^ivr/', $destdata[0])) {
                $ivr = preg_split('/-/', $destdata[0]);
                return new ivr($this->db, $ivr[1]);
            }
            if (preg_match('/^app-announcement/', $destdata[0])) {
                $ann = preg_split('/-/', $destdata[0]);
                return new announcement($this->db, $ann[2]);
            }
            switch ($destdata[0]) {
                case 'from-did-direct':
                    return new extension($this->db, $destdata[1]);
                case 'ext-queues':
                    return new queue($this->db, $destdata[1]);
                case 'ext-group':
                    return new ringgroup($this->db, $destdata[1]);
                case 'timeconditions':
                    return new timecondition($this->db, $destdata[1]);
                case 'app-blackhole':
                    return new blackhole($this->db, $destdata[1]);
                default:
                    return $this->properties['destination'];
            }
        }

        public function get_dest_objs() {
            return array($this->get_dest_obj());
        }

        public function __toString() {
            return $this->prefix."INCOMING: ".$this->ext;
        }
    }
?>
