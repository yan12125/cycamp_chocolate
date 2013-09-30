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
    $stmt = $pdo->prepare('SELECT orderer,receiver,stand,company,ID,products,date FROM chocolate WHERE orderer REGEXP ? AND debug="N" ORDER BY date DESC,stand,ID');
    // http://lists.mysql.com/mysql/206935
    $stmt->execute(array('\\{"name":"[^"]*'.str_replace("\\", "\\\\", utf8_to_escaped($_POST['s']))));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $output = array(
        'data' => array(), 
        'count' => count($results), 
        'total' => 0, 
        'debug' => array('\\{"name":"[^"]*'.utf8_to_escaped($_POST['s']))
    );
    for($i=0;$i<$output['count'];$i++)
    {
        $item = $results[$i];
        $data2 = json_decode($item['orderer'], true);
        $campus = campus($item['orderer'], $item['receiver']);
        $data['name'] = $data2['name'];
        $data['tel'] = $data2['tel'];
        $data['stand'] = $_data['stands'][$item['stand']]['name'];
        $data['id'] = sprintf("%04d", $item['ID']);
        $data['date'] = $item['date'];
        $output['data'][] = $data;
        $output['total'] += price(json_decode($item['products'], true), $item['company'])+fee($item['company'], $campus);
    }
    echo json_encode($output);
}
?>
