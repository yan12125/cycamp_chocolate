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
            <div id="banner">
                台大雲嘉會巧克力傳情  網路訂購系統
            </div>
            <table id="notes">
                <tr>
                    <td id="step"></td>
                    <td id="content"></td>
                </td>
            </table>
            <div id="page1">
                <div id="messages">
                    今年台大雲嘉會的巧克力傳情有網路預購系統，可以節省大家的排隊時間喔！<br>
                    　　歡迎各位貴賓使用本系統預購巧克力傳情的服務，請依照流程引導輸入我們需要的資訊，就可以完成巧克力的預購了。今年台大雲嘉會的巧克力傳情承辦廠商有兩個工作室，這兩個工作室的巧克力都各具特色，很適合同學們在聖誕節前給好友或情人一個驚喜！那就開始預購喜歡的巧克力吧～<br>

                    注意事項：<br>
                    <ol>
                        <li>台大雲嘉會巧克力傳情僅提供台大校內學生的服務，如有不便，敬請見諒。</li>
                        <li>本預購單僅為縮減各位同學來排隊的時間，巧克力以實品為主，如果網頁上的圖片有任何疑問，請洽詢我們的負責人。</li>
                        <li>填寫預購單後仍須到我們的攤位或是各宿舍的駐點向服務人員付款簽名並領取收據，填寫預購單僅為縮減同學們滯留攤位的時間，收據會由我們統一填寫並交給同學們，請同學妥善保存。</li>
                        <li>我們的承辦廠商有─山豬頭工作室與齊可工作室兩家，這兩家廠商能傳情的學校略有不同，請各位同學在選購巧克力前先看清楚您所要傳達的學校是否在清單內，沒在清單內的學校並沒有提供傳情服務。</li>
                        <li>訂單一旦送出後將無法做更改，請送出前務必確認您的資料正確性。您在向我們服務人員領取收據時可以要求更改資料及訂購商品，但是要重填訂單，可能會浪費您寶貴的時間，敬請見諒。</li>
                        <li>您可以在巧克力訂單上附上卡片喔～卡片可以向我們小福攤位領取或是自備。（自備卡片請不要太大）</li>
                    </ol>
                    台大雲嘉會巧克力傳情小福攤位服務時間：10/1~10/5 10:00~18:00<br>
                    　　　　　　　　　　網路預購服務時間：9/29~10/7 00:00~23:59<br>
                    活動總負責人：電機二 黃大珉 0921159155<br>
                </div>
                <div id="links"></div>
                <span id="choices">請選擇您要訂購的廠商：</span>
            </div>
            <div id="page2">
                <fieldset id="orderer_data">
                    <legend>傳情人資料</legend>
                </fieldset>
                <fieldset id="receiver_data">
                    <legend>幸運人資料</legend>
                </fieldset>
                <div id="add_card_wrapper">
                    <input type="checkbox" id="add_card">
                    <label for="add_card">附加卡片</label>
                </div>
            </div>
            <div id="page3">
                <div id="products"></div>
                <div class="left">
                    <div id="fee"></div>
                    <div id="link">
                        <a href="#" target="_blank">巧克力型錄</a>
                    </div>
                </div>
                <div class="right">小計：$<span id="total">0</span></div>
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
                    <div class="caption">傳情人資料：</div>
                    <div id="orderer"></div><br>
                    <div class="caption">商品列表：</div>
                    <div id="products"></div>
                </div>
                <div class="right">
                    <div class="caption">幸運人資料：</div>
                    <div id="receiver"></div><br>
                    <div class="caption">訂單資料：</div>
                    總價：$<span id="total"></span><br>
                    付款地點：<span id="stand"></span><br>
                    是否附加卡片：<span id="add_card"></span><br>
                    廠商：<span id="company"></span><br>
                </div>
            </div>
            <div id="page6">
                <div id="results">
                    請記下您的訂單編號：<div id="result_ID"></div>
                    並在服務時段<span id="time" class="fields"></span>
                    攜帶<span class="fields">$</span><span id="money" class="fields"></span>
                    至<span id="place" class="fields"></span>領取收據
                </div>
            </div>
            <div id="buttons-wrapper">
                <table id="buttons">
                    <tr>
                        <td><input type="button" id="button-1"></td>
                        <td><input type="button" id="button+1"></td>
                    </tr>
                </table>
            </div>
        </div>
    </body>
</html>
