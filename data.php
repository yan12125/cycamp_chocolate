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
        throw 'Unmatched item count!';
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

function fee($company)
{
    switch($company)
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
