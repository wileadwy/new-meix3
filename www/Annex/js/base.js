/*
    name : Dragon
    version : 3.0
    developer : Dragon Huang
    time : 2013-1-10
    email : 29392959@qq.com
*/
(function(window){
    var Dragon = function () {};
    Dragon.prototype = {
        //判断函数
        isFunction : function ( obj ) {
            return '[object Function]' == Object.prototype.toString.call(obj);
        },
        //判断数组
        isArray : function ( obj ) {
            return obj != null && typeof obj == "object" &&  'splice' in obj && 'join' in obj;
        },
        //判断对象
        isObject : function ( obj ) {
            return typeof obj === "object";
        },
        isEmail : function (text) {
            return /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/.test(text);
        },
        //字符串转json
        evalJSON : function( text ) {
            return eval("(" + text + ")");
        },
        //字符串去空格
        trim : function ( text ) {
            return text == null ? "" : text.toString().replace(/(^\s*)|(\s*$)/g, "" );
        },
        //判断数组中是否含有某项
        contains : function ( arr , text ){ //参数(数组,字符串)
            var flag = false;
            for(var i = 0 ; i < arr.length ; i ++ ){
                if(arr[i]==text){
                    flag = true;
                };
            };
            return flag;
        },
        //遍历执行
        each : function ( arr , fn ) { //参数(数组,字符串)
            for(var i = 0 ; i < arr.length ; i ++ ) {
                arr[i].index = i;
                fn(arr[i]);
            };
        },
        //对象继承,覆盖
        extend : function ( defaults , options ) {  //参数(默认对象,修改对象)
            for(var k in options) {
                defaults[k] = options[k];
            };
            return defaults;
        },
        //class选择器
        getClass : function ( text ) {
            var dom = document.getElementsByTagName("*");
            var arr = [];
            for(var i = 0; i < dom.length; i++){
                var str = dom[i].className.toString().split(' ');
                for(var j = 0 ; j < str.length ; j++){
                    if(str[j]==text){
                        arr.push(dom[i]);
                    };
                };
            };
            arr.thisClass = text;
            return arr;
        },
        //id选择器
        getId : function ( text ) {
            return document.getElementById(text);
        },
        //标签选择器
        getTag : function ( text ) {
            var _arr = document.getElementsByTagName(text);
            var arr = [];
            for(var i = 0 ; i < _arr.length ; i ++ ){
                arr.push(_arr[i])
            };
            return arr;
        },
        //name选择器
        getName : function ( text ) {
            var _arr = document.getElementsByTagName('*');
            var arr = [];
            for(var i = 0 ; i < _arr.length ; i ++ ){
                if(_arr[i].name==text){
                    arr.push(dom[i]);
                };
            };
            return arr;
        },
        //获取上层指定节点
        parents : function ( obj , text ) {
            var $this = this;
            var parent = '';
            var fn = function ( obj , text ) {
                if(obj.parentNode.className!=''&&obj.parentNode.className!=undefined){
                    var cl = obj.parentNode.className.toString().split(' ');
                    if($this.contains(cl,text)){
                        parent = obj.parentNode;
                    }else{
                        parent = obj.parentNode;
                        fn( parent , text );
                    }
                }
            };
            fn( obj , text );
            return parent;
        },
        //子集选择器
        children : function ( obj , cl ) {
            var arr = [];
            var list = obj.childNodes;
            for(i = 0 ; i < list.length ; i++ ){
                if(list[i].className!=''&&list[i].className!=undefined){
                    var arr_cl = list[i].className.toString().split(' ');
                    if(list[i].nodeType===1&&this.contains(arr_cl,cl)){
                        arr.push(list[i]);
                    };
                }
            };
            return arr;
        },
        //兄弟选择器
        siblings : function ( obj ) {
            var list = this.children(obj.parentNode);
            var arr = [];
            for( var i = 0 ; i < list.length ; i++ ){
                if(list[i]!=obj){
                    arr.push(list[i]);
                };
            };
            return arr;
        },
        //获取元素索引值
        index : function ( arr , obj ) {
            for(i = 0 ; i < arr.length ; i++ ){
                if(arr[i]==obj){
                    return i;
                };
            };
        },
        //添加class
        addClass : function ( obj , text ) {
            if(!this.contains( obj.className.split(' ') , text )){
                obj.className = this.trim(obj.className+' '+text);
            };
        },
        //删除class
        removeClass : function ( obj , text ) {
            var arr = this.trim(obj.className).split(' ');
            for(var i = 0 ; i < arr.length ; i ++ ){
                if(arr[i]==text){
                    delete arr[i];
                };
            };
            obj.className = arr.join(' ');
        },
        //已有class
        hasClass : function ( obj , text ){
            var flag = false;
            if(obj.className!=''&&obj.className!=undefined){
                var str = obj.className.split(' ');
                for(var i = 0 ; i < str.length ; i++){
                    if(str[i]==text){
                        flag = true;
                    };
                };
            }
            return flag;
        },
        //设置css
        css : function ( obj , json ) {
            for(var k in json){
                if(!isNaN(json[k])){
                    json[k] = json[k]+'px';
                };
                obj.style[k] = json[k];
            };
        },
        //获取鼠标坐标
        position : function ( e ) {
            if(e.pageX || e.pageY){ 
                return {left:e.pageX, top:e.pageY};
            };
            return { 
                left:e.clientX + document.documentElement.scrollLeft - document.body.clientLeft,
                top:e.clientY + document.documentElement.scrollTop  - document.body.clientTop
            }; 
        },
        //动画方法
        animate : function (obj,options,time,callback) {
            var f = 0;
            var j = 0; //计数器
            var common = {
                tween : function(time,defaultValue,settingValue,defaultTmie){
                    return -settingValue*(time/=defaultTmie)*(time-2) + defaultValue;
                },
                execution : function (key,value,time) {
                    var getTime=(new Date()).getTime();
                    var defaultTmie = time || 500;
                    //获取元素默认值
                    if(window.getComputedStyle) {
                        var defaultValue = parseInt(window.getComputedStyle(obj, null)[key]) || 0;
                    }else{
                        var defaultValue = parseInt(obj.currentStyle[k]) || 0;
                    }
                    var settingValue = value - defaultValue;
                    var fn=function(){
                        var settingTime=(new Date()).getTime()-getTime;
                        if(settingTime>defaultTmie){
                            settingTime=defaultTmie;
                            obj.style[key]=common.tween(settingTime,defaultValue,settingValue,defaultTmie)+'px';
                            if(++f==j && callback){callback.apply(obj)}
                            return true;
                        };
                        obj.style[key]=common.tween(settingTime,defaultValue,settingValue,defaultTmie)+'px';
                        setTimeout(fn,10);
                    };
                    fn();
                }
            };
            for(var k in options){
                j++;
                common.execution(k,parseInt(options[k]),time);
            };
        },
        //获取,设置html
        html : function ( obj , text ) {
            if(text){
                obj.innerHTML = text;
                if(obj.getElementsByTagName("script")[0]){
                    eval(obj.getElementsByTagName("script")[0].innerHTML);
                }
            }else{
                return obj.innerHTML;
            };
        },
        //获取,设置属性值
        attr : function ( obj , type , text ) {
            if(obj){
                if(text){
                    obj.setAttribute(type,text);
                }else{
                    return obj.getAttribute(type);
                };
            }
        },
        getValue : function ( text ) {
            var json = {};
            var dom = this.getClass(text);
            for(var i = 0 ; i < dom.length ; i ++ ){
                json[this.attr(dom[i],'name')] = dom[i].value;
            }
            return json;
        },
        //在某节点后插入
        append : function ( obj , text ) {
            var dom = document.createElement('div');
            dom.innerHTML = text;
            var len = dom.childNodes.length;
            for( var i = 0 ; i < len ; i ++ ){
                if(document.all){
                    i = 0;
                }
                if(!dom.childNodes[i]){
                    return false;
                }
                if(dom.childNodes[i].nodeType===1){
                    var _obj = dom.childNodes[i].getElementsByTagName("script")[0];
                    obj.appendChild(dom.childNodes[i]);
                    if(_obj){
                        eval(_obj.innerHTML);
                    }
                }
            }
        },
        //在某节点前插入
        prepend : function ( obj , text ) {
            var dom = document.createElement('div');
            dom.innerHTML = text;
            var len = dom.childNodes.length;
            for ( var i = 0 ; i < len ; i ++ ) {
                if(document.all){
                    i = 0;
                }
                if(!dom.childNodes[i]){
                    return false;
                }
                if(dom.childNodes[i].nodeType===1){
                    var _obj = dom.childNodes[i].getElementsByTagName("script")[0];
                    obj.insertBefore(dom.childNodes[i],obj.childNodes[i]);
                    if(_obj){
                        eval(_obj.innerHTML);
                    }
                }
            }
        },
        //用对象,html替换
        replaceWith : function ( obj , text ) {
            if(this.isObject(text)){
                obj.parentNode.replaceChild(text,obj)
            }else{
                var dom = document.createElement('div');
                dom.innerHTML = text;
                var _obj = dom.childNodes[0].getElementsByTagName("script")[0];
                obj.parentNode.replaceChild(dom.childNodes[0],obj);
                if(_obj){
                    eval(_obj.innerHTML);
                }
            };
        },
        //克隆节点文本或节点对象
        clone : function ( obj ) {
            return obj.cloneNode();
        },
        //删除节点
        remove : function ( obj ) {
            obj.parentNode.removeChild(obj);
        },
        //事件绑定
        on : function ( target , event , fn ) {
            if(target==undefined||target==''){
                return false;
            };
            var typeRef = '_' + event;
            var _fn = function (_target) {
                if(_target.attachEvent){
                    if(!_target[typeRef]){
                        _target[typeRef] = [];
                    }
                    for(var k in _target[typeRef]){
                        if(_target[typeRef][k] == fn){
                            return;
                        }
                    }
                    _target[typeRef].push(fn);
                    _target['on'+event] = function(){
                        for(var k in this[typeRef]){
                            if(_target[typeRef]){
                                _target[typeRef][k].apply(this,arguments);
                            }
                        }
                    }
                }else{
                    _target.addEventListener(event,fn,false);
                    _target[typeRef] = fn;  //存储响应事件
                };
            };
            if(this.isArray(target)){
                for(var i = 0 ; i < target.length ; i ++ ){
                    _fn(target[i]);
                };
            }else{
                _fn(target);
            };
        },
        //事件移除
        off : function ( target , event ) {
            if(target==undefined||target==''){
                return false;
            };
            var typeRef = '_' + event;
            if(target[typeRef]){
                if(target.detachEvent){
                    target[typeRef] = null;
                }else{
                    target.removeEventListener(event,target[typeRef],false);
                };
            };
        },
        //动态绑定事件
        live : function ( target , event , fn ) {
            var $this = this;
            this.on(document,event,function(event){
                var e = event || window.event;
                var elem = e.srcElement || e.target;
                var arr = $this.getClass(target.thisClass);
                if($this.contains(arr,elem)){
                    fn.apply(elem,arguments);
                };
            });
        },
        //监听回车事件
        isEnter : function ( obj , fn ) {
            this.on(obj,'keyup',function(event){
                var e = event || window.event;
                if(e.keyCode == 13){
                    fn();
                }
            });
        },
        //阻止浏览器默认事件
        stopDefault : function (e) {
            if ( e && e.preventDefault ){
                e.preventDefault();
            }else{
                window.event.returnValue = false;
            };
        },
        //阻止冒泡
        stopPropagation : function (e) {
            if ( e && e.stopPropagation ) {
                e.stopPropagation();
            }else{
                window.event.cancelBubble = true;
            };
        },
        //设置ajax默认值
        ajaxSetup : {
            url : location.href,  //默认URL地址
            type : 'POST',  //默认提交方式
            async : true,  //默认开启异步请求
            data : {},  //提交参数对象
            dataType : 'json',  //成功返回的文本类型
            success : function (data) {}  //请求成功后执行
        },
        //ajax方法
        ajax : function ( options ) {
            var $this = this;
            var settings = this.extend( this.ajaxSetup , options );
            var data = '';
            if(http!=null){
                http.abort();
            };
            if(window.XMLHttpRequest){
                var http = new XMLHttpRequest();
            }else if(window.ActiveXObject){
                var activexName = ['MSXML2.XMLHTTP','Microsoft.XMLHTTP'];
                for(var i = 0; i<activexName.length;i++){
                    try{
                        var http = new ActiveXObject(activexName[i]);
                        break;
                    } catch (e) {
                        continue;
                    };
                };
            };
            http.onreadystatechange = function () {
                if(http.readyState === 4){
                    if(http.status >= 200 && http.status < 300){
                        var responseText;
                        switch(settings.dataType){
                            case 'html':
                                responseText = http.responseText;
                                break;
                            case 'xml':
                                responseText = http.responseXML;
                                break;
                            case 'json':
                                responseText = $this.evalJSON(http.responseText);
                                break;
                        }
                        settings.success(responseText);
                    }
                };
            };
            http.open(settings.type,settings.url,settings.async);
            if(settings.type=='POST'){
                http.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            };
            http.setRequestHeader('X-Requested-With','XMLHttpRequest');
            for(var k in settings.data){
                data += k+'='+settings.data[k]+'&';
            };
            data = data.substring(0,data.length-1);
            http.send(data);
        }
    };
    window.$ = new Dragon();
}(window));