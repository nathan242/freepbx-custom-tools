<?php
    require_once "../include/mdb.php";
    require_once "../include/cnfmanager.php";

    // DB object
    $db = new mdb('localhost', 'root', '', 'asterisk');

    // Config file class
    $cnf = new cnfManager($db, '/tftpboot');

    // Get list of extensions with TFTP config
    $cnf->load();
    $confs = array();
    foreach ($cnf->data as $d) {
        $confs[] = $d['line1_name'];
    }

    // Get extension list
    $ext = array();
    $db->query('SELECT `id`, `tech`, `description` FROM `devices` ORDER BY `id` ASC');
    foreach ($db->result as $r) {
        $r['type'] = ($r['tech'] == 'sip') ? 'Internal Extension' : 'Forward External Number';
        $r['tftpconf'] = (in_array($r['id'], $confs)) ? 'Yes': 'No';
        unset($r['tech']);
        $ext[] = $r;
    }

    // CSV export
    if (isset($_GET['csv'])) {
        $outfile = fopen('/tmp/extension_list.csv', "w+");
        fputcsv($outfile, array('Ext', 'Description'));
        foreach ($ext as $e) {
            unset($e['type'], $e['tftpconf']);
            fputcsv($outfile, $e);
        }
        fclose($outfile);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="extension_list.csv"');
        header('Cache-Control: max-age=0');
        readfile('/tmp/extension_list.csv');
        exit;
    }

?>
<!doctype html>
    <head>
        <title>Extension List</title>
    </head>
    <body>
        <h1>Extension List</h1>
        <p><a href="?csv=1">DOWNLOAD CSV</a></p>
        <table border="1">
            <tr>
                <th>Ext</th>
                <th>Description</th>
                <th>Type</th>
                <th>TFTP Config</th>
            </tr>

<?php
    foreach ($ext as $e) {
        echo '<tr>';
        echo '<td>'.$e['id'].'</td>';
        echo '<td>'.$e['description'].'</td>';
        echo '<td>'.$e['type'].'</td>';
        echo '<td>'.$e['tftpconf'].'</td>';
        echo '</tr>';
    }
?>

        </table>
    </body>
</html>
