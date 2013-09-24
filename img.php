<?php
require './util.php';
// Parsing parameters
$data = array();
$invalid_fields = array();
$required_fields = array(
    'dir' => '/FLORA|Fun/', 
    'id' => '/\d{3}/'
);
if(!validate_and_parse($_GET, $data, $required_fields, $invalid_fields))
{
    header('HTTP/1.1 404 Not found');
    exit(0);
}

// File and new size
$pattern = 'images/'.$data['dir'].'/'.$data['id'].'*.jpg';
$filenames = glob($pattern);
if(empty($filenames))
{
    header('HTTP/1.1 404 Not found');
    exit(0);
}
$filename = $filenames[0];

// transform to a fixed height 60
list($width, $height) = getimagesize($filename);
$newwidth = $width * 60 / $height;
$newheight = 60;

$thumb = imagecreatetruecolor($newwidth, $newheight);
$source = imagecreatefromjpeg($filename);

imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

header('Content-Type: image/jpeg');
imagejpeg($thumb);

imagedestroy($thumb);
imagedestroy($source);

?>
