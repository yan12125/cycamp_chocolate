var curPage = 0;

// modified from http://www.yelotofu.com/2008/08/jquery-outerhtml/, onlu use get
jQuery.fn.outerHTML = function(){
    return jQuery("<p>").append(this.eq(0).clone()).html();
};

$(document).on('ready', function(e){
    // some data
    var users = [ 'orderer', 'receiver' ];
    var data = {
        company: '', 
        fee: 0, 
        total: 0, 
        stand_name: undefined, 
        add_card: false, 
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
            $('#page2 #'+users[idx]+'_data').append('<span class="row">'+fields[name].zh+required_text+'<'+tagname+' class="'+id+'"></'+tagname+'></span><br>');
        }
    }
    $('#page2 .orderer_department').append('<option value="請選擇">請選擇</option>');
    $('#page2 .orderer_school')
        .val('台灣大學')
        .parent().append('台灣大學');

    // load the two companies
    for(var id in companies)
    {
        $('#page1 #company'+id).append('<legend>'+companies[id].name+'</legend><div id="schoolList'+id+'"></div>');
        $('#page1 #schoolList'+id)
            .html(companies[id].schools.join('、'))
            .css('display', 'none');
        $('#page1 #company'+id).append('<table id="productList'+id+'"></table>');
        for(var j in companies[id].products)
        {
            var s = companies[id].products[j];
            $('#page1 #productList'+id).append('<tr><td class="img"><img src="images/'+s.img+'"></td><td class="name">'+s.name+'</td><td class="price">$'+s.price+'</td></tr>');
        }
        $('#page1 #choices').append('<input type="radio" name="company" value="'+id+'" id="radio_'+id+'"><label for="radio_'+id+'">'+companies[id].name+'</label><br>');
    }

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
        $('#page4 #pay_location').append('<input type="radio" name="stand" value="'+stands[i]+'" id="stand_'+i+'"><label for="stand_'+i+'">'+stands[i]+'</label><br>');
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

        var next_texts = [ '', '', '', '', '確認送出', '重新訂購'  ];

        $('input[type=button]:eq(0)')
            .val('上一頁')
            .css('display', curPage===0||curPage===5?'none':'block');
        $('input[type=button]:eq(1)')
            .val(next_texts[curPage]===''?'下一頁':next_texts[curPage]);
    })();

    // define the events of the buttons
    var movePage = function(direction){
        curPage+=direction;
        updatePage();
    };

    // for those doesn't support <input type='number'> (IE)
    var page3_validate_numbers = function(){
        var valid = true;
        $('#page3 table[id^=productList] tr').each(function(idx, item){
            var count_item = $(item).find('.count input');
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
            $('#page3 table[id^=productList] tr').each(function(idx, item){
                var price = parseInt($(item).find('.price').html().substring(1)); // the first character is '$'
                var count_item = $(item).find('.count input');
                var count = parseInt(count_item.val());
                ret_obj.products.push(count);
                ret_obj.total += price*count;
            });
            $('#page3 #total').html(ret_obj.total);
        }
        return ret_obj;
    };

    $('#page1 input[name=company]').on('change', function(e){
        data.company = $('#page1 input[name=company]:checked').val();
        // copy data of selected company to page3
        
        $('#page3 #products').html($('#page1 #catalog').html());
        $('#page3 #company'+(data.company==='A'?'B':'A')).remove();
        $('#page3 #productList'+data.company+' tr').append('<td class="count"><input type="number" min="0" value="0"></td>');
        $('#page3 #products').find('.left, .right').removeClass('left right');
        $('#page3 #products .count input').on('change', function(e){
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

    $('#page1 #add_card').on('change', function(e){
        data.add_card = ($('#page1 #add_card').attr('checked')==='checked')?true:false;
    });

    var verifiers = [];
    verifiers[0] = function(){
        if(typeof companies[data.company]==='object')
        {
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
                        invalid = true;
                        $(item2).parent().css('color', 'red');
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

        if(window.debug)
        {
            invalid = false;
        }
        var msg = '';
        if(invalid)
        {
            msg += '部份資料未填或不完整！\n';
        }
        if(contains_asterisk)
        {
            msg += '內容不能包含星號！\n';
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
            $('#page5 #stand').html(data.stand_name);
            $('#page5 #add_card').html(data.add_card?'是':'否');
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
        if(typeof data.ordered_products !== 'string')
        {
            data.ordered_products = JSON.stringify(data.ordered_products); // make it less
        }
        $.ajax({
            type: 'POST', 
            url: './save.php', 
            data: JSON.parse(JSON.stringify(data)), // clone a temporary version because strings will be escaped
            datatype: 'json', 
            success: function(data, status, xhr){
                if(data.status === 'ok')
                {
                    if(/^[A-G][AB]{2}\d{5}$/.test(data.ID))
                    {
                        $('#page6').append('訂單編號：'+data.ID);
                        movePage(+1);
                    }
                    else
                    {
                        console.log(data);
                    }
                }
                else
                {
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
});

