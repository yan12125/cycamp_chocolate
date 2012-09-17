<?php
require_once './db.php';
require_once './util.php';
require_once './data.php';
header('Content-type: application/json;charset=utf-8');
_main();
function _main()
{
    $basic_data = array('name', 'school', 'department', 'grade', 'class', 'tel', 'email', 'comment');
    $required_fields = array(
        'add_card', 'company', 'total', 'stand_name', 'ordered_products', 
        'orderer' => $basic_data, 
        'receiver' => $basic_data, 
    );
    $data = validate_and_parse($_POST, $required_fields);
    if($data!==false)
    {
        $dbh = start_db();
        $output = array();
        $campus = $data['receiver']['school']==='台灣大學'?'A':'B';
        $stand = stand($data['stand_name']);
        $ID = getMaxID($dbh, $data, $campus, $stand)+1;
        if(($products = json_decode($data['ordered_products'], true))===null)
        {
            $output['status'] = 'error';
        }
        else
        {
            $query = 'INSERT INTO chocolate (ID,stand,company,campus,products,price,receiver,orderer_name,orderer,date,card)'
                                  .' VALUES (?,?,?,?,?,?,?,?,?,?,?)';
            $stmt = $dbh->prepare($query);
            $send_data = array(
                $ID, 
                $stand, 
                $data['company'], 
                $campus, 
                implode($products), 
                $data['total'], 
                json_encode($data['receiver']), 
                $data['orderer']['name'], 
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

        echo json_encode($output);
    }
}
function getMaxID($dbh, $data, $campus, $stand)
{
    $ID = 0;
    $query = "SELECT MAX(ID) from chocolate WHERE company=? AND stand=? AND campus=?";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($data['company'], $stand, $campus));
    $result = $stmt->fetchAll();
    if(count($result)===0)
    {
       $ID = 0;
    }
    else
    {
       $ID = (integer)$result[0][0];
    }
    return $ID;
}
?>
