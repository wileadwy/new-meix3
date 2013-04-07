//公用方法
var Common = {
    //弹出层
    mask : function ( options ) {
        //默认参数
        var defaults = {
            back : '<a class="mask_return" href="javascript:;"></a>',
            title : '',
            content : '',
            operate : '<a class="link0 js_mask_close" href="javascript:;">取消</a><a class="button0 js_mask_submit" href="javascript:;">确定</a>',
            width : '500px',
            callback : this.maskClose
        };
        //覆盖默认参数
        var settings = $.extend( defaults , options );
        //弹出层基础模板
        var layout ='<div id="mask" class="mask">'+
                        '<div id="mask_bg" class="mask_bg"></div>'+
                        '<div id="mask_wrap" class="mask_wrap js_mask_close">'+
                            '<table id="mask_table"><tr><td id="mask_table_td" class="mask_table_td" align="center">'+
                                '<div id="mask_main" style="width:'+settings.width+';" class="mask_main">'+
                                    '<div class="mask_header"><table><tr><td align="left" class="pl10" width=25%">'+settings.back+'</td><td width="50%" align="center">'+settings.title+'</td><td width="25%" align="right"><a class="mask_close js_mask_close" href="javascript:;"></a></td></tr></table></div>'+
                                    '<div id="mask_content" class="mask_content">'+settings.content+'</div>'+
                                    '<div class="mask_footer">'+settings.operate+'</div>'+
                                '</div>'+
                            '</td></tr></table>'+
                        '</div>'+
                    '</div>';
        //插入弹出层
        $.append($.getTag('body')[0],layout);
        //调整弹出层位置
        var size = function () {
            if($.getId('mask_main').offsetHeight<=document.documentElement.clientHeight){
                $.css($.getId('mask_table_td'),{height:document.documentElement.clientHeight-100}); //控制表格高度需要用td撑开
                $.css($.getId('mask_bg'),{height:document.documentElement.clientHeight});
            }else{
                $.css($.getId('mask_table'),{height:$.getId('mask_table').offsetHeight});
                $.css($.getId('mask_bg'),{height:$.getId('mask_wrap').offsetHeight});
            }
            $.css($.getId('mask'),{height:document.documentElement.clientHeight});
        };
        //获取卷曲高度
        if(document.body.scrollTop){
            var docScrolltop = document.body.scrollTop;
        }else{
            var docScrolltop = document.documentElement.scrollTop;
        }
        //隐藏浏览器滚动条
        if(navigator.userAgent.indexOf("Firefox")>0){
            $.css(document.getElementsByTagName('body')[0],{'overflow':'hidden'});
        }else{
            $.css(document.getElementsByTagName('html')[0],{'overflow':'hidden'});
        }
        //定位弹出层位置
        $.css($.getId('mask'),{top:docScrolltop});
        //调整弹出层位置
        size();
        //监听浏览器大小变化,并改变弹出层位置
        window.onresize = size;
        //绑定EXC关闭
        $.on($.getTag('body')[0],'keyup',function(event){
            var e = event || window.event;
            if(e.keyCode == 27){
                settings.callback();
            }
        });
        //阻止向背景层冒泡
        $.on($.getId('mask_main'),'click',function(event){
            var e = event || window.event;
            $.stopPropagation(e);
        });
        //绑定关闭事件
        $.on($.getClass('js_mask_close'),'click',settings.callback);
    },
    //关闭弹出层
    maskClose : function () {
        if($.getId('mask')!=null){
            //移除弹出层
            $.remove($.getId('mask'));
            //卸载绑定事件
            $.off($.getTag('body')[0],'keyup');
            $.off($.getId('mask_main'),'click');
            window.onresize = null;
            //回复滚动条
            if(navigator.userAgent.indexOf("Firefox")>0){
                $.css(document.getElementsByTagName('body')[0],{'overflow':'auto'});
            }else{
                $.css(document.getElementsByTagName('html')[0],{'overflow':'auto'});
            }
        }
    },
    //警告提示
    alert : function ( options ) {
        var defaults = {
            title : '提示',
            content : '这是传说中的提示内容!',
            callback : Common.maskClose
        };
        var settings = $.extend( defaults , options );
        this.mask({
            back : settings.title,
            content : '<div class="mask_prompt">'+settings.content+'</div>',
            operate : '<a class="button0" id="js_prompt_enter" href="javascript:;">确定</a>',
            width : '300px',
            callback : settings.callback
        });
        $.on($.getId('js_prompt_enter'),'click',settings.callback);
    },
    //确认操作
    confirm : function ( options ) {
        var defaults = {
            title : '提示',
            content : '这是传说中的提示内容!',
            enter_callback : Common.maskClose,
            exit_callback : Common.maskClose
        };
        var settings = $.extend( defaults , options );
        this.mask({
            back : settings.title,
            content : '<div class="mask_prompt">'+settings.content+'</div>',
            operate : '<a class="link0 js_mask_close" href="javascript:;">取消</a><a class="button0" id="js_prompt_enter" href="javascript:;">确定</a>',
            width : '300px',
            callback : settings.exit_callback
        });
        $.on($.getId('js_prompt_enter'),'click',settings.enter_callback);
    },
    //调取模板
    layout : function (title,url,data) {
        $.ajax({
            url : Config['defaultUrl'] + url,
            data : data,
            success : function (data) {
                Common.mask({
                    back : title,
                    content : data['data']
                });
            }
        });
    },
    //操作输入框默认值
    inputDefault : function ( obj , val , cl ) {
        $.on(obj,'focus',function(){
            var value = $.attr(this,val);
            if(this.value == value ){
                this.value = '';
                $.addClass(this,cl);
            }
        });
        $.on(obj,'blur',function(){
            var value = $.attr(this,val);
            if(this.value == '' ){
                this.value = value;
                $.removeClass(this,cl);
            }
        });
    },
    searchTag : function (options) {
        $.on($.getClass('js_search_tag'),'keyup',function(){
            var data = $.getValue('js_search_tag');
            data['notin'] = new Array();
            for(var i = 0 ; i < $.getClass('js_search_tag_select').length ; i ++ ){
                data['notin'][i] = $.attr($.getClass('js_search_tag_select')[i],'data-tag-id');
            };
            data['levelin'] = options['levelin'];
            data['limitnum'] = options['limitnum'];
            data['pagenum'] = options['pagenum'];
            if(data['code']!=''){
                $.ajax({
                    url : Config['defaultUrl'] + '/Tool/tool_search_stock_a',
                    data : data,
                    success : function (data) {
                        if(data['status']){
                            var html = '';
                            for(var k in data['data']){
                                html += '<li class="tag"><a data-tag-id="'+data['data'][k]['idstock']+'" class="js_search_tag_add" href="javascript:;">'+data['data'][k]['showname']+'</a></li>'
                            }
                            $.html($.getClass('js_tag_list')[0],html);
                            $.addClass($.getClass('search_tag_text')[0],'show');
                            $.on($.getClass('js_search_tag_add'),'click',function(){
                                if($.getClass('js_search_tag_select').length>4){
                                    alert('最多选择5个标签!');
                                }else{
                                    $.append($.getClass('js_search_tag_show')[0],'<li class="tag"><a class="js_search_tag_select" data-tag-id="'+$.attr(this,'data-tag-id')+'">'+$.html(this)+'</a><a class="js_search_tag_del" href="javascript:;">X</a></li>')
                                    $.html($.getClass('js_tag_list')[0],' ');
                                    $.on($.getClass('js_search_tag_del'),'click',function(){
                                        $.remove(this.parentNode);
                                    });
                                }
                                $.removeClass($.getClass('search_tag_text')[0],'show');
                                $.getClass('js_search_tag')[0].value = '';
                                $.getClass('js_search_tag')[0].focus();
                            });
                        }else{
                            $.addClass($.getClass('search_tag_text')[0],'show');
                            $.html($.getClass('js_tag_list')[0],'无搜索结果!');
                        }
                    }
                });
            }else{
                $.html($.getClass('js_tag_list')[0],' ');
                $.removeClass($.getClass('search_tag_text')[0],'show');
            }
        });
    },
    //调整价格
    percentage : function () {
        var common = function (fn){
            if($.html($.getClass('js_recommendedStock_now_peice')[0])==''){
                $.getClass('js_recommendedStock_price_text')[0].value = '';
                $.getClass('js_recommendedStock_baifenbi_text')[0].value = '';
                alert('请先选择要推荐的股票');
            }else{
                fn();
            };
        };
        $.on($.getClass('js_recommendedStock_price_text'),'keyup',function(){
            common(function(){
                var a = Number($.html($.getClass('js_recommendedStock_now_peice')[0]));
                var b = Number($.getClass('js_recommendedStock_price_text')[0].value);
                $.getClass('js_recommendedStock_baifenbi_text')[0].value = Math.round((b-a)/a*100);
            });
        });
        $.on($.getClass('js_recommendedStock_baifenbi_text'),'keyup',function(){
            common(function(){
                var a = Number($.html($.getClass('js_recommendedStock_now_peice')[0]));
                var b = Number($.getClass('js_recommendedStock_baifenbi_text')[0].value);
                $.getClass('js_recommendedStock_price_text')[0].value = Math.round(a*(1+b/100) * 100) / 100;
            });
        });
        $.on($.getClass('js_recommendedStock_price_up'),'click',function(){
            common(function(){
                var a = Number($.html($.getClass('js_recommendedStock_now_peice')[0]));
                $.getClass('js_recommendedStock_baifenbi_text')[0].value = Number($.getClass('js_recommendedStock_baifenbi_text')[0].value) + 1;
                var b = Number($.getClass('js_recommendedStock_baifenbi_text')[0].value);
                $.getClass('js_recommendedStock_price_text')[0].value = Math.round(a*(1+b/100) * 100) / 100;
            });
        });
        $.on($.getClass('js_recommendedStock_price_down'),'click',function(){
            common(function(){
                var a = Number($.html($.getClass('js_recommendedStock_now_peice')[0]));
                $.getClass('js_recommendedStock_baifenbi_text')[0].value = Number($.getClass('js_recommendedStock_baifenbi_text')[0].value) - 1;
                var b = Number($.getClass('js_recommendedStock_baifenbi_text')[0].value);
                $.getClass('js_recommendedStock_price_text')[0].value = Math.round(a*(1+b/100) * 100) / 100;
            });
        });
    },
    change : {
        user : function ( options ) {
            $.on($.getClass('js_user_change'),'click',function(){
                $.ajax({
                    url : Config['defaultUrl'] + '/Home/maybe_love_person',
                    data : options,
                    success : function (data) {
                        if(data['status']==1){
                            var html = '';
                            for(var i = 0 ; i < data['data'].length ; i ++ ){
                                html += '<div class="sidebar_user_list_box clearfix"><div class="sidebar_user_list_img"><a href="'+Config['defaultUrl']+'/User/index/id/'+data['data'][i]['iduser']+'"><img src="'+data['data'][i]['avatar']+'" width="54" height="54" /></a></div><div class="sidebar_user_list_info"><div class="sidebar_user_list_info_title"><a href="'+Config['defaultUrl']+'/User/index/id/'+data['data'][i]['iduser']+'">'+data['data'][i]['name']+'</a></div><div class="sidebar_user_list_info_other">'+data['data'][i]['rate']+'准确率<span>|</span>'+data['data'][i]['attention']+'人关注</div></div></div>';
                            }
                            $.html($.getClass('js_change_user_list')[0],html);
                        }
                    }
                });
            });
        },
        group : function ( options ) {
            $.on($.getClass('js_group_change'),'click',function(){
                $.ajax({
                    url : Config['defaultUrl'] + '/Home/maybe_love_groups',
                    data : options,
                    success : function (data) {
                        if(data['status']==1){
                            var html = '';
                            for(var i = 0 ; i < data['data'].length ; i ++ ){
                                var list = '';
                                var num = 4;
                                for(var j = 0 ; j < data['data'][i]['User'].length ; j ++ ){
                                    list += '<li><img src="'+data['data'][i]['User'][j]['avatar']+'" /></li>'
                                    if(j>4){
                                        num = 9;
                                    }else if(j>8){
                                        break;
                                    }
                                }
                                html += '<div class="sidebar_group_list_box clearfix"><div class="sidebar_group_list_img img'+num+'"><a href="'+Config['defaultUrl']+'/Groups/discuess/gid/'+data['data'][i]['user_id']+'"><ul class="clearfix">'+list+'</ul></a></div><div class="sidebar_group_list_info"><div class="sidebar_group_list_info_title"><a href="'+Config['defaultUrl']+'/Groups/discuess/gid/'+data['data'][i]['user_id']+'">'+data['data'][i]['name']+'</a></div><div class="sidebar_group_list_info_other">'+data['data'][i]['join']+'人参与</div><div class="sidebar_group_list_info_other">'+data['data'][i]['rate']+'准确率<span>|</span>'+data['data'][i]['attention']+'人关注</div></div></div>'
                            }
                            $.html($.getClass('js_change_group_list')[0],html);
                        }
                    }
                });
            });
        }
    },
    tagSearch : function ( options ) {
        $.on($.getClass('js_search'),'keyup',function(){
            var defaults = {
                levelin : '',//控制分类
                limitnum : '',//个数
                pagenum : '',//页码
                type : '',//类型(沪深)
                url : ''
            };
            var settings = $.extend(defaults,options);
            settings['code'] = this.value;
            if(settings['code']!=''){
                $.ajax({
                    url : Config['defaultUrl'] + '/Tool/tool_search_stock_a',
                    data : settings,
                    success : function (data) {
                        if(data['status']==1){
                            var html = '';
                            for(var k in data['data']){
                                html += '<li class="tag"><a href="'+settings['url']+'/sid/'+data['data'][k]['idstock']+'">'+data['data'][k]['showname']+'</a></li>'
                            }
                            $.html($.getClass('search_results')[0],'<div class="tag_list"><ul class="clearfix">'+html+'</ul></div>');
                            $.addClass($.getClass('search_results')[0],'show');
                        }else{
                            $.addClass($.getClass('search_results')[0],'show');
                            $.html($.getClass('search_results')[0],'无搜索结果!');
                        }
                    }
                });
            }else{
                $.html($.getClass('search_results')[0],' ');
                $.removeClass($.getClass('search_results')[0],'show');
            }
        });
    },
    groupApply : function () {
        $.on($.getClass('js_group_apply'),'click',function(){
            var json = {'gid':$.attr(this,'data-group-id')};
            $.ajax({
                url : Config['defaultUrl'] + '/Groups/application_groups_a',
                data : json,
                success : function (data) {
                    if(data['status']==1){
                        alert('申请成功!');
                    }else{
                        alert(data['info']);
                    }
                }
            });
        });
    },
    groupAttention : function () {
        $.on($.getClass('js_group_attention'),'click',function(){
            var json = {'table':'groups','id':$.attr(this,'data-group-id')}
            $.ajax({
                url : Config['defaultUrl'] + '/Tool/attention_a',
                data : json,
                context : this,
                success : function (data) {
                    if(data['status']==1){
                        $.html(this.context,data['info']);
                    }
                }
            });
        });
    },
    group : function ( url , name , id ) {
        $.ajax({
            url : Config['defaultUrl'] + '/Theme/groups_view',
            data : {'groups_id':id},
            success : function (data) {
                Common.mask({
                    title : name,
                    width : '550px',
                    operate : '<a class="link0" href="'+url+'">点击进入主页 >></a>',
                    content : data['data']
                });
            }
        });
    }
};