<?php
require_once './db.php';
require_once './data.php';
require_once './auth.php';
header('Content-type: text/plain;charset=utf-8');

header_auth(get_param('username'), get_param('password'));
_main();
function _main()
{
    if(!isset($_GET['stand'])||!preg_match('/^[A-H]$/', $_GET['stand']))
    {
        exit(1);
    }
    $dbh = start_db();

    $query = 'SELECT * from chocolate WHERE stand=? AND debug=?';
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($_GET['stand'], $_GET['debug']));
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo count($result)."\n";
    foreach($result as $item)
    {
        $parts = array();
        // ID => dddd, each region has its own counter
        $campus = campus($item['orderer'], $item['receiver']);
        $parts[] = sprintf("%04d", $item['ID']);
        $parts[] = $campus;
        $products = json_decode($item['products'], true);
        for($i=0;$i<count($products);$i++)
        {
            $parts[] = $products[$i];
        }
        $parts[] = price($products, $item['company'])+fee($item['company'], $campus);
        $parts[] = 0; // 未付款
        $parts[] = $item['card'];
        $users = array('receiver', 'orderer');
        foreach($users as $user)
        {
            $user_data = json_decode($item[$user], true);
            foreach($user_data as $key => $data_item)
            {
                if($key=='department'&&$user_data['school']=='台灣大學')
                {
                    $parts[] = $data_item.$GLOBALS['_data']['departments'][$data_item];
                }
                else
                {
                    $parts[] = $data_item===''?'無':$data_item;
                }
            }
        }
        $parts[] = date('Y/m/d', strtotime($item['date']));
        $parts[] = 0; // 尚未作廢
        echo implode('*', $parts)."\n";
    }
}
?>
