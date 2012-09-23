<?php
require_once './db.php';
require_once './util.php';
require_once './data.php';
header('Content-type: application/json;charset=utf-8');
_main();
function _main()
{
    $basic_data = array(
        'name' => '/.+/', 
        'school'=> '/.+/', 
        'department' => '/.+/', 
        'grade' => '/(^$)|(^[1-7]$)/', 
        'class' => '/.*/', 
        'tel' => '/^0[2-9]\d{7,8}$/', 
        'email' => '/(^$)|(^[a-zA-z0-9.%+-]+@([a-zA-Z0-9-_]+\\.)+[a-zA-Z0-9-_]+$)/', 
        'comment' => '/.*/'
    );
    $required_fields = array(
        'add_card' => '/^[0-1]$/', 
        'company' => '/^[A-B]$/', 
        'stand_name' => '/^[A-BD-G]$/', 
        'ordered_products' => '/^\[(\d+,){13,21}\d+\]$/', // json contains 14-1 or 22-1 numbers
        'orderer' => $basic_data, 
        'receiver' => $basic_data, 
    );
    $output = array();
    $invalid_fields = array();
    if(validate_and_parse($_POST, $data, $required_fields, $invalid_fields))
    {
        $dbh = start_db();
        $campus = $data['receiver']['school']==='台灣大學'?'A':'B';
        $stand = $data['stand_name'];
        $ID = getMaxID($dbh, $data, $campus, $stand)+1;
        $query = 'INSERT INTO chocolate (ID,stand,company,products,receiver,orderer,date,card)'
                              .' VALUES (?,?,?,?,?,?,?,?)';
        $stmt = $dbh->prepare($query);
        $send_data = array(
            $ID, 
            $stand, 
            $data['company'], 
            $data['ordered_products'], 
            json_encode($data['receiver']), 
            json_encode($data['orderer']), 
            date('Y/n/j'), 
            $data['add_card']?1:0
        );
        if($stmt->execute($send_data))
        {
            $output['ID'] = $stand.$data['company'].$campus.sprintf("%05d", $ID);
            $output['status'] = 'ok';
        }
        else
        {
            $output['status'] = 'error';
            $output['info'] = $stmt->errorInfo();
        }
    }
    else
    {
        $output['status'] = 'error';
        $output['info'] = 'Invalid data';
        $output['invalid_fields'] = $invalid_fields;
    }
    echo json_encode($output);
}
function getMaxID($dbh, $data, $campus, $stand)
{
    //print_r(func_get_args());
    $ID = 0;
    $query = "SELECT ID,receiver from chocolate WHERE company=? AND stand=?";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($data['company'], $stand));
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $IDs = array();
    foreach($result as $item)
    {
        $receiver_data = json_decode($item['receiver'], true);
        $campus2 = ($receiver_data['school']==='台灣大學')?'A':'B';
        if($campus2 === $campus)
        {
            $IDs[] = $item['ID'];
        }
    }
    if(count($IDs) === 0)
    {
        return 0;
    }
    else
    {
        return max($IDs);
    }
}
?>
