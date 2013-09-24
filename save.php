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
        'stand_name' => '/^[A-H]$/', 
        'ordered_products' => '/^\[(\d+,){22}\d+\]$/', // json contains 23-1 numbers
        'orderer' => $basic_data, 
        'receiver' => $basic_data, 
        'debug' => '/^[YN]$/'
    );
    $output = array();
    $invalid_fields = array();
    if(validate_and_parse($_POST, $data, $required_fields, $invalid_fields))
    {
        $dbh = start_db();
        $campus = $data['receiver']['school']==='台灣大學'?'A':'B';
        $stand = $data['stand_name'];
        $ID = getMaxID($dbh, $data, $stand)+1;
        $query = 'INSERT INTO chocolate (ID,stand,company,products,receiver,orderer,date,card,debug)'
                              .' VALUES (?,?,?,?,?,?,?,?,?)';
        $stmt = $dbh->prepare($query);
        $send_data = array(
            $ID, 
            $stand, 
            $data['company'], 
            $data['ordered_products'], 
            json_encode($data['receiver']), 
            json_encode($data['orderer']), 
            date('Y/n/j'), 
            $data['add_card']?1:0, 
            $data['debug']
        );
        if($stmt->execute($send_data))
        {
            $output['ID'] = sprintf("%04d", $ID);
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
function getMaxID($dbh, $data, $stand)
{
    //print_r(func_get_args());
    $query = "SELECT MAX(ID) from chocolate WHERE company=? AND stand=?";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($data['company'], $stand));
    $result = $stmt->fetch(PDO::FETCH_NUM);
    if(is_null($result[0]))
    {
        return 0;
    }
    else
    {
        return $result[0];
    }
}
?>
