<?php
require_once './data.php';
function start_db()
{
    $password = 'qa9eWApHrLJyWZCf';
    $dsn = 'mysql:host=localhost;port=3306;dbname=cloudhome';
    $user = get_param('username');
    $dbh = new PDO($dsn, $user, $password);
    $dbh->query('set names utf8');
    return $dbh;
}
?>
