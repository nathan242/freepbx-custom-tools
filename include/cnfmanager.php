<?php
    class cnfManager {

        private $db;
        private $cnfdir;

        public $data = array();

        function __construct(&$db, $dir) {
            $this->db = $db;
            $this->cnfdir = $dir;
        }

        public function load($mac = false) {
            $this->data = array();
            if (!is_dir($this->cnfdir)) { return false; }

            if ($mac === false) {
                $contents = scandir($this->cnfdir);
                $files = array();

                if (count($contents) == 0) { return false; }

                foreach ($contents as $c) {
                    if (preg_match('/^SIP.{12}\.cnf$/', $c) && is_file($this->cnfdir.'/'.$c)) {
                        $files[] = $c;
                    }
                }
            } elseif (strlen($mac) != 12) {
                return false;
            } else {
                $files = array('SIP'.$mac.'.cnf');
            }
            //exit('<pre>'.print_r($files,1).'</pre>');

            foreach ($files as $f) {
                $cnfdata = array(
                    'phone_label'=>'',
                    'line1_name'=>'',
                    'line1_shortname'=>'',
                    'line1_displayname'=>'',
                    'line1_password'=>'',
                    'line1_authname'=>''
                );

                $cnffile = file_get_contents($this->cnfdir.'/'.$f);
                $lines = preg_split('/\n/', $cnffile);

                foreach ($lines as $l) {
                    $var = preg_split('/:\ +/', $l);
                    if (!isset($var[1])) { $var[1] = ''; }
                    $var[1] = preg_replace('/"/', '', $var[1]);
                    switch ($var[0]) {
                        case "phone_label": $cnfdata['phone_label'] = $var[1]; break;
                        case "line1_name": $cnfdata['line1_name'] = $var[1]; break;
                        case "line1_shortname": $cnfdata['line1_shortname'] = $var[1]; break;
                        case "line1_displayname": $cnfdata['line1_displayname'] = $var[1]; break;
                        case "line1_password": $cnfdata['line1_password'] = $var[1]; break;
                        case "line1_authname": $cnfdata['line1_authname'] = $var[1]; break;
                    }
                }

                $this->data[substr($f, 3, 12)] = $cnfdata;
            }

            return true;
        }

        public function add($mac, $ext) {
            if (strlen($mac) != 12) { return false; }
            $mac = strtoupper($mac);
            if (file_exists($this->cnfdir.'/SIP'.$mac.'.cnf' )) { return false; }

            $phone_label = '';
            $name = $ext;
            $shortname = $ext;
            $displayname = $ext;
            $authname = $ext;
            $password = '';

            if (!$this->db->prepared_query('SELECT `description` FROM `devices` WHERE `id`=?', array('i'), array($ext))) { return false; }
            if (!isset($this->db->result[0])) { return false; }
            $phone_label = $this->db->result[0]['description'];

            if (!$this->db->prepared_query('SELECT * FROM `sip` WHERE `id`=?', array('i'), array($ext))) { return false; }
            if (!isset($this->db->result[0])) { return false; }
            foreach ($this->db->result as $r) {
                if ($r['keyword'] == 'secret') {
                    $password = $r['data'];
                }
            }

            $output = "phone_label: \"".$phone_label."\"\n";
            $output .= "line1_name: \"".$name."\"\n";
            $output .= "line1_shortname: \"".$shortname."\"\n";
            $output .= "line1_displayname: \"".$displayname."\"\n";
            $output .= "line1_password: \"".$password."\"\n";
            $output .= "line1_authname: \"".$authname."\"\n";

            if (file_put_contents($this->cnfdir.'/SIP'.$mac.'.cnf', $output)) {
                return true;
            } else {
                return false;
            }
        }

        public function delete($mac) {
            $mac = strtoupper($mac);
            if (unlink($this->cnfdir.'/SIP'.$mac.'.cnf')) {
                return true;
            } else {
                return false;
            }
        }

        public function edit($mac, $ext) {
            if (!$this->delete($mac)) { return false; }
            if ($this->add($mac, $ext)) {
                return true;
            } else {
                return false;
            }
        }

    }
?>
