<?php
    class ivr {
        private $db;

        public $id;
        public $prefix;

        public $properties = array();

        function __construct(&$db, $id, $prefix = '') {
            $this->db = $db;
            $this->id = $id;
            $this->prefix = $prefix;

            // Get properties
            $this->db->query('SELECT * FROM `ivr_details` WHERE `id` = '.(int)$id);
            if (isset($this->db->result[0])) {
                $this->properties = $this->db->result[0];
                if (isset($this->properties['name'])) { $this->properties['description_'] = $this->properties['description']; $this->properties['description'] = $this->properties['name']; }
            }

            // ID
            $this->properties['type_id'] = 5;
        }

        public function get_dest_obj($entry, $prefix = '') {
            $destdata = preg_split('/,/', $entry['dest']);
            if (preg_match('/^ivr/', $destdata[0])) {
                $ivr = preg_split('/-/', $destdata[0]);
                return new ivr($this->db, $ivr[1], $prefix);
            }
            if (preg_match('/^app-announcement/', $destdata[0])) {
                $ann = preg_split('/-/', $destdata[0]);
                return new announcement($this->db, $ann[2], $prefix);
            }
            switch ($destdata[0]) {
                case 'from-did-direct':
                    return new extension($this->db, $destdata[1], $prefix);
                case 'ext-queues':
                    return new queue($this->db, $destdata[1], $prefix);
                case 'ext-group':
                    return new ringgroup($this->db, $destdata[1], $prefix);
                case 'timeconditions':
                    return new timecondition($this->db, $destdata[1], $prefix);
                case 'app-blackhole':
                    return new blackhole($this->db, $destdata[1], $prefix);
                default:
                    return implode(',', $destdata);
            }
        }

        public function get_dest_objs() {
            $this->db->query('SELECT * FROM `ivr_entries` WHERE `ivr_id` = '.(int)$this->id);
            $ret = array();
            foreach ($this->db->result as $r) {
                $ret[] = $this->get_dest_obj($r, '(OPTION: '.$r['selection'].') ');
            }
            return $ret;
        }

        public function __toString() {
            return $this->prefix."IVR: ".$this->id;
        }
    }
?>
