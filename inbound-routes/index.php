<?php
    include '../include/mdb.php';
    include '../include/incoming.php';
    include '../include/queue.php';
    include '../include/ringgroup.php';
    include '../include/extension.php';
    include '../include/timecondition.php';
    include '../include/ivr.php';
    include '../include/announcement.php';
    include '../include/blackhole.php';

    //ini_set('memory_limit', '512M');

    // DB object
    $db = new mdb('localhost', 'root', '', 'asterisk');

    // Get inbound numbers
    $db->query('SELECT `extension` FROM `incoming`');
    if (!isset($db->result[0])) { exit('No Inbound Numbers!'); }

    $result = $db->result;

    $data = array();
    $properties = array();

    function add_element($objs) {
        global $db;
        global $properties;
        $ret = array();
        foreach ($objs as $obj) {
            // Endpoints
            if (!is_object($obj)) {
                $ret[(string)$obj] = '';
                continue;
            }
            if (isset($obj->extensions)) {
                //$ret[(string)$obj] = $obj->extensions;
                $arr = array();
                foreach ($obj->extensions as $e) {
                    $ext = new extension($db, $e);
                    $arr['EXTENSION: '.$e.' ['.$ext->properties['description'].']'] = '';
                    if (!array_key_exists('EXTENSION: '.$e.' ['.$ext->properties['description'].']', $properties)) { $properties['EXTENSION: '.$e.' ['.$ext->properties['description'].']'] = $ext->properties; }
                }
                $ret[(string)$obj.' ['.$obj->properties['description'].']'] = $arr;
                if (!array_key_exists((string)$obj.' ['.$obj->properties['description'].']', $properties)) { $properties[(string)$obj.' ['.$obj->properties['description'].']'] = $obj->properties; }
                continue;
            }
            if (!method_exists($obj, 'get_dest_objs')) {
                $ret[(string)$obj.' ['.$obj->properties['description'].']'] = '';
                if (!array_key_exists((string)$obj.' ['.$obj->properties['description'].']', $properties)) { $properties[(string)$obj.' ['.$obj->properties['description'].']'] = $obj->properties; }
                continue;
            }
            // Branches
            $dests = $obj->get_dest_objs();
            $ret[(string)$obj.' ['.$obj->properties['description'].']'] = add_element($dests);
            if (!array_key_exists((string)$obj.' ['.$obj->properties['description'].']', $properties)) { $properties[(string)$obj.' ['.$obj->properties['description'].']'] = $obj->properties; }
        }
        return $ret;
    }

    foreach ($result as $r) {
        $inc = new incoming($db, $r['extension']);
        $incstring = $r['extension'].' ['.$inc->properties['description'].']'.(($inc->properties['grppre'] != "") ? ' [Prefix: '.$inc->properties['grppre'].']' : '');
        $objs = $inc->get_dest_objs();
        $data[$incstring] = add_element($objs);
        if (!array_key_exists($incstring, $properties)) { $properties[$incstring] = $inc->properties; }
    }

    //exit('<pre>'.print_r($data,1).'</pre>');
    //exit('<pre>'.print_r($properties,1).'</pre>');
?>
<!doctype html>
    <head>
        <title>Inbound Routes</title>
    </head>
    <body style="font-size: smaller;">
        <h1>Inbound Routes</h1>
