<?php
/*
 * http://stackoverflow.com/questions/4727197
 */
$im = new Imagick(dirname(__FILE__).'/images/group.jpg');

if (!$im->getImageAlphaChannel()) {
    $im->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);
}

$width = $im->getImageWidth();
$height = $im->getImageHeight();

$gradient = new Imagick();

$gradient->newPseudoImage($width, $height, "gradient:black-transparent");

$im->compositeImage($gradient, imagick::COMPOSITE_DSTOUT, 0, 0);

$canvas = new Imagick();

$canvas->newImage($width, $height, 'none');
$canvas->setImageFormat('png');

$canvas->compositeImage($im, imagick::COMPOSITE_SRCOVER, 0, 0);

header('Content-type: image/png');
echo $canvas;
?>
