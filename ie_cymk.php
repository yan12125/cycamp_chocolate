<?php
if(!isset($_GET['path']))
{
    exit(0);
}

// http://offshootinc.com/blog/2008/10/24/using-the-imagick-class-to-convert-a-cmyk-jpeg-to-rgb/
// ImageMagick requires full path
$filePath = dirname(__FILE__).'/gallery/var/'.$_GET['path'];
$i = new Imagick($filePath);
 
$cs = $i->getImageColorspace();
if($cs == Imagick::COLORSPACE_CMYK)
{
    $i->setImageColorspace(Imagick::COLORSPACE_RGB);
    // http://www.php.net/manual/en/imagick.setimagecolorspace.php#112426
    $i->negateImage(false, Imagick::CHANNEL_ALL);
}
header('Content-type: image/jpeg');
echo $i;
 
$i->clear();
$i->destroy();
$i = null;
?>
