<?php
require_once './db.php';
require_once './data.php';
require_once './auth.php';
header_auth('cloudhome', 'query');
function utf8_to_escaped($s)
{
    return substr(json_encode($s), 1, -1); // remove leading and trailing quotation mark
}
if(isset($_POST['s']))
{
    $pdo = start_db();
    $stmt = $pdo->prepare('SELECT orderer,receiver,stand,company,ID FROM chocolate WHERE orderer REGEXP ?');
    $stmt->execute(array('\\{"name":"[^"]*'.utf8_to_escaped($_POST['s'])));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $output = array();
    for($i=0;$i<count($results);$i++)
    {
        $item = $results[$i];
        $data = json_decode($item['orderer'], true);
        $campus = campus($item['orderer'], $item['receiver']);
        $ID = $item['stand'].$item['company'].$campus.sprintf("%05d", $item['ID']);
        $data['parsed_id'] = $ID;
        $output[] = $data;
    }
    echo json_encode($output);
}
?>
