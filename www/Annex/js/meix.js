(function(window,document){
    var MeiX = {
        //id选择器
        getId : function ( text ) {
            return document.getElementById(text);
        },
        //事件绑定
        on : function ( target , event , fn ) {
            if(target==undefined||target==''){
                return false;
            };
            var typeRef = '_' + event;
            if(target.attachEvent){
                if(target[typeRef] == fn){
                    return false;
                };
                target[typeRef] = fn;  //存储响应事件
                target['on'+event] = function(){
                    if(this[typeRef]){
                        this[typeRef].apply(this,arguments);  //将this从window指向该绑定函数
                    };
                };
            }else{
                target.addEventListener(event,fn,false);
                target[typeRef] = fn;  //存储响应事件
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
        //获取文本
        getSelectedText : function () {
            if (window.getSelection) {
                return window.getSelection().toString();  
            }else if (document.getSelection) {
                return document.getSelection();  
            }else if (document.selection) { 
                return document.selection.createRange().text;  
            }
        }
    };
    MeiX.on(document,'mouseup',function(event){
        var e = event || window.event;
        var text = MeiX.getSelectedText();
        var btn = MeiX.getId('MeiX_button');
        var url = MeiX.getId('MeiX').src.replace('/Annex/js/meix.js','');
        if(text!=''&&!btn){
            var div = document.createElement('div');
            div.innerHTML = '<a id="MeiX_button" target="_blank" href="'+url+'/index.php/Api/me/txt/'+text+'" style="z-index:9999; background:#488FCE; color:#fff; padding: 0 10px; border-radius: 5px; line-height:35px; display:inline-block; position: absolute; top:'+MeiX.position(e).top+'px; left:'+MeiX.position(e).left+'px;">采集到美市</a>';
            document.getElementsByTagName('body')[0].appendChild(div.childNodes[0]);
        }else if(text==''&&btn){
            btn.parentNode.removeChild(btn);
        };
    });
}(window,document));