<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>wait...</title>
<style type="text/css">
    * {word-break:break-all;font-family:Verdana,Arial;-webkit-text-size-adjust:none;margin:0px;padding:0px;}/*"Microsoft Yahei",*/
    body {margin:0;background:#efefef;}
    form,input,select,textarea,td,th {font-size:12px;}
    img {border:none;}
    ul li {list-style-type:none;}
    ol li {list-style-type:decimal;}
    ul,form {margin:0px;padding:0px;}

    a:hover {text-decoration: underline;}
    .main_box{width:88%;max-width:400px; min-width:320px;background:#fff; padding:30px 5px;margin:200px auto;box-shadow:0px 3px 4px 0px #e3e3e3;border-radius:5px;}
    .msg_box_success{width:100%;text-align:center;margin:30px auto 20px;border:none;color:#009688;}
    .msg_box_error{width:100%;text-align:center;margin:30px auto 20px;border:none;color:#FF5722;}
    .face_box{width:130px;height:130px;margin:5px auto;display:block;}
    .font_box{font-size:16px; color:#333; text-align:center;margin:10px auto;width:100%;}
    .a_success{text-decoration: none;color:#009688;}
    .a_success:link,.a_success:visited,.a_success:active {text-decoration: none;color:#009688;}

    .a_error{text-decoration: none;color:#FF5722;}
    .a_error:link,.a_error:visited,.a_error:active {text-decoration: none;color:#FF5722;}
</style>
</head>
<body>
<div class="main_box">
        {if $code eq 1}
            <div class="face_box">
            <svg xmlns="http://www.w3.org/2000/svg" style="height:100%;width:100%; color: #009688;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>


           
            </div>  
            <div class="msg_box_success"><?php echo(strip_tags($msg));?></div>
        {else}
            <div class="face_box">
            
                <svg xmlns="http://www.w3.org/2000/svg" style="height:100%;width:100%; color: #FF5722;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="msg_box_error"><?php echo(strip_tags($msg));?></div>
        {/if}

        <div class="font_box">
             <a id="href" {if $code eq 1}class="a_success"{else}class="a_error"{/if} href="<?php echo($url);?>">Go Home</a>, Please wait: <b id="wait"><?php echo($wait);?></b>
        </div>
</div>
<script type="text/javascript">
    (function(){
        var wait = document.getElementById('wait'),
            href = document.getElementById('href').href;
        var interval = setInterval(function(){
            var time = --wait.innerHTML;
            if(time <= 0) {
                location.href = href;
                clearInterval(interval);
            };
        }, 1000);
    })();
</script>
</body>
</html>