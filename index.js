// modified from http://www.yelotofu.com/2008/08/jquery-outerhtml/, onlu use get
jQuery.fn.outerHTML = function(){
    return jQuery("<p>").append(this.eq(0).clone()).html();
};

function arrToTable(data, caption)
{
    var lines = data.split('\n');
    var output = $('<table class="table_inline" cellspacing="5px"></table>');
    for(var i = 0; i < lines.length; i++)
    {
        var row = '<td>'+((i == 0)?caption:'')+'</td>';
        var cells = lines[i].split('\t');
        for(var j = 0; j < cells.length; j++)
        {
            row += '<td>'+cells[j]+'</td>';
        }
        output.append('<tr>'+row+'</tr>');
    }
    return output.outerHTML();
}

$(document).on('ready', function(e){
    // load all data from data.json
    $.ajax({
        'url': './data.json',
        'success': function(_data, status, xhr){
            var curPage = 0;
            var steps = _data.steps;
            var fields = _data.fields;
            var grades = _data.grades;
            var stands = _data.stands;
            var companies = _data.companies;
            var departments = _data.departments;
            var notices = _data.notice;
            
            // some data
            var users = [ 'orderer', 'receiver' ];
            var data = {
                company: 'A', 
                fee: 0, 
                total: 0, 
                stand_name: undefined, 
                add_card: 0, 
                orderer: {}, 
                receiver: {}, 
                ordered_products: []
            };

            // load steps
            var stepsStr = [];
            for(var i = 0; i < steps.length; i++)
            {
                stepsStr.push('<span class="step">Step '+(i+1)+' '+steps[i]+'</span>');
            }
            $('#steps').html(stepsStr.join('→'));
            // draw input fields
            for(var name in fields)
            {
                for(var idx in users)
                {
                    var tmp = fields[name].type[idx];
                    var tagname = tmp===''?'input type="text"':tmp;
                    var id =users[idx]+'_'+name;
                    var required_text = (fields[name].regex === '.*'?'':'* ');
                    // special case
                    if(users[idx]==='receiver'&&name === 'email')
                    {
                        required_text = '';
                    }
                    $('#page2 #'+users[idx]+'_data').append('<span class="row">'+fields[name].zh+required_text+'<'+tagname+' class="'+id+'"></'+tagname+'></span><br>');
                }
            }
            $('#page2 input[class$=_tel]').attr('maxlength', '10');
            $('#page2 .orderer_department').append('<option value="請選擇">請選擇</option>');
            $('#page2 .orderer_school')
                .val('台灣大學')
                .parent().append('台灣大學');
            $('#page2 input[class$=_tel]').parent().append('(請全部輸入數字)');

            // add links
            $('#page1 #links').append('【');
            for(var id in companies)
            {
                $('#page1 #links').append('<a href="category.html">可傳情的學校</a>');
            }
            $('#page1 #links').append('】');
            for(var id in companies)
            $('#page1 #links a').attr('target', '_blank');
            // load departments of NTU
            for(var id in departments)
            {
                $('#page2 .orderer_department').append('<option value="'+id+'">'+id+' '+departments[id]+'</option>');
            }

            // load schools
            $('#page2 .receiver_school').html('<option value="請選擇">請選擇</option>');
            var _schools = companies[data.company].schools;
            for(var i = 0; i < _schools.length; i++)
            {
                $('#page2 .receiver_school').append('<optgroup label="'+_schools[i].region+'"></optgroup>');
                for(var j = 0; j < _schools[i].schools.length; j++)
                {
                    var school_name = _schools[i].schools[j];
                    $('#page2 .receiver_school optgroup:last').append('<option value="'+school_name+'">'+school_name+'</option>');
                }
            }

            // load grades
            $('#page2 select[class$=_grade]').append('<option value="請選擇">請選擇</option>');
            for(var i in grades)
            {
                $('#page2 select[class$=_grade]').append('<option value="'+(parseInt(i)+1)+'">'+grades[i]+'</option>');
            }

            // load the products of the selected company
            $('#page3 #products tbody').html('');
            for(var j in companies[data.company].products)
            {
                var s = companies[data.company].products[j];
                $('#page3 #products tbody').append('<tr><td class="img"><img src="'+s.img+'"></td><td class="name">'+s.name+'</td><td class="price">$'+s.price+'</td><td><input type="number" class="count" min="0" value="0"></td></tr>');
            }
            if($('#page3 #products .count')[0].type !== "number") // browser doesn't support html5 number
            {
                $('#page3 #products tr').find('td:last').append('<img src="images/plus.png" class="btn"><img src="images/minus.png" class="btn">');
                $('#page3 #products img').on('mousedown', function(e){
                    var delta = 0;
                    if(this.src.indexOf('plus.png') !== -1)
                    {
                        delta = +1;
                    }
                    else if(this.src.indexOf('minus.png') !== -1)
                    {
                        delta = -1;
                    }
                    else
                    {
                        return;
                    }
                    var item = $(this).parent().find('.count');
                    var original_value = parseInt(item.val());
                    if(!isNaN(original_value))
                    {
                        if(original_value+delta>=0)
                        {
                            item.val(original_value+delta);
                            // seems onchange is not call in firefox 15
                            page3_count(); 
                        }
                    }
                    else
                    {
                        alert('輸入錯誤！');
                    }
                });
            }

            $('#page3 #products .count').on('change', function(e){
                page3_count();
            });

            // load stands
            for(var i in stands)
            {
                var dataTable = arrToTable(stands[i].time, '開放時間：');
                $('#page4 #pay_location').append(
                    '<input type="radio" name="stand" value="'+i+'" id="stand_'+i+'">'+
                    '<label for="stand_'+i+'">'+stands[i].name+'</label><br>'+
                    dataTable+'<br>'
                );
            }

            $('#page2 .receiver_department').parent().append($('#page2 .orderer_department').outerHTML());
            $('#page2 #receiver_data .orderer_department').attr('class', 'hidden receiver_department');
            $('#page2 .receiver_school').on('change', function(e){
                // some util function
                var toggle = function(i){
                    var prefix = '#page2 .receiver_department';
                    $(prefix).removeClass('hidden');
                    $(prefix+':eq('+i+')').addClass('hidden');
                };
                // 校內
                if($('#page2 .receiver_school').val() === $('#page2 .orderer_school').val())
                {
                    toggle(0);
                }
                else // 校外
                {
                    toggle(1);
                }
            });
            $('#page2 .receiver_name').parent().after($('#page2 #add_card_wrapper'));

            // hide other pages
            var updatePage = null;
            (updatePage = function(){
                $('div[id^=page]').each(function(idx, item){
                        $(item).attr('class', 'pages');
                        $(item).css('display', ((idx === curPage)?'block':'none'));
                });
                var $curPage = $('#page'+(curPage+1));
                var tag = notices[curPage].length > 1?'ol':'ul';
                $curPage.find('.notices').html('<'+tag+'></'+tag+'>');
                for(var i = 0; i < notices[curPage].length; i++)
                {
                    var content = notices[curPage][i]
                                    .replace(/\[/g, '<span class="notice">')
                                    .replace(/\]/g, '</span>');
                    $curPage.find('.notices '+tag).append('<li>'+content+'</li>');
                }

                var next_texts = [ '開始訂購', '', '', '', '確認送出', '再次訂購' ];
                var previous_texts = [ '', '', '', '', '修改', '' ];

                $('input[type=button]:eq(0)')
                    .val(previous_texts[curPage]===''?'上一頁':previous_texts[curPage])
                    .css('display', curPage===0||curPage===5?'none':'block');
                $('input[type=button]:eq(1)')
                    .val(next_texts[curPage]===''?'下一頁':next_texts[curPage]);
                $('#notes').width($curPage.width());
                $('.step').removeClass('selected');
                $('.step').eq(curPage).addClass('selected');
            })();

            // load data from cookie
            for(var _s in fields)
            {
                if($.cookie('ch_'+_s)!==null)
                {
                    $('#page2 .orderer_'+_s).val($.cookie('ch_'+_s));
                }
            }

            // define the events of the buttons
            var movePage = function(direction){
                curPage+=direction;
                updatePage();
            };

            // for those doesn't support <input type='number'> (IE)
            var page3_validate_numbers = function(){
                var valid = true;
                $('#page3 #products tr').each(function(idx, item){
                    var count_item = $(item).find('.count');
                    count_item.css({background: '', color: ''});
                    if(!(/^\d+$/.test(count_item.val())))
                    {
                        count_item.css({background: 'yellow', color: 'red'});
                        valid = false;
                    }
                });
                return valid;
            };
            var page3_count = function(){
                var ret_obj = {
                    products: [], 
                    total: 0
                };
                if(page3_validate_numbers())
                {
                    $('#page3 #products tr').each(function(idx, item){
                        var price = parseInt($(item).find('.price').html().substring(1)); // the first character is '$'
                        var count_item = $(item).find('.count');
                        var count = parseInt(count_item.val());
                        ret_obj.products[idx] = count;
                        ret_obj.total += price*count;
                    });
                    $('#page3 #total').html(ret_obj.total+data.fee);
                }
                return ret_obj;
            };

            $('#page2 #add_card').on('change', function(e){
                data.add_card = ($('#page2 #add_card').attr('checked')==='checked')?1:0;
            });

            var verifiers = [];
            verifiers[0] = function(){
                return true;
            };
            verifiers[1] = function(){
                $('#page2 .row').css('color', '');
                var invalid = false;
                var contains_asterisk = false;

                // remove all spaces in telephone
                $('#page2 input[class$=_tel]').val(function(index, value){
                    // reference: http://www.mediacollege.com/internet/javascript/form/remove-spaces.html
                    return value.replace(/\s+/g, '');
                });
                // first parse ordinary data
                for(var idx in users)
                {
                    $('#page2 #'+users[idx]+'_data')
                        .find('input, select')
                        .not('.hidden')
                        .each(function(idx2, item2){
                            if(item2.id == 'add_card')
                            {
                                return;
                            }
                            var field_name = item2.className.substr((users[idx]+'_').length);
                            data[users[idx]][field_name] = (item2.value!=='請選擇')?item2.value:'';
                            var regex = fields[field_name].regex;
                            var rule = new RegExp(regex||"\\S+");
                            if(!rule.test(item2.value)||(regex!=='.*'&&item2.value==='請選擇'))
                            {
                                if(!(users[idx]==='receiver'&&field_name==='email'&&item2.value===''))
                                {
                                    invalid = true;
                                    $(item2).parent().css('color', 'red');
                                }
                            }
                            if(item2.value.indexOf('*')!==-1)
                            {
                                contains_asterisk = true;
                                $(item2).parent().css('color', 'red');
                            }
                        });
                }

                // then orderer.department and receiver.school
                data.orderer.department = $('#page2 select[class=orderer_department]').val();
                if(typeof departments[data.orderer.department]==='undefined')
                {
                    $('#page2 .orderer_department').parent().css('color', 'red');
                    invalid = true;
                }
                data.receiver.school = $('#page2 select[class=receiver_school]').val();
                // IE does not support Array.prototype.indexOf
                var _schools = companies[data.company].schools;
                var school_invalid = true;
                for(var i = 0; i < _schools.length; i++)
                {
                    if($.inArray(data.receiver.school, _schools[i].schools) !== -1)
                    {
                        school_invalid = false;
                    }
                }
                if(school_invalid)
                {
                    $('#page2 .receiver_school').parent().css('color', 'red');
                    invalid = true;
                }

                var msg = '';
                if(invalid)
                {
                    msg += '部份資料未填或不完整！\n';
                }
                if(contains_asterisk)
                {
                    msg += '內容不能包含「 * 」符號！\n';
                }
                if(msg!=='')
                {
                    alert(msg);
                    return false;
                }
                else
                {
                    // now we know 校內 or 校外
                    if(data.receiver.school === data.orderer.school)
                    {
                        data.fee = companies[data.company].price[0];
                        $('#page3 #fee').html('校內運費：$'+data.fee);
                    }
                    else
                    {
                        data.fee = companies[data.company].price[1];
                        $('#page3 #fee').html('校外運費：$'+data.fee);
                    }
                    $('#page3 #total').html(data.fee);
                    return true;
                }
            };
            verifiers[2] = function(){
                if(!page3_validate_numbers())
                {
                    alert('輸入錯誤：非正整數');
                    return false;
                }
                var result = page3_count();
                data.ordered_products = result.products;
                data.total = result.total;
                if(data.total<=0)
                {
                    alert('請至少選擇一種商品！');
                    return false;
                }
                else
                {
                    data.total += data.fee;
                    return true;
                }
            };
            verifiers[3] = function(){
                data.stand_name = $('#page4 input[name=stand]:checked').val();
                if(typeof data.stand_name!=='undefined')
                {
                    // output all data on the last page
                    $('#page5 #fee').html(data.fee);
                    $('#page5 #total2').html(data.total);
                    $('#page5 #stand').html(stands[data.stand_name].name);
                    $('#page5 #add_card2').html(data.add_card===1?'是':'否');
                    $('#page5 #company').html(companies[data.company].name);
                    var products_json = [];
                    for(var i in companies[data.company].products)
                    {
                        if(data.ordered_products[i]>0)
                        {
                            var cur_product = companies[data.company].products[i];
                            products_json.push([ cur_product.name, ' $'+cur_product.price, ' ×'+data.ordered_products[i] ].join('\t'));
                        }
                    }
                    $('#page5 #products2').html(arrToTable(products_json.join('\n'), ''));
                    for(var idx in users)
                    {
                        $('#page5 #'+users[idx]).html('');
                        for(var i in fields)
                        {
                            $('#page5 #'+users[idx]).append(fields[i].zh+data[users[idx]][i]+'<br>');
                        }
                    }
                    return true;
                }
                else
                {
                    alert('請選擇一個付款地點！');
                }
            };
            verifiers[4] = function(){
                // clone a temporary version because strings will be escaped
                var data_copy = JSON.parse(JSON.stringify(data));
                if(location.search.indexOf('debug')!==-1)
                {
                    data_copy.debug = 'Y';
                }
                else{
                    data_copy.debug = 'N';
                }
                data_copy.ordered_products = JSON.stringify(data_copy.ordered_products); // make it less lengthy
                $.ajax({
                    type: 'POST', 
                    url: './save.php', 
                    data: data_copy, 
                    datatype: 'json', 
                    success: function(data2, status, xhr){
                        if(data2.status === 'ok')
                        {
                            if(/^\d{4}$/.test(data2.ID))
                            {
                                var standName = stands[data.stand_name].name;
                                $('#page6 #result_ID').html(data2.ID);
                                $('#page6 #time').html(arrToTable(stands[data.stand_name].time, '開放時間：'));
                                $('#page6 #money').html(data.total);
                                $('#page6 #dorm').html(standName);
                                $('#page6 #place').html(standName+stands[data.stand_name].room);
                                $('#page6 #staff').html(stands[data.stand_name].staff);
                                $('#page6 #phone').html(stands[data.stand_name].phone);
                                $('#remember_me').parent().show();
                                movePage(+1);
                            }
                            else
                            {
                                alert('發生意外的錯誤(伺服器傳回錯誤的資料)，請稍候再試一遍');
                                //console.log(data);
                            }
                        }
                        else
                        {
                            alert('發生意外的錯誤(訂單新增失敗)，請稍候再試一遍');
                            //console.log(data);
                        }
                    }, 
                    error: function(xhr, status, err){
                        alert('發生意外的錯誤(網路連線失敗)，請稍候再試一遍');
                        //console.log(err, xhr);
                    }
                });
                return false; // move page manually
            };
            verifiers[5] = function(){
                if($('#remember_me').attr('checked')==='checked')
                {
                    alert('您的資料將會再下次訂購時自動填入。若要清除這些資料，請將所有網頁關閉。');
                    for(var i in fields)
                    {
                        if(i === 'comment')
                        {
                            continue;
                        }
                        $.cookie('ch_'+i, data.orderer[i]);
                    }
                }
                location.reload();
            };

            $('input[type=button]').on('click', function(e){
                var s = this.id;
                var dir = parseInt(s.substr(s.length-2)); // direction
                if(dir===-1||verifiers[curPage]())
                {
                    movePage(dir);
                }
            }).button();

            // load FB like button
            FB.init({
                appId      : '227382584057414',
                channelUrl : '//chyen.twbbs.org/chocolate/channel.html', // Channel File
                status     : true, // check login status
                cookie     : true, // enable cookies to allow the server to access the session
                xfbml      : true  // parse XFBML
            });
            FB.Event.subscribe('xfbml.render', function(response){
                var offsetTop = 0;
                var $like = $('#like_button');
                $like.children().each(function(){
                    offsetTop -= $(this).height();
                });
                $like.css('top', offsetTop);
            });
        }, 
        'error': function(xhr, status, err){
            alert('載入資料檔時發生錯誤！請重新整理');
        }
    });
});

