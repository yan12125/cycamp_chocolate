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
<title>雲嘉會巧克力傳情 - 人名查詢</title>
<script type="text/javascript" src="/HTML/library/jquery.js"></script>
<script>
$(document).on('ready', function(e){
    var run = function(){
        $.post('query_core.php', {s: $('#name').val()}, function(data, status, xhr){
            if(data.data.length == 0)
            {
                $('#results').html('無資料');
                return;
            }
            $('#results').html('數量：'+data.count+'<br>總價：$'+data.total+'<br>');
            $('#results').append('<table border="1"><tr><td></td><td>姓名</td><td>電話</td><td>駐點</td><td>網路訂單編號</td><td>日期</td></tr></table>');
            for(var i=0;i<data.data.length;i++)
            {
                $('#results table').append(
                    '<tr>'+
                        '<td>'+(i+1)+'</td>'+
                        '<td>'+data.data[i].name+'</td>'+
                        '<td>'+data.data[i].tel+'</td>'+
                        '<td>'+data.data[i].stand+'</td>'+
                        '<td>'+data.data[i].id+'</td>'+
                        '<td>'+data.data[i].date+'</td>'+
                    '</tr>'
                );
            }
        }, 'json');
    };
    $('#name').on('keydown', function(e){
        if(e.keyCode === 13)
        {
            run();
        }
    });
    $('#run').on('click', function(e){
        run();
    });
});
</script>
</head>
<body>
    傳情人姓名：<input type="text" id="name">
    <input type="button" value="查詢" id="run">
    <div id="results"></div>
</body>
</html>
