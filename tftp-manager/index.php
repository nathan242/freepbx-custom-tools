<?php
    require_once "../include/mdb.php";
    require_once "../include/cnfmanager.php";

    // DB object
    $db = new mdb('localhost', 'root', '', 'asterisk');

    // Config file class
    $cnf = new cnfManager($db, '/tftpboot');

    // Add config
    if (isset($_POST['add']) && isset($_POST['mac']) && isset($_POST['ext'])) {
        if ($cnf->add($_POST['mac'], $_POST['ext'])) {
            header('Location: '.$_SERVER['PHP_SELF']);
            exit();
        } else {
            exit("Failed to add config.\n");
        }
    }

    // Edit config
    if (isset($_POST['edit']) && isset($_POST['mac']) && isset($_POST['ext'])) {
        if (isset($_POST['confirm'])) {
            if ($cnf->edit($_POST['mac'], $_POST['ext'])) {
                header('Location: '.$_SERVER['PHP_SELF']);
                exit();
            } else {
                exit("Failed to edit config.\n");
            }
        }
        echo '<!doctype html><head><title>TFTP SIP Config Manager</title></head>';
        echo '<body>';
        if ($cnf->load($_POST['mac'])) {
            echo '<h1>Modify MAC: '.$_POST['mac'].'</h1>';
            echo '<p>Current EXT: '.$cnf->data[$_POST['mac']]['line1_authname'].'</p>';
            echo '<p>New EXT: '.$_POST['ext'].'</p>';
            echo '<form method="POST">';
            echo '<input type="hidden" name="edit" value="1">';
            echo '<input type="hidden" name="mac" value="'.$_POST['mac'].'">';
            echo '<input type="hidden" name="ext" value="'.$_POST['ext'].'">';
            echo '<input type="hidden" name="confirm" value="1">';
            echo '<input type="submit" value="CONFIRM">';
            echo '</form>';
        } else {
            echo '<p>Cannot get config data.</p>';
        }
        echo '</body></html>';
        exit();
    }

    // Load config files data
    $cnf->load();

    // Get extensions
    if (!$db->query('SELECT `id` FROM `devices`')) { exit("Cannot get extension list.\n"); }
    if (!isset($db->result[0])) { exit("No extensions.\n"); }
    $result = $db->result;

    $extensions = array();
    foreach ($result as $r) {
        $extensions[] = $r['id'];
    }
?>
<!doctype html>
    <head>
        <title>TFTP SIP Config Manager</title>
        <script>
            function edit(mac, ext) {
                if (ext != '<SELECT>' && confirm('Change MAC '+mac+' to EXT '+ext+' ?')) {
                    var form = document.createElement("form");
                    var e1 = document.createElement("input");
                    var e2 = document.createElement("input");
                    var e3 = document.createElement("input");

                    form.method = "POST";

                    e1.name = "edit";
                    e1.value = 1;
                    e1.type = "hidden";
                    e2.name = "mac";
                    e2.value = mac;
                    e2.type = "hidden";
                    e3.name = "ext";
                    e3.value = ext;
                    e3.type = "hidden";

                    form.appendChild(e1);
                    form.appendChild(e2);
                    form.appendChild(e3);
                    document.body.appendChild(form);

                    form.submit();
                }
            }
        </script>
    </head>
    <body>
        <h1>TFTP SIP Config Manager</h1>
        <form method="POST">
            <input type="hidden" name="add" value=1>
            <table border="1">
                <tr>
                    <td>MAC: <input type="text" name="mac"></td>
                    <td>EXT:  <select name="ext"><option>&ltSELECT&gt</option><option><?php echo implode('</option><option>', $extensions); ?></option></select></td>
                    <td><input type="submit" value="ADD"></td>
                </tr>
            </table>
        </form>
        <table border="1">
            <tr>
                <th>MAC Address</th>
                <th>Label</th>
                <th>Name</th>
                <th>Shortname</th>
                <th>Displayname</th>
                <th>Password</th>
                <th>Authname</th>
                <th>Change To...</th>
            </tr>
<?php
    foreach ($cnf->data as $mac=>$row) {
        echo '<tr>';
        echo '<td>'.$mac.'</td><td>'.$row['phone_label'].'</td><td>'.$row['line1_name'].'</td><td>'.$row['line1_shortname'].'</td><td>'.$row['line1_displayname'].'</td><td>'.$row['line1_password'].'</td><td>'.$row['line1_authname'].'</td><td><select onchange="edit(\''.$mac.'\', this.options[this.selectedIndex].value);"><option>&ltSELECT&gt</option><option>'.implode('</option><option>', $extensions).'</option></select></td>';
    }
?>
        </table>
    </body>
</html>
