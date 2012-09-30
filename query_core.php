<?php
if(!isset($_SERVER['HTTPS']))
{
    exit(1);
}
require_once './db.php';
require_once './data.php';
require_once './auth.php';

header_auth(get_param('username2'), get_param('password2'));
function utf8_to_escaped($s)
{
    return substr(json_encode($s), 1, -1); // remove leading and trailing quotation mark
}
if(isset($_POST['s']))
{
    $pdo = start_db();
    $stmt = $pdo->prepare('SELECT orderer,receiver,stand,company,ID,products FROM chocolate WHERE orderer REGEXP ? AND debug="N"');
    $stmt->execute(array('\\{"name":"[^"]*'.utf8_to_escaped($_POST['s'])));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $output = array(
        'data' => array(), 
        'count' => count($results), 
        'total' => 0
    );
    for($i=0;$i<$output['count'];$i++)
    {
        $item = $results[$i];
        $data = json_decode($item['orderer'], true);
        $campus = campus($item['orderer'], $item['receiver']);
        $ID = $item['stand'].$item['company'].$campus.sprintf("%05d", $item['ID']);
        $data['parsed_id'] = $ID;
        $output['data'][] = $data;
        $output['total'] += price(json_decode($item['products'], true), $item['company']);
    }
    echo json_encode($output);
}
?>
