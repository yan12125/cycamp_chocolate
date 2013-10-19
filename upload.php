<?php
if(!isset($_SERVER['HTTPS']))
{
    $redirect= "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    header("Location:{$redirect}");
    exit(0);
}
require_once './auth.php';
require_once './data.php';
header_auth(get_param('username2'), get_param('password2'));
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
</head>
<body>
    <form action="upload_impl.php" method="post" enctype="multipart/form-data">
        請選擇檔案：
        <input type="file" name="file">
        <input type="submit" value="送出">
    </form>
</body>
</html>