<?php

    $x = $y = 0;
    $table = array();

    function add_data($data) {
        global $x;
        global $y;
        global $table;

        $x++;
        $table[$x.':'.$y] = '&gt';
        $x++;

        foreach ($data as $k => $v) {
            if (!isset($table[($x-1).':'.$y])) { $table[($x-1).':'.$y] = '&gt'; }
	    if (!isset($table[($x-2).':'.$y])) {
                $table[($x-2).':'.$y] = '-';
                if ($table[($x-2).':'.($y-1)] == '') {
                    $z = $y;
                    while ($table[($x-2).':'.--$z] == '') { $table[($x-2).':'.$z] = '|'; }
                }
            }
            //if ($table[($x-3).':'.$y] == '|') {
            //    $z = $y;
            //    while ($table[($x-3).':'.--$z] == '') { $table[($x-3).':'.$z] = '|'; }
            //}

            $table[$x.':'.$y] = $k;
            if (is_array($v)) { add_data($v); }
            $y++;
        }

        $x--;
        $x--;
    }

    foreach ($data as $k => $v) {
        $y++; $x = 0;
        $table[$x.':'.$y] = $k;
        add_data($v);
    }

    //exit('<pre>'.print_r($table,1).'</pre>');

    // Make table
    $x = $y = 0;
    $mx = $my = 0;
    foreach (array_keys($table) as $k) {
        $pos = preg_split('/:/', $k);
        $mx = ($pos[0] > $mx) ? $pos[0] : $mx;
        $my = ($pos[1] > $my) ? $pos[1] : $my;
    }

    echo '<table border="0">';
    for ($y = 0; $y != $my+1; $y++) {
        echo '<tr>';
        for ($x = 0; $x != $mx+1; $x++) {
            if (array_key_exists($x.':'.$y, $table) && !preg_match('/^(-+|(&gt)+|\|+)$/', $table[$x.':'.$y])) { echo '<td style="border: black; border-style: solid;">'; } else { echo '<td>'; }
            if (array_key_exists($x.':'.$y, $table) && preg_match('/^-$/', $table[$x.':'.$y])) { echo '<img style="width: 100%;" src="right-join.png">'; }
            elseif (array_key_exists($x.':'.$y, $table) && preg_match('/^\|$/', $table[$x.':'.$y])) { echo '<img style="width: 100%;" src="line-down.png">'; }
            else {
                if (array_key_exists($x.':'.$y, $table)) {
                    if (array_key_exists($table[$x.':'.$y], $properties)) {
                        $proplist = '';
                        foreach ($properties[$table[$x.':'.$y]] as $k => $v) {
                            $proplist .= ($proplist != '') ? "\n" : "";
                            $proplist .= '['.$k.'] = '.$v;
                        }
                        $prop_type_id = $properties[$table[$x.':'.$y]]['type_id'];
                        $prop_id = $properties[$table[$x.':'.$y]]['id'];
                        //echo '<a href="javascript:window.open(\'configure.php?type='.$prop_type_id.'&id='.$prop_id.'\', \'DETAILS\', \'width=800,height=800\')" title="'.$proplist.'">'.$table[$x.':'.$y].'</a>';
                        echo '<a title="'.$proplist.'">'.$table[$x.':'.$y].'</a>';
                    } else {
                        echo $table[$x.':'.$y];
                    }
                } else {
                    echo '&nbsp';
                }
            }
            echo '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';

/*
    function display_element($data) {
        echo '<div style="float: left; margin: 10px;">';
        echo '<table border="1">';
        foreach ($data as $k => $v) {
            echo '<tr><td>'.$k.'</td></tr>';
            $count = count($v);
            for ($i = 1; $i < $count; $i++) {
                echo '<tr><td>&nbsp</td></tr>';
            }
        }
        echo '</table>';
        echo '</div>';

        echo '<div style="float: left; margin: 10px;">';
        echo '<table border="0">';
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                echo '<tr><td>-&gt</td></tr>';
                $count = count($v);
                for ($i = 1; $i < $count; $i++) {
                    echo '<tr><td>&nbsp</td></tr>';
                }
            }
        }
        echo '</table>';
        echo '</div>';

        foreach ($data as $k => $v) {
            display_element($v);
        }
    }

    foreach ($data as $k => $v) {
        echo '<hr>';
        echo '<h2>Inbound Route: '.$k.'</h2>';

        echo '<div style="overflow: hidden">';

        echo '<div style="float: left; margin: 10px;">';
        echo '<table border="1"><tr><td>'.$k.'</td></tr></table>';
        echo '</div>';

        echo '<div style="float: left; margin: 10px;">';
        echo '<table border="0"><tr><td>-&gt</td></tr></table>';
        echo '</div>';

        display_element($v);

        echo '</div>';
    }
*/
?>
    </body>
</html>
