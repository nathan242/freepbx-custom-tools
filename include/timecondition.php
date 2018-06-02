<?php
    class timecondition {
        private $db;

        public $id;
        public $prefix;

        public $properties = array();

        function __construct(&$db, $id, $prefix = '') {
            $this->db = $db;
            $this->id = $id;
            $this->prefix = $prefix;

            // Get properties
            $this->db->query('SELECT * FROM `timeconditions` WHERE `timeconditions_id` = '.(int)$id);
            if (isset($this->db->result[0])) {
                $this->properties = $this->db->result[0];
                if (isset($this->properties['displayname'])) { $this->properties['description'] = $this->properties['displayname']; }
            }

            // ID
            $this->properties['id'] = $this->properties['timeconditions_id'];
            $this->properties['type_id'] = 6;

            // Get time group property
            $this->db->query('SELECT `time` FROM `timegroups_details` WHERE `timegroupid` = '.(int)$this->properties['time']);
            if (isset($this->db->result[0])) {
                $this->properties['time_details'] = $this->db->result[0]['time'];
            }
        }

        public function get_dest_obj($type) {
            $destprefix = '';
            if ($type == "true") {
                $desttype = 'truegoto';
                $destprefix = '(TRUE) ';
            } else {
                $desttype = 'falsegoto';
                $destprefix = '(FALSE) ';
            }
            $destdata = preg_split('/,/', $this->properties[$desttype]);
            if (preg_match('/^ivr/', $destdata[0])) {
                $ivr = preg_split('/-/', $destdata[0]);
                return new ivr($this->db, $ivr[1], $destprefix);
            }
            if (preg_match('/^app-announcement/', $destdata[0])) {
                $ann = preg_split('/-/', $destdata[0]);
                return new announcement($this->db, $ann[2], $destprefix);
            }
            switch ($destdata[0]) {
                case 'from-did-direct':
                    return new extension($this->db, $destdata[1], $destprefix);
                case 'ext-queues':
                    return new queue($this->db, $destdata[1], $destprefix);
                case 'ext-group':
                    return new ringgroup($this->db, $destdata[1], $destprefix);
                case 'timeconditions':
                    return new timecondition($this->db, $destdata[1], $destprefix);
                case 'app-blackhole':
                    return new blackhole($this->db, $destdata[1], $destprefix);
                default:
                    return $this->properties[$desttype];
            }
        }

        public function get_dest_objs() {
            return array($this->get_dest_obj(true), $this->get_dest_obj(false));
        }

        public function __tostring() {
            return $this->prefix."TIMECONDITION: ".$this->id;
        }
    }
?>
