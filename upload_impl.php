<?php
if(!isset($_SERVER['HTTPS']))
{
    $redirect= "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    header("Location:{$redirect}");
    exit(0);
}
require_once './auth.php';
require_once './data.php';
require_once './db.php';
header_auth(get_param('username2'), get_param('password2'));

if(!isset($_FILES))
{
    exit(0);
}
// header('Content-type: text/plain');
// print_r($_FILES);
$data = explode("\n", file_get_contents($_FILES['file']['tmp_name']));

// remove tailing \n
if($data[count($data) - 1] === "")
{
    unset($data[count($data) - 1]);
}

$nRows = intval($data[0]);
if($nRows != count($data) - 1)
{
    echo "資料格式錯誤：項目數量不符\n";
    exit(0);
}
$dbh = start_db();
array_splice($data, 0, 1);
$added = 0;
$not_added = 0;
for($i = 0; $i < count($data); $i++)
{
    $item = explode('*', $data[$i]);
    $products = array_map(function ($n) {
        return intval($n);
    }, array_splice($item, 2, 23));
    $data[$i] = array(
        'id' => $item[0], 
        'products' => json_encode($products), 
        'paid' => intval($item[3]), 
        'receiver' => array(), 
    );
    array_splice($item, 0, 5);
    $fields = array('name', 'school', 'department', 'grade', 'class', 'phone', 'mail', 'comment');
    for($j = 0; $j < count($fields); $j++)
    {
        $data[$i]['receiver'][$fields[$j]] = $item[$j];
    }
    array_splice($item, 0, 2 * count($fields));
    $data[$i]['date'] = $item[0];
    $data[$i]['discarded'] = intval($item[1]);
    if($data[$i]['discarded'] != 0 || $data[$i]['paid'] == 0)
    {
        continue;
    }
    $stmt = $dbh->prepare('SELECT count(ID) FROM orders_2013 WHERE ID = ?');
    $stmt->execute(array($data[$i]['id']));
    $result = $stmt->fetch(PDO::FETCH_NUM);
    if($result[0] == 1)
    {
        $not_added++;
        continue;
    }
    $stmt = $dbh->prepare('INSERT INTO orders_2013 (ID,products,receiver,date) VALUES (?,?,?,?)');
    $stmt->execute(array(
        $data[$i]['id'], 
        $data[$i]['products'], 
        json_encode($data[$i]['receiver']), 
        $data[$i]['date']
    ));
    $added++;
}
// var_dump($data);
echo "共{$nRows}張訂單<br>\n已新增{$added}筆，和已存在的訂單重複有{$not_added}筆";
?>
<br>
<a href="./upload.php">上傳其他檔案</a>
