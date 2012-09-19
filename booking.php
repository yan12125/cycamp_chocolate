<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>雲嘉會巧克力傳情 - 線上訂購網頁</title>
        <!--link rel="shortcut icon" href="/chocolate/cloudhome.ico"-->
        <link rel="stylesheet" href="index.css" type="text/css">
        <script type="text/javascript" src="/HTML/library/jquery.js"></script>
        <script type="text/javascript" src="./index.js"></script>
    </head>
    <body>
        <div id="wrapper">
            <div id="page1">
                <div id="catalog">
                    <!--fieldset will generate additional margin, so used another divs to pack them-->
                    <div class="left">
                        <fieldset id="companyA"></fieldset>
                    </div>
                    <div class="right">
                        <fieldset id="companyB"></fieldset>
                    </div>
                </div>
                <div id="choices"></div>
                <div id="add_card_wrapper">
                    <input type="checkbox" id="add_card">
                    <label for="add_card">附加卡片</label>
                </div>
            </div>
            <div id="page2">
                <div id="note">打*號為必填，內容不能包含星號</div>
                <fieldset id="orderer_data">
                    <legend>傳情人資料</legend>
                </fieldset>
                <fieldset id="receiver_data">
                    <legend>幸運人資料</legend>
                </fieldset>
            </div>
            <div id="page3">
                <div id="products"></div>
                <div id="fee" class="left"></div>
                <div class="right">
                    小計：$<span id="total">0</span>
                </div>
            </div>
            <div id="page4">
                <div id="wrapper">
                    <fieldset id="pay_location">
                        <legend>付款地點</legend>
                    </fieldset>
                </div>
            </div>
            <div id="page5">
                <div class="left">
                    總價：$<span id="total"></span><br>
                    付款地點：<span id="stand"></span><br>
                    是否附加卡片：<span id="add_card"></span><br>
                    廠商：<span id="company"></span><br><br><!--require two \n to separate data-->
                    傳情人資料：<div id="orderer"></div><br>
                    幸運人資料：<div id="receiver"></div><br>
                </div>
                <div class="right">
                    商品列表：<div id="products"></div>
                </div>
            </div>
            <div id="page6">
                <div id="results">
                </div>
            </div>
            <table id="buttons">
                <tr>
                    <td><input type="button" id="button-1"></td>
                    <td><input type="button" id="button+1"></td>
                </tr>
            </div>
        </div>
    </body>
</html>
