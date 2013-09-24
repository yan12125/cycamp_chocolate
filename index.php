<?php
    $debug = true;
    if(!isset($_GET['debug']))
    {
        $debug = false;
        $start_time = strtotime('2013/9/27 00:00');
        $end_time = strtotime('2013/10/5 23:59');
        $now = time();
        if($now<$start_time||$now>$end_time)
        {
            header('Location: not_running.html');
            exit(0);
        }
    }
?>
<!DOCTYPE html>
<html xmlns:fb="http://ogp.me/ns/fb#">
    <head>
        <meta charset="UTF-8">
        <title>雲嘉會巧心巧語校園傳情 - 線上訂購網頁<?php if($debug){ echo '(測試用)'; } ?></title>
        <link rel="stylesheet" href="index.css" type="text/css">
        <script type="text/javascript" src="/HTML/library/jquery.js"></script>
        <script type="text/javascript" src="/HTML/library/jquery.cookie.js"></script>
        <script type="text/javascript" src="/HTML/library/json2.js"></script>
        <script src="//connect.facebook.net/zh_TW/all.js"></script>
        <script type="text/javascript" src="./index.js"></script>
    </head>
    <body>
        <div id="fb-root"></div>
        <div id="wrapper">
            <div id="banner">
                台大雲嘉會巧心巧語校園傳情  網路訂購系統<?php if($debug){ echo '(測試用)'; } ?>
            </div>
            <table id="notes">
                <tr>
                    <td id="steps"></td>
                    <td id="content"></td>
                </td>
            </table>
            <div id="page1">
                <div id="messages">
                    <div style="font-size: 120%">
                    為了傳遞自己的心意，卻得在大太陽底下大排長龍……<br>
                    歡迎使用台大雲嘉會巧心巧語校園傳情網路訂購系統！<br>
                    只需要簡單的五個步驟，就可以省去現場排隊等待之苦^.&lt; <br><br>
                    </div>

                    <div id="links"></div><br><br>
                    注意事項：<div class="notices"></div>

                    台大雲嘉會巧克力傳情小福攤位服務時間：9/30~10/4 09:00~17:00<br>
                    　　　　　　　　　　網路預購服務時間：9/27~10/5 00:00~23:59<br><br>
                    <table>
                        <tbody>
                            <tr>
                                <td>活動總負責人：</td>
                                <td>政治二 施學沂 0963013937</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>醫學二 李鴻瑋 0934262226</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>工管二 張沛綸 0972257151</td>
                            </tr>
                        </tbody>
                    </table>
                    <div id="like_button">
                        <fb:like href="https://www.facebook.com/chocotaida" width="300" show_faces="true" send="true"></fb:like>
                    </div>
                </div>
            </div>
            <div id="page2">
                <div>
                    注意事項：<div class="notices"></div>
                </div>
                <fieldset id="orderer_data">
                    <legend>傳情人資料</legend>
                </fieldset>
                <fieldset id="receiver_data">
                    <legend>幸運人資料</legend>
                </fieldset>
                <span id="add_card_wrapper">
                    <input type="checkbox" id="add_card">
                    <label for="add_card">附加卡片</label>
                </span>
            </div>
            <div id="page3">
                <div id="products_wrapper"><table id="products"><tbody></tbody></table></div>
                <div class="left">
                    <div id="fee"></div>
                    <div id="link">
                        <a href="category.html?company=A&type=products" target="_blank">巧克力型錄</a>
                    </div>
                </div>
                <div class="right">小計：$<span id="total">0</span></div>
            </div>
            <div id="page4">
                <div id="pay_location_wrapper">
                    注意事項：<div class="notices"></div>
                    <fieldset id="pay_location">
                        <legend>付款地點</legend>
                    </fieldset><br>
                </div>
            </div>
            <div id="page5">
                <div id="border">
                    <div class="left">
                        <div class="caption" id="caption_orderer">傳情人資料(自己)</div>
                        <div id="orderer"></div><br>
                        <div class="caption">訂單資料：</div>
                        總價：$<span id="total2"></span><br>
                        付款地點：<span id="stand"></span><br>
                        是否附加卡片：<span id="add_card2"></span><br>
                        商品列表：
                        <div id="products2"></div>
                    </div>
                    <div class="right">
                        <div class="caption" id="caption_receiver">幸運人資料(對方)</div>
                        <div id="receiver"></div><br>
                    </div>
                    <div class="spacer"></div>
                </div>
            </div>
            <div id="page6">
                <div id="results">
                    <span id="emphasized_text">請記下您的訂單編號：</span>
                    <div id="result_ID"></div>
                    並在服務時段<br><span id="time" class="fields"></span><br>
                    攜帶<span class="fields">$</span><span id="money" class="fields"></span>
                    至<span id="place" class="fields"></span><br>
                    向駐點人員<span id="staff" class="fields"></span>領取收據
                </div>
            </div>
            <div id="buttons-wrapper">
                <table id="buttons">
                    <tr>
                        <td><input type="button" id="button-1"></td>
                        <td>
                            <span class="hidden">
                                <input type="checkbox" id="remember_me" checked="checked">記住我的資料以便再次訂購
                            </span>
                            <input type="button" id="button+1">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </body>
</html>
