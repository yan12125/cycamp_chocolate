<?php
set_time_limit(0);
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', 'On');
if(!isset($_SERVER['HTTPS']))
{
    $redirect= "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    header("Location:{$redirect}");
    exit(0);
}
require_once './auth.php';
require_once './data.php';
header_auth(get_param('username2'), get_param('password2'));

if(!isset($_FILES))
{
    exit(0);
}
header('Content-type: text/json');
// print_r($_FILES);
$data = explode("\n", file_get_contents($_FILES['file']['tmp_name']));
$schools = json_decode(file_get_contents('./data.json'), true);
$schools = $schools['companies']['A']['schools'];

array_splice($data, 0, 1);
for($i = 0; $i < count($data); $i++)
{
    $item = explode('*', $data[$i]);
    if(count($item) != 46)
    {
        continue;
    }
    $products = array_map(function ($n) {
        return intval($n);
    }, array_splice($item, 2, 23));
    $data[$i] = array(
        'id' => $item[0], 
        'products' => $products, 
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
    $school_id = '';
    $school_index2 = 0;
    for($j = 0; $j < count($schools); $j++)
    {
        $school_index2 = array_search($data[$i]['receiver']['school'], $schools[$j]['schools']);
        if($school_index2 !== false)
        {
            $school_id = $schools[$j]['id'][$school_index2];
        }
    }
    $grade_map = array(
        '一' => 1, 
        '二' => 2, 
        '三' => 3, 
        '四' => 4, 
        '五' => 5, 
        '六' => 6, 
        '七' => 7
    );
    $grade = 6;
    if($data[$i]['receiver']['grade'] !== '無')
    {
        $grade2 = $data[$i]['receiver']['grade'];
        if(isset($grade_map[$grade2]))
        {
            $grade = $grade_map[$grade2];
        }
        else if(intval($grade2) !== 0)
        {
            $grade = $grade2;
        }
    }
    $post_data = array(
        'from' => 'ntu', 
        'number' => sprintf('%06d', $data[$i]['id']), 
        'area' => $school_index2 + 1, 
        'to' => $school_id, 
        'grade' => $grade, 
        'sex' => 1
    );
    for($j = ord('a'); $j <= ord('w'); $j++)
    {
        $curN = $data[$i]['products'][$j - ord('a')];
        if($curN <= 4)
        {
            $post_data[chr($j)] = $curN;
            $post_data[chr($j)."other"] = "";
        }
        else
        {
            $post_data[chr($j)] = 0;
            $post_data[chr($j)."other"] = $curN;
        }
    }
    print_r($post_data);
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => 'http://www.lovechoco1997.com.tw/result.php', 
        CURLOPT_POST => 1, 
        CURLOPT_POSTFIELDS => $post_data, 
        CURLOPT_RETURNTRANSFER => 1, 
        CURLOPT_BINARYTRANSFER => 1
    ));
    echo curl_exec($ch);
    curl_close($ch);
    ob_flush();
    flush();
    usleep(500*1000); // 0.5 seconds
}
?>
