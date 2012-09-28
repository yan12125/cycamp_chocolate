<?php
require_once './data.php';
function start_db()
{
    $password = get_param('password3');
    $dsn = 'mysql:host=localhost;port=3306;dbname=cloudhome';
    $user = get_param('username3');
    $dbh = new PDO($dsn, $user, $password);
    $dbh->query('set names utf8');
    return $dbh;
}
?>
