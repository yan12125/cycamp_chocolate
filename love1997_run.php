<?php
if(php_sapi_name() !== 'cli')
{
    exit(0);
}
error_reporting(E_ALL|E_STRICT);

require_once './love1997.php';

set_error_handler(array('Love1997', 'error_handler'));

$instance = new Love1997();
$data_dir = '/home/yen/School/102-1/Activities/chocolate/data/';
$instance->loadFile(array(
    $data_dir.'receiptdata1.cycamp', 
    $data_dir.'receiptdata2.cycamp'
));

$instance->getRemoteList();
file_put_contents('./data/parsed_local_data', print_r($instance->getLocalData(), true));
file_put_contents('./data/parsed_remote_data', 
    print_r($instance->getRemoteData(), true)."\n".
    print_r($instance->getRemoteProducts(), true)."\n".
    print_r($instance->loadRemoteSchools(), true)
);
$instance->check();

file_put_contents('./data/stats.csv', $instance->generateTable());
?>
