<?php
    class announcement {
        private $db;

        public $id;
        public $prefix;

        public $properties = array();

        function __construct(&$db, $id, $prefix = '') {
            $this->db = $db;
            $this->id = $id;
            $this->prefix = $prefix;

            // Get properties
            $this->db->query('SELECT * FROM `announcement` WHERE `announcement_id` = '.(int)$id);
            if (isset($this->db->result[0])) {
                $this->properties = $this->db->result[0];
            }
            // ID
            $this->properties['id'] = $this->properties['announcement_id'];
            $this->properties['type_id'] = 7;
        }

        public function get_dest_obj() {
            $destdata = preg_split('/,/', $this->properties['post_dest']);
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
                    return implode(',', $destdata);
            }
        }

        public function get_dest_objs() {
            return array($this->get_dest_obj());
        }

        public function __toString() {
            return $this->prefix."ANNOUNCEMENT: ".$this->id;
        }
    }
?>
