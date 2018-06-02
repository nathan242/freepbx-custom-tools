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

    // DB object
    $db = new mdb('localhost', 'root', '', 'asterisk');

    if (isset($_POST['type']) && isset($_POST['id'])) {
        $type = $_POST['type'];
        $id = $_POST['id'];
    } else if (isset($_GET['type']) && isset($_GET['id'])) {
        $type = $_GET['type'];
        $id = $_GET['id'];
    } else {
        exit('ERROR');
    }

    switch ($type) {
        case 0:
            $obj = new blackhole($db, $id);
            break;
        case 1:
            $obj = new incoming($db, $id);
            break;
        case 2:
            $obj = new extension($db, $id);
            break;
        case 3:
            $obj = new queue($db, $id);
            break;
        case 4:
            $obj = new ringgroup($db, $id);
            break;
        case 5:
            $obj = new ivr($db, $id);
            break;
        case 6:
            $obj = new timecondition($db, $id);
            break;
        case 7:
            $obj = new announcement($db, $id);
            break;
        default:
            exit('UNKNOWN TYPE');
    }

    echo '<pre>'.print_r($obj->properties,1).'</pre>';
    
?>
