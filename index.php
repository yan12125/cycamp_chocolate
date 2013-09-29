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
<html prefix="fb: http://ogp.me/ns/fb#" prefix="og: http://ogp.me/ns#">
    <head>
        <meta charset="UTF-8">
        <title>雲嘉會巧心巧語校園傳情 - 線上訂購網頁<?php if($debug){ echo '(測試用)'; } ?></title>
        <link rel="stylesheet" href="/HTML/library/jquery-ui.css" type="text/css">
        <link rel="stylesheet" href="/HTML/library/bootstrap.min.css">
        <link rel="stylesheet" href="index.css" type="text/css">
        <meta name="description" content="台大雲嘉會 2013巧心巧語校園傳情 線上訂購系統">
        <meta name="keywords" content="巧克力傳情,2013,巧心巧語,校園傳情,線上訂購,雲嘉會,台大">
        <meta name="author" content="台大雲嘉會">
        <meta property="og:type" content="website">
        <meta property="og:title" content="台大雲嘉會 2013巧心巧語校園傳情 線上訂購系統">
        <meta property="og:url" content="http://chyen.twbbs.org/chocolate/">
        <meta property="og:image" content="http://chyen.twbbs.org/chocolate/images/group.jpg">
        <meta property="og:description" content="
                    為了傳遞自己的心意，卻得在大太陽底下大排長龍……
                    歡迎使用台大雲嘉會巧心巧語校園傳情網路訂購系統！
                    只需要簡單的六個步驟，就可以省去現場排隊等待之苦^.&lt; ">
        <script type="text/javascript" src="/HTML/library/jquery.js"></script>
        <script type="text/javascript" src="/HTML/library/jquery.cookie.js"></script>
        <script type="text/javascript" src="/HTML/library/jquery-ui.js"></script>
        <script type="text/javascript" src="/HTML/library/bootstrap.min.js"></script>
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
                </tr>
            </table>
            <div id="page1">
                <div id="messages">
                    <div style="font-size: 120%">
                    為了傳遞自己的心意，卻得在大太陽底下大排長龍……<br>
                    歡迎使用台大雲嘉會巧心巧語校園傳情網路訂購系統！<br>
                    只需要簡單的六個步驟，就可以省去現場排隊等待之苦^.&lt; <br><br>
                    </div>

                    【<a href="./gallery/2013CYChocolate" target="_blank" style="color: blue;">巧克力型錄</a>】
                    <span id="links"></span><br><br>
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
                        更多資訊請洽FB粉絲專頁：
                        <a href="https://www.facebook.com/chocotaida" target="_blank">
                            <!--img src="http://graph.facebook.com/chocotaida/picture"-->
                            <br>2013台大雲嘉會 巧心巧語校園傳情
                        </a>
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
                <div class="right">
                    <div id="fee"></div>
                    小計　　：$<span id="total">0</span>
                </div>
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
            <div id="page6">
                <div id="results">
                    <br>
                    <span class="emphasized_text">請記下您的訂單編號：<span id="result_ID"></span></span>
                    <br><br>
                    注意事項：<div class="notices"></div>
                    金額：$<span id="money"></span>
                    <br><br>
                    <span id="dorm"></span>負責人：<span id="staff"></span>
                    <br>
                    負責人手機：<span id="phone"></span>
                    <br>
                    地點：<span id="place"></span>
                    <br>
                    <!--there is a table with cellspacing=5-->
                    <span id="time" style="margin: -5px;"></span>
                </div>
            </div>
            <div id="buttons-wrapper">
                <table id="buttons">
                    <tr>
                        <td><input type="button" id="button-1" class="btn btn-primary" value="上一頁"></td>
                        <td>
                            <span class="hidden">
                                <input type="checkbox" id="remember_me" checked="checked">記住我的資料以便再次訂購
                            </span>
                            <input type="button" id="button+1" class="btn btn-primary" value="下一頁">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </body>
</html>
