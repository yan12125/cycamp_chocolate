// modified from http://www.yelotofu.com/2008/08/jquery-outerhtml/, onlu use get
jQuery.fn.outerHTML = function(){
    return jQuery("<p>").append(this.eq(0).clone()).html();
};

$(document).on('ready', function(e){
    // load all data from data.json
    $.ajax({
        'url': './data.json',
        'success': function(_data, status, xhr){
            var curPage = 0;
            var fields = _data.fields;
            var grades = _data.grades;
            var stands = _data.stands;
            var companies = _data.companies;
            var departments = _data.departments;
            var page_description = _data.page_description;
            
            // some data
            var users = [ 'orderer', 'receiver' ];
            var data = {
                company: '', 
                fee: 0, 
                total: 0, 
                stand_name: undefined, 
                add_card: 0, 
                orderer: {}, 
                receiver: {}, 
                ordered_products: []
            };

            // draw input fields
            for(var name in fields)
            {
                for(var idx in users)
                {
                    var tmp = fields[name].type[idx];
                    var tagname = tmp===''?'input type="text"':tmp;
                    var id =users[idx]+'_'+name;
                    var required_text = (fields[name].regex === '.*'?'':'*');
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
            for(var id in companies)
            {
                $('#page1 #links').append(companies[id].name+'工作室 <a href="category.html?company='+id+'&type=products">商品型錄</a> <a href="category.html?company='+id+'&type=schools">可送達的學校</a><br>');
                $('#page1 #choices').append('<input type="radio" name="company" value="'+id+'" id="radio_'+id+'"><label for="radio_'+id+'">'+companies[id].name+'工作室</label>');
            }
            $('#page1 #links a').attr('target', '_blank');
            // load departments of NTU
            for(var id in departments)
            {
                $('#page2 .orderer_department').append('<option value="'+id+'">'+id+' '+departments[id]+'</option>');
            }

            // load grades
            $('#page2 select[class$=_grade]').append('<option value="請選擇">請選擇</option>');
            for(var i in grades)
            {
                $('#page2 select[class$=_grade]').append('<option value="'+(parseInt(i)+1)+'">'+grades[i]+'</option>');
            }

            // load stands
            for(var i in stands)
            {
                $('#page4 #pay_location').append('<input type="radio" name="stand" value="'+i+'" id="stand_'+i+'"><label for="stand_'+i+'">'+stands[i].name+' - '+stands[i].text+'</label><br>');
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

            // hide other pages
            var updatePage = null;
            (updatePage = function(){
                $('div[id^=page]').each(function(idx, item){
                        $(item).attr('class', 'pages');
                        $(item).css('display', ((idx === curPage)?'block':'none'));
                });

                var next_texts = [ '開始訂購', '', '', '', '確認送出', '再次訂購' ];
                var previous_texts = [ '', '', '', '', '修改', '' ];

                $('input[type=button]:eq(0)')
                    .val(previous_texts[curPage]===''?'上一頁':previous_texts[curPage])
                    .css('display', curPage===0||curPage===5?'none':'block');
                $('input[type=button]:eq(1)')
                    .val(next_texts[curPage]===''?'下一頁':next_texts[curPage]);
                $('#notes').width($('#page'+(curPage+1)).width());
                if(curPage>=1&&curPage<=5)
                {
                    $('#notes #step').html('Step '+curPage+': ');
                    $('#notes #content').html(page_description[curPage-1]);
                    //$('#notes #content').css('left', $('#notes #step').width()+10);
                }
                else
                {
                    $('#notes #step').html('');
                    $('#notes #content').html('');
                }
            })();

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
                    var count = parseInt(count_item.val());
                    if(isNaN(count))
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

            $('#page1 input[name=company]').on('change', function(e){
                data.company = $('#page1 input[name=company]:checked').val();
                
                // load the products of the selected company
                for(var j in companies[data.company].products)
                {
                    var s = companies[data.company].products[j];
                    $('#page3 #products').append('<tr><td class="img"><img src="images/'+s.img+'"></td><td class="name">'+s.name+'</td><td class="price">$'+s.price+'</td><td><input type="number" class="count" min="0" value="0"></td></tr>');
                }
                if($('#page3 #products .count')[0].type !== "number") // browser doesn't support html5 number
                {
                    $('#page3 #products tr').append('<img src="images/plus.png" class="btn"><img src="images/minus.png" class="btn">');
                    $('#page3 #products img').on('click', function(e){
                        var item = $(this).parent().find('.count');
                        var delta = (this.src.indexOf('plus.png')!==-1)?(+1):(-1);
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

                // load schools
                $('#page2 .receiver_school').html('<option value="請選擇">請選擇</option>');
                for(var i in companies[data.company].schools)
                {
                    var school_name = companies[data.company].schools[i];
                    $('#page2 .receiver_school').append('<option value="'+school_name+'">'+school_name+'</option>');
                }
            });

            $('#page2 #add_card').on('change', function(e){
                data.add_card = ($('#page2 #add_card').attr('checked')==='checked')?1:0;
            });

            var verifiers = [];
            verifiers[0] = function(){
                if(typeof companies[data.company]==='object')
                {
                    $('#page3 #link a').attr('href', 'category.html?company='+data.company+'&type=products');
                    return true;
                }
                else
                {
                    alert('請選擇其中一家廠商！');
                    return false
                }
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
                if($.inArray(data.receiver.school, companies[data.company].schools)===-1)
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
                    // to prevent .right jumping to second line and 
                    // make next and previous buttons floating as total changes
                    $('#page3 .left').width(350); 
                    return true;
                }
            };
            verifiers[2] = function(){
                if(!page3_validate_numbers())
                {
                    alert('輸入錯誤：非數字');
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
                    $('#page5 #total').html(data.total);
                    $('#page5 #stand').html(stands[data.stand_name].name.replace('、', '或'));
                    $('#page5 #add_card').html(data.add_card===1?'是':'否');
                    $('#page5 #company').html(companies[data.company].name);
                    $('#page5 #products').html('');
                    for(var i in companies[data.company].products)
                    {
                        if(data.ordered_products[i]>0)
                        {
                            var cur_product = companies[data.company].products[i];
                            $('#page5 #products').append(cur_product.name+' $'+cur_product.price+' *'+data.ordered_products[i]+'<br>');
                        }
                    }
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
                data_copy.ordered_products = JSON.stringify(data_copy.ordered_products); // make it less lengthy
                $.ajax({
                    type: 'POST', 
                    url: './save.php', 
                    data: data_copy, 
                    datatype: 'json', 
                    success: function(data2, status, xhr){
                        if(data2.status === 'ok')
                        {
                            if(/^[A-G][AB]{2}\d{5}$/.test(data2.ID))
                            {
                                $('#page6 #result_ID').html(data2.ID);
                                $('#page6 #time').html(stands[data.stand_name].time);
                                $('#page6 #money').html(data.total);
                                $('#page6 #place').html(stands[data.stand_name].name.replace('、', '或'));
                                movePage(+1);
                            }
                            else
                            {
                                alert('發生意外的錯誤，請稍候再試一遍');
                                console.log(data);
                            }
                        }
                        else
                        {
                            alert('發生意外的錯誤，請稍候再試一遍');
                            console.log(data);
                        }
                    }, 
                    error: function(xhr, status, err){console.log(err, xhr);}
                });
                return false; // move page manually
            };
            verifiers[5] = function(){
                location.reload();
            };

            $('input[type=button]').on('click', function(e){
                var s = this.id;
                var dir = parseInt(s.substr(s.length-2)); // direction
                if(dir===-1||verifiers[curPage]())
                {
                    movePage(dir);
                }
            });

            // load FB like button
            FB.init({
                appId      : '227382584057414',
                channelUrl : '//chyen.twbbs.org/chocolate/test/channel.html', // Channel File
                status     : true, // check login status
                cookie     : true, // enable cookies to allow the server to access the session
                xfbml      : true  // parse XFBML
            });
        }, 
        'error': function(xhr, status, err){
            alert('載入資料檔時發生錯誤！請重新整理');
        }
    });
});

