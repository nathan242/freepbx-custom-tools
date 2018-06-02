<?php
    require_once "../include/mdb.php";
    require_once "../include/extension.php";

    // DB object
    $db = new mdb('localhost', 'root', '', 'asterisk');

    // Get extensions
    $db->query('SELECT `id` FROM `devices`');
    if (!isset($db->result[0])) { exit('No Extensions!'); }

    $result = $db->result;
?>
<!doctype html>
    <head>
        <title>Extension Config</title>
    </head>
    <body>
        <h1>Extension Config</h1>
<?php
    foreach ($result as $r) {
        echo '<hr>';
        $ext = new extension($db, $r['id']);
        echo '<h2>Extension: '.$r['id'].' ['.$ext->properties['description'].']</h2>';

        echo '<div style="overflow: hidden;">';
        echo '<div style="float: left; margin: 10px;">';
        echo '<h3>Queues</h3>';
        echo '<table border="1">';
        foreach ($ext->queues as $q) {
            echo '<tr><td>'.$q.'</td>';
            if ($db->query('SELECT `descr` FROM `queues_config` WHERE `extension`='.(int)$q) && isset($db->result[0])) {
                $desc = $db->result[0]['descr'];
            } else {
                $desc = "UNKNOWN";
            }
            echo '<td>'.$desc.'</td></tr>';
        }
        echo '</table>';
        echo '</div>';

        echo '<div style="float: left; margin: 10px;">';
        echo '<h3>Ring Groups</h3>';
        echo '<table border="1">';
        foreach ($ext->ringgroups as $rg) {
            echo '<tr><td>'.$rg.'</td>';
            if ($db->query('SELECT `description` FROM `ringgroups` WHERE `grpnum`='.(int)$rg) && isset($db->result[0])) {
                $desc = $db->result[0]['description'];
            } else {
                $desc = "UNKNOWN";
            }
            echo '<td>'.$desc.'</td></tr>';
        }
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }
?>
    </body>
</html>
