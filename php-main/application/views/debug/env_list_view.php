<?php
// if (!defined('BASEPATH')) {
//     http_response_code(403);
//     die('Access Forbidden');
// }
?>
<style>
html, body {
    height: 100%;
    width: 100%;
}
body {
    margin: 0;
    background: linear-gradient(0deg,#202223,#2d3436);
    background-repeat: no-repeat;
    font-family: sans-serif;
    font-weight: 100;
    background-attachment: fixed;
    height: 100%;
}

table {
    width: 100%;
    border-collapse: collapse;
    overflow: hidden;
    box-shadow: 0 0 20px rgba(0,0,0,0.5);
    cursor: default;
}
th, td {
    padding: 15px;
    color: #fff;
}
th {
    text-align: left;
    background-color: rgba(0,0,0,0.4);
}
thead th {
    background-color: #55608f;
}
tbody .notfirst:hover {
    background-color: rgba(255,255,255,0.3);
}
tbody .notfirst:active {
    background-color: rgba(255,255,255,0.9);
}
tbody td {
    position: relative;
}
tbody .notfirst:hover:before {
    background-color: rgba(255,255,255,0.2);
}
.search{
    background: linear-gradient(0deg,#202223,#2d3436);
    color: #ddd;
    border: solid 3px rgba(0,0,0,0);
    width: 100%;
    height: 50px;
    padding-left: 10px;
    font-family: sans-serif;
    font-weight: 100;
}
</style>

<input class="search" id="envsearch" type="text" class="form-control" onkeyup="search(this)" placeholder="Search...">

<?php
echo '<div class="container"><table id="envtable" style="width:100%"><tr><th>ENV KEY</th><th>ENV VALUE</th></tr>';

$command = "grep -iIsrPohw --include \*.php \"getenv\(['\\\"].*?['\\\"]\)\" " . $_SERVER['DOCUMENT_ROOT'];
$shell_command = exec($command, $output, $return);

$output = array_unique($output);

foreach ($output as &$env) {
    $env = str_replace(array('"', '\'', '(', ')', 'getenv'), '', $env);
    $value = getenv($env);
    echo '<tr class="notfirst"><td>' . $env . '</td><td onclick="copyval(this)">' . $value . '</td></tr>';
}
echo '</table></div>';
?>

<script>

function search(e) {
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById("envsearch");
    filter = input.value.toUpperCase();
    table = document.getElementById("envtable");
    tr = table.getElementsByTagName("tr");

    for (i = 0; i < tr.length; i++) {
        td = tr[i].getElementsByTagName("td")[0];
            if (td) {
                txtValue = td.textContent || td.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}

function copyval(e) {
    let tmp = document.createElement('textarea');
    tmp.value = e.innerText;
    tmp.setAttribute('readonly', '');
    tmp.style.position = 'absolute';
    tmp.style.left = '-9999px';
    document.body.appendChild(tmp);
    tmp.select();
    document.execCommand('copy');
    document.body.removeChild(tmp);
}
</script>

<?php exit; ?>