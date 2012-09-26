<?php
    require_once './auth.php';
    header_auth('cloudhome', 'query');
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
                        $('#results').html('');
                        if(data.length>0)
                        {
                            for(var i=0;i<data.length;i++)
                            {
                                $('#results').append(data[i].name+" "+data[i].tel+" "+data[i].parsed_id+"<br>\n");
                            }
                        }
                        else
                        {
                            $('#results').html('無資料');
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
        姓名：<input type="text" id="name">
        <input type="button" value="查詢" id="run">
        <div id="results"></div>
    </body>
</html>
