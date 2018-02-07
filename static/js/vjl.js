var s_time,dealWith = {};
function so(){
    window.location = _so_url+document.getElementById('so').value;
}
function close_video(){
    action = '';
    play_vid = '';
    document.getElementsByClassName('modal_video')[0].style.cssText = 'display:none'
    var videoD = document.getElementById('videos');
    videoD.src = '';
    videoD.pause();
    videoD.load();
    clearInterval(s_time);
}

function login(){
    $.ajax({
        'url':'dologin',
        'type':'post',
        'data':{'pwd':$('[name=pwd]').val(),'id':$('[name=id]').val()},
        //'async':false,
        'success':function(data){
            if(data.status){
                window.location.reload();
                //url = data.result.url;
            }else{
                alert(data.code)
            }
        }
    })
}
var action = '',play_vid = '',video_list = {};
function getVideoUrl(video_id,callbak) {
    if(video_list[video_id] != undefined ){
        play_vid = video_id;
        callbak(video_list[video_id]);
    }
    $.ajax({
        url:'video.php?do=url',
        type:'post',
        data:{video_id:video_id},
//                async:false,
        success:function (res) {
            if(!res.status){
                alert(res.code);
            }else{
                play_vid = video_id;
                video_list[video_id] = res.url;
                callbak(video_list[video_id]);
            }
        },error:function () {}
    });
    //return video_list[video_id];

}
var cdk = '';
function recharge() {
    alert('(注: 当您剩余时间为负数时，不会计算负的时间,例如剩余-5天,充值30天，那么充值后剩余时长为30天。\n当充值剩余时长为正数时则是叠加充值,例如剩余时长为5天,充值30天后则是剩余时长为35天)');
    cdk = prompt('请输入充值卡密进行充值: \n',cdk);
    if(cdk===null){
        return false;
    }

    $.post('recharge.php',{cdk:cdk},function (res) {
        alert(res.code);
        if(res.status){
            window.location.reload();
        }
    })
}

function modifyPass() {
    var o_pwd = prompt('请输入当前密码: ');
    if(o_pwd === null){
        return false;
    }

    var n1_pwd = prompt('请输入新密码: ');
    if(n1_pwd === null){
        return false;
    }
    var n2_pwd = prompt('请再次输入新密码: ');
    if(n2_pwd === null){
        return false;
    }
    if(n1_pwd == ''){
        alert('新密码不能为空!');
        return false;
    }
    if(n1_pwd != n2_pwd){
        alert('两次密码输入不一致!');
        return false;
    }
    loadLoginLock = false;
    $.post('user.php?act=modify_pass',{o_pwd:o_pwd,n1_pwd:n1_pwd},function (res) {
        alert(res.code);
        loadLoginLock = !res.status;
        if(res.status){
            window.location.reload();
        }
    });
}

function delVideo(video_id) {
    if (!confirm('确认要删除该视频吗? ')) {
        return false;
    }
    $.ajax({
        url: 'video.php?do=del',
        type: 'post',
        data: {video_id: video_id},
        success: function (res) {
            alert(res.code);
            if (res.status) {
                window.location.reload();
            }
        }
    });
}
//创建下载视频任务
function createDownTask(video_id) {
    $.ajax({
        url: 'video.php?do=create_download_task',
        type: 'post',
        data: {video_id: video_id},
        success: function (res) {
            alert(res.code);
            /*if (res.status) {
                window.location.reload();
            }*/
        },error:function () {}
    });
}
//加精选
function addDigest(id) {
    $.ajax({
        url: 'video.php?do=digest',
        type: 'post',
        data: {video_id: id},
        success: function (res) {
            alert(res.code);
            if (res.status) {
                window.location.reload();
            }
        },error:function () {}
    });
}
//识别设备类型
var browser = {
    versions:function(){
        var u = navigator.userAgent, app = navigator.appVersion;
        return {
            trident: u.indexOf("Trident") > -1, //IE内核
            presto: u.indexOf("Presto") > -1, //opera内核
            webKit: u.indexOf("AppleWebKit") > -1, //苹果、谷歌内核
            gecko: u.indexOf("Gecko") > -1 && u.indexOf("KHTML") == -1, //火狐内核
            mobile: !!u.match(/AppleWebKit.*Mobile.*/), //是否为移动终端
            ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
            android: u.indexOf("Android") > -1 || u.indexOf("Linux") > -1, //android终端或者uc浏览器
            iPhone: u.indexOf("iPhone") > -1 , //是否为iPhone或者QQHD浏览器
            iPad: u.indexOf("iPad") > -1, //是否iPad
            webApp: u.indexOf("Safari") == -1, //是否web应该程序，没有头部与底部
            wexin:u.indexOf("MicroMessenger") > -1 ,//是否在微信设备
        };
    }(),
    language:(navigator.browserLanguage || navigator.language).toLowerCase()
};

