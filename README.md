雲嘉會 巧克力傳情 線上訂購系統

訂購網頁
index.html
index.js
index.css

generate.html 產生訂單資料
category.html 商品型錄、學校名單

images/ 存放巧克力的圖片

後端處理
save.php 新增訂購紀錄
generate.php 產生某個付款地點的訂購資料

資料檔 PHP和Javascript共用同一份
data.uncompressed.json 原始檔案
data.json 壓縮後的檔案
data.json.old 每次壓縮的備份檔
compress_data.js 執行壓縮的小程式，須以node.js執行

.htaccess 
    1. rewrite standX.cycamp到generate.html?stand=X
    2. 限制generate.php與standX.cycamp的存取IP

trash 一些暫存檔和不會用到的程式
