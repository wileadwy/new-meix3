//全局方法
var Global = {
    menu : function () {
        var time = {};
        $.on($.getClass('header_user'),'mouseover',function(){
            $.addClass(this,'show');
        });
        $.on($.getClass('header_user'),'mouseout',function(){
            $.removeClass(this,'show');
        });
    },
    polling : function () {
        
    },
    search : function () {
        $.on($.getClass('js_search'),'focus',function(){
            $.addClass(this,'focus');
        });
        $.on($.getClass('js_search'),'blur',function(){
            if(this.value==''){
                $.removeClass(this,'focus');
            }
        });
    },
    select : function () {
        $.on($.getClass('js_select'),'mouseover',function(){
            $.addClass(this,'show');
        });
        $.on($.getClass('js_select'),'mouseout',function(){
            $.removeClass(this,'show');
        });
    },
    textarea : function () {
        $.live($.getClass('js_textarea'),'keyup',function(){
            var textarea = this;
            var cols = textarea.cols; 
            var str = textarea.value; 
            var lines;
            var chars = 0;
            str = str.replace(/ ?/, " "); 
            for(var i = 0 ; i < str.length ; i ++ ){
                var strTemp = str.charAt(i);
                if(strTemp.match(/[\x00-\x80]/)){
                    chars += 1;
                }else{
                    chars += 2;
                };
            };
            if(window.getComputedStyle) {
                var defaultValue = parseInt(window.getComputedStyle(textarea, null)['width']) || 0;
            }else{
                var defaultValue = parseInt(textarea.currentStyle['width']) || 0;
            }
            textarea.setAttribute("rows", Math.ceil(chars/(defaultValue / 7))); 
        });
    },
    getMessage : function () {
        var common = {
            add : function ( options , obj ) {
                $.ajax({
                    url : Config['defaultUrl'] + '/Theme/list_message_w',
                    data : options,
                    context : obj,
                    success : function (data) {
                        if(data['status']==1){
                            $.html($.children($.parents(this.context,'content_list_right'),'js_message_list_wrap')[0],data['data']);
                            $.addClass($.children($.parents(this.context,'content_list_right'),'js_message_list_wrap')[0],'show');
                        }
                    }
                });
            },
            remove : function ( obj ) {
                $.html($.children($.parents(obj,'content_list_right'),'js_message_list_wrap')[0],' ');
                $.removeClass($.children($.parents(obj,'content_list_right'),'js_message_list_wrap')[0],'show');
            }
        };
        $.live($.getClass('js_viewpoint_message'),'click',function(){
            if(!$.hasClass($.children($.parents(this,'content_list_right'),'js_message_list_wrap')[0],'show')){
                common.add({'table':'point_view','id':$.attr(this,'data-message-id')},this);
            }else{
                common.remove(this);
            }
        });
        $.live($.getClass('js_recstocks_message'),'click',function(){
            if(!$.hasClass($.children($.parents(this,'content_list_right'),'js_message_list_wrap')[0],'show')){
                common.add({'table':'rec_stocks','id':$.attr(this,'data-message-id')},this);
            }else{
                common.remove(this);
            }
        });
    },
    upDown : function () {
        var common = function ( options , obj ) {
            $.ajax({
                url : Config['defaultUrl'] + '/Tool/toporpoor_a',
                data : options,
                context : obj,
                success : function (data) {
                    if(data['status']==1){
                        if(options['toporpoor']=='top'){
                            $.html(this.context,'顶('+data['data']+')');
                        }else if(options['toporpoor']=='poor'){
                            $.html(this.context,'踩('+data['data']+')');
                        }
                    }
                }
            });
        };
        $.live($.getClass('js_viewpoint_up'),'click',function(){
            common({'table':'point_view','id':$.attr(this,'data-viewpoint-id'),'toporpoor':'top'},this);
        });
        $.live($.getClass('js_viewpoint_down'),'click',function(){
            common({'table':'point_view','id':$.attr(this,'data-viewpoint-id'),'toporpoor':'poor'},this);
        });
        $.live($.getClass('js_recstocks_up'),'click',function(){
            common({'table':'rec_stocks','id':$.attr(this,'data-recstocks-id'),'toporpoor':'top'},this);
        });
        $.live($.getClass('js_recstocks_down'),'click',function(){
            common({'table':'rec_stocks','id':$.attr(this,'data-recstocks-id'),'toporpoor':'poor'},this);
        });
        $.live($.getClass('js_message_up'),'click',function(){
            common({'table':'message','id':$.attr(this,'data-message-id'),'toporpoor':'top'},this);
        });
        $.live($.getClass('js_message_down'),'click',function(){
            common({'table':'message','id':$.attr(this,'data-message-id'),'toporpoor':'poor'},this);
        });
    },
    favorite : function () {
        var common = function ( options , obj ) {
            $.ajax({
                url : Config['defaultUrl'] + '/Tool/favorite_a',
                data : options,
                context : obj,
                success : function (data) {
                    if(data['status']==1){
                        $.html(this.context,data['info']+'('+data['data']+')');
                    }
                }
            });
        };
        $.live($.getClass('js_viewpoint_favorite'),'click',function(){
            common({'table':'point_view','id':$.attr(this,'data-viewpoint-id')},this);
        });
        $.live($.getClass('js_recstocks_favorite'),'click',function(){
            common({'table':'rec_stocks','id':$.attr(this,'data-recstocks-id')},this);
        });
    },
    attention : function () {
        $.on($.getClass('js_attention_person'),'click',function(){
            var json = {'table':'user','id':$.attr(this,'data-user-id')}
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
    letter : function () {
        $.live($.getClass('js_letter_add'),'click',function(){
            Common.layout('发私信','/Theme/index',{'theme':'letter_add','name':$.attr(this,'data-user-name'),'id':$.attr(this,'data-user-id')});
        });
    },
    showall : function () {
        $.live($.getClass('js_content_list_text_all'),'click',function(){
            $.addClass($.parents(this,'content_list_text'),'show');
        });
        $.live($.getClass('js_content_list_text_less'),'click',function(){
            $.removeClass($.parents(this,'content_list_text'),'show');
        });
    },
    getNum : function () {
        $.ajax({
            url : Config['defaultUrl'] + '/Tool/letternotice_a',
            success : function (data) {
                if(data['status']==1){
                    if(data['data']['notice']!='0'&&data['data']['notice']!=null){
                        $.html($.getClass('js_notice_num')[0],'提示<span>'+data['data']['notice']+'</span>');
                    }
                    if(data['data']['letter']!='0'&&data['data']['letter']!=null){
                        $.html($.getClass('js_letter_num')[0],'私信<span>'+data['data']['letter']+'</span>');
                    }
                    if(data['data']['notice']&&data['data']['letter']){
                        var num = Number(data['data']['notice'])+Number(data['data']['letter']);
                        $.html($.getClass('header_user_name_num')[0],num);
                        if(num!=0){
                            $.addClass($.getClass('header_user_name_num')[0],'show');
                        }
                    }
                }
            }
        });
    }
};
//调用全局方法
Global.menu(); //用户菜单
Global.search(); //搜索框
Global.select(); //选择框
Global.textarea(); //输入框自适应高度
Global.getMessage(); //获取留言列表
Global.upDown(); //顶踩
Global.favorite(); //收藏
Global.attention(); //关注人
Global.letter(); //发私信
Global.polling(); //轮询
Global.showall(); //显示全部
Global.getNum(); //获得消息数
$.on($.getClass('headerWrap'),'dblclick',function(){
    if(document.body.scrollTop){
        var docScrolltop = document.body.scrollTop;
    }else{
        var docScrolltop = document.documentElement.scrollTop;
    }
    var i = docScrolltop;
    var t = setInterval(function(){
        if(i<0){
            clearInterval(t);
        }
        window.scrollTo(0,i);
        i = i - 200;
    },1);
    
});