//加载锁
var loadLoginLock = true;
$(function () {
    $("img[data-original]").lazyload({effect: "fadeIn",  threshold: 500,placeholder: 'static/img/lazy.gif'});
    jQuery.ajaxSetup({
        //请求失败遇到异常触发
        error: function () {
            alert('请求失败,请检查网络是否正常!')
        },
        //完成请求后触发。即在success或error触发后触发
        complete: function () {
            //loadingToast.hide();
            $.hideLoading();
        },
        //发送请求前触发
        beforeSend: function () {
            //loadingToast.show();
            $.showLoading();
        }
    });

    if(!_login){
        $('.login').show();
        return false;
    }
    if(!_bind_qq){
        $.post('../api/getqqinfo.php?qq='+_qq,function (res) {
            if(res.status){
                $.each(res.data,function (k,v) {
                    $('.nickname').text(v.nickname);
                })
            }
        })
    }
    //视频播放 click
    $('.video.play_v').click(function () {
        action = 'play';
        var vid = $(this).data('vid'), title = $(this).data('title'),is_stime = $(this).data('is-stime');
        var $videos = $('#videos');
        try{
            //解决手机端触发问题
            $videos[0].play();
        }catch(e){}
        getVideoUrl(vid,function (video_url) {
            $videos[0].src = video_url;
            $videos[0].load();
            $videos[0].play();
            $('.modal_video').show();
            if(is_stime && !dealWith[vid]){
                var callback = function () {
                    if($videos[0].readyState===4){
                        $.ajax({
                            type:'post',
                            url:'video.php?do=set_time',
                            data:{video_id:vid,seconds:$videos[0].duration},
                            async:false,
                            success:function (res) {
                                if(res.status){
                                    dealWith[vid] = 1;
                                    clearInterval(s_time);
                                    $('.v_time_'+vid).text(res.time);
                                }
                            },error:function () {}
                        })
                    }
                };
                s_time = setInterval(callback,5000);
            }
            $('.video_title')[0].innerText=title;
        });
        /*  $('.modal_video').show();$('#videos')[0].src='http://video_src.nafou.top/file/video/da1590833f6f892db4afe8c1ea797fdc.mp4';$('#videos')[0].play()*/
    });
    var time = setInterval(function () {
        if(!loadLoginLock){
            return false;
        }
        $.ajax({
            url: 'load_login.php',
            data: {action: action, vid: play_vid},
            async: false,
            success: function (res) {
               /* action = '';
                play_vid = '';*/
                if (!res.status) {
                    clearInterval(time);
                    alert(res.code);
                    window.location.reload();
                }
            },error:function () {}
        });
    }, 5000);

    $('#ser_id'+_ser_id).addClass('action');
    $('#outLogin').click(function () {
        if(!confirm('确认要注销登录吗？(请不要频繁的注销登录否则后果自负)')){
            return false;
        }
        loadLoginLock = false;
        $.ajax({
            url: 'user.php?do=out_login',
            success: function (res) {
                alert(res.code);
                if (res.status) {
                    window.location.reload();
                }else{
                    loadLoginLock = true;
                }
            }
        });
    });
    if(_bind_qq){
        $('#bind_qq').modal().unbind('click');
    }
    $('#bindqq_btn').click(function () {
        var qq = $('#qq').val();
        var validation = $('#validation').val();
        if(qq == ''){
            alert('请输入QQ号');
            return false;
        }
        if(qq.length<=5 || qq.length>=11) {
            alert("请输入正确的QQ号！");
            return false;
        }
        if(validation == ''){
            alert('请输入验证码');
            return false;
        }
        $.post('user.php?do=bind_qq',{qq:qq,validation:validation},function (res) {
            alert(res.code);
            if(res.status){
                window.location.reload();
            }

        })
    });

    var sendFlag = true;
    $('#send_validation').click(function () {
        var qq = $('#qq').val();
        if(qq == ''){
            alert('请输入QQ号');
            return false;
        }
        if(!sendFlag){
            return false;
        }
        sendFlag = false;
        loadLoginLock = false;
        $.ajax({
            url:'send_var.php',
            data:{qq:qq},
            type:'post',
            // async:false,
            success:function (res) {
                // alert('send success');
                if(!res.status){
                    sendFlag = true;
                    alert(res.code);
                }else{
                    var time = 60;
                    time--;
                    sendFlag = false;
                    $('#send_validation').text(time+'s后再获取');
                    var t = setInterval(function () {
                        time--;
                        if(time < 1){
                            time = 60;
                            sendFlag = true;
                            $('#send_validation').text('发送验证码');
                            clearInterval(t);
                            return false;
                        }
                        $('#send_validation').text(time+'s后再获取');
                    },1000);
                }
            },complete:function () {
                loadLoginLock = true;
                $.hideLoading();
            }
        });


    })

});