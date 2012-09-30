<?php
if(!isset($_data))
{
    $data_file = file_get_contents('./data.json');
    $_data = json_decode($data_file, true);
    if($_data === null)
    {
        throw new Exception("Error loading data!");
    }
}

function stand($stand_name)
{
    $stands = $GLOBALS['_data']['stands'];
    return array_search($stand_name, $stands);
}

function price($products, $company)
{
    $productList = $GLOBALS['_data']['companies'][$company]['products'];
    $total = 0;
    if(count($products)===count($productList))
    {
        for($j=0;$j<count($products);$j++)
        {
            $total += $productList[$j]['price']*$products[$j];
        }
    }
    else
    {
        throw new Exception('Unmatched item count!');
        return false;
    }
    return $total;
}

function campus($orderer_json, $receiver_json)
{
    $orderer = json_decode($orderer_json, true);
    $receiver = json_decode($receiver_json, true);
    if($orderer === null || $receiver === null)
    {
        throw new Exception("Invalid receiver or order data");
    }
    if($orderer['school'] === $receiver['school']&&$orderer['school']==='台灣大學')
    {
        return 'A';
    }
    else
    {
        return 'B';
    }
}

function fee($company, $campus)
{
    switch($campus)
    {
        case 'A':
            return $GLOBALS['_data']['companies'][$company]['price'][0];
            break;
        case 'B':
            return $GLOBALS['_data']['companies'][$company]['price'][1];
            break;
        default:
            throw new Exception('Invalid company code!');
            return false;
            break;
    }
}

function get_param($name)
{
    $obj = json_decode(file_get_contents('./.htsecret'), true);
    if(isset($obj[$name]))
    {
        return $obj[$name];
    }
    else
    {
        return null;
    }
}

function item_count($pdo)
{
    $stmt = $pdo->query('select count(*) from chocolate where debug="N"');
    $results = $stmt->fetchAll(PDO::FETCH_NUM);
    return $results[0][0];
}
