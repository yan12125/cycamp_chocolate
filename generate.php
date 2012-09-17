<?php
require_once './db.php';
header('Content-type: text/plain;charset=utf-8');

_main();
function _main()
{
    if(!isset($_GET['stand'])||!preg_match('/^[A-G]$/', $_GET['stand']))
    {
        exit(1);
    }
    $dbh = start_db();

    $query = 'SELECT * from chocolate WHERE stand=?';
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($_GET['stand']));
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo count($result)."\n";
    foreach($result as $item)
    {
        $parts = array();
        // ID => XYZddddd
        $parts[] = $item['stand'].$item['company'].$item['campus'].sprintf("%05d", $item['ID']);
        for($i=0;$i<strlen($item['products']);$i++)
        {
            $parts[] = $item['products']{$i};
        }
        $parts[] = $item['price'];
        $parts[] = 0; // 未付款
        $parts[] = 0; // 尚未拿卡片來
        $users = array('receiver', 'orderer');
        foreach($users as $user)
        {
            $user_data = json_decode($item[$user], true);
            foreach($user_data as $data_item)
            {
                $parts[] = $data_item===''?'無':$data_item;
            }
        }
        $parts[] = date('Y/m/d', strtotime($item['date']));
        $parts[] = 0; // 尚未作廢
        echo implode('*', $parts)."\n";
    }
}
?>
