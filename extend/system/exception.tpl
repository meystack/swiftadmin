<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>系统发生错误</title>
<meta name="robots" content="noindex,nofollow" />
<style>
body{color:#333;margin:0;padding:0 20px 20px;min-height:100%;background:#edf1f4;text-rendering:optimizeLegibility;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;font-family:"Helvetica Neue",Helvetica,"PingFang SC","Hiragino Sans GB","Microsoft YaHei",微软雅黑,Arial,sans-serif}
h1{margin:10px 0 0;font-size:28px;font-weight:500;line-height:32px}
h2{color:#4288ce;font-weight:400;padding:6px 0;margin:6px 0 0;font-size:18px;border-bottom:1px solid #eee}
h3{margin:12px;font-size:16px;font-weight:bold}
abbr{cursor:help;text-decoration:underline;text-decoration-style:dotted}
a{color:#868686;cursor:pointer}
a:hover{text-decoration:underline}
.line-error{background:#f8cbcb}
.echo table{width:100%}
.echo pre{padding:16px;overflow:auto;font-size:85%;line-height:1.45;background-color:#f7f7f7;border:0;border-radius:3px;font-family:Consolas,"Liberation Mono",Menlo,Courier,monospace}
.echo pre > pre{padding:0;margin:0}
.exception{margin-top:20px}
.exception .message{padding:12px;border:1px solid #ddd;border-bottom:0 none;line-height:18px;font-size:16px;border-top-left-radius:4px;border-top-right-radius:4px;font-family:Consolas,"Liberation Mono",Courier,Verdana,"微软雅黑",serif}
.exception .code{float:left;text-align:center;color:#fff;margin-right:12px;padding:16px;border-radius:4px;background:#999}
.exception .source-code{padding:6px;border:1px solid #ddd;background:#f9f9f9;overflow-x:auto}
.exception .source-code pre{margin:0}
.exception .source-code pre ol{margin:0;color:#4288ce;display:inline-block;min-width:100%;box-sizing:border-box;font-size:14px;font-family:"Century Gothic",Consolas,"Liberation Mono",Courier,Verdana,serif;padding-left:<?php echo (isset($source) && !empty($source)) ? parse_padding($source):40;?>px}
.exception .source-code pre li{border-left:1px solid #ddd;height:18px;line-height:18px}
.exception .source-code pre code{color:#333;height:100%;display:inline-block;border-left:1px solid #fff;font-size:14px;font-family:Consolas,"Liberation Mono",Courier,Verdana,"微软雅黑",serif}
.exception .trace{padding:6px;border:1px solid #ddd;border-top:0 none;line-height:16px;font-size:14px;font-family:Consolas,"Liberation Mono",Courier,Verdana,"微软雅黑",serif}
.exception .trace h2:hover{text-decoration:underline;cursor:pointer}
.exception .trace ol{margin:12px}
.exception .trace ol li{padding:2px 4px}
.exception div:last-child{border-bottom-left-radius:4px;border-bottom-right-radius:4px}
.exception-var table{width:100%;margin:12px 0;box-sizing:border-box;table-layout:fixed;word-wrap:break-word}
.exception-var table caption{text-align:left;font-size:16px;font-weight:bold;padding:6px 0}
.exception-var table caption small{font-weight:300;display:inline-block;margin-left:10px;color:#ccc}
.exception-var table tbody{font-size:13px;font-family:Consolas,"Liberation Mono",Courier,"微软雅黑",serif}
.exception-var table td{padding:0 6px;vertical-align:top;word-break:break-all}
.exception-var table td:first-child{width:28%;font-weight:bold;white-space:nowrap}
.exception-var table td pre{margin:0}
.copyright{margin-top:24px;padding:12px 0;border-top:1px solid #eee}
pre.prettyprint .pln{color:#000}
pre.prettyprint .str{color:#080}
pre.prettyprint .kwd{color:#008}
pre.prettyprint .com{color:#800}
pre.prettyprint .typ{color:#606}
pre.prettyprint .lit{color:#066}
pre.prettyprint .pun,pre.prettyprint .opn,pre.prettyprint .clo{color:#660}
pre.prettyprint .tag{color:#008}
pre.prettyprint .atn{color:#606}
pre.prettyprint .atv{color:#080}
pre.prettyprint .dec,pre.prettyprint .var{color:#606}
pre.prettyprint .fun{color:red}
.exception-container{border-radius:5px;text-align:center;box-shadow:0 0 30px rgba(99,99,99,0.06);padding:50px;background-color:#fff;width:100%;left:50%;top:50%;max-width:456px;position:absolute;margin-top:-280px;margin-left:-280px}
.exception-container .head-line{transition:color .2s linear;font-size:40px;line-height:60px;letter-spacing:-1px;color:#777}
.exception-container .subheader{transition:color .2s linear;font-size:26px;line-height:46px;color:#494949}
.exception-container .hr{height:1px;background-color:#eee;width:80%;max-width:350px;margin:23px auto}
.exception-container .context{transition:color .2s linear;font-size:16px;line-height:27px;color:#aaa}
.exception-container .buttons-container{margin-top:35px;overflow:hidden}
.exception-container .buttons-container a{transition:text-indent .2s ease-out,color .2s linear,background-color .2s linear;text-indent:0px;font-size:14px;text-transform:uppercase;text-decoration:none;color:#fff;background-color:#1890ff;border-radius:10px;padding:10px 10px;text-align:center;display:inline-block;overflow:hidden;position:relative;width:40%;margin:0px 8px 0px 8px}
.status-ico{width:72px;height:72px;line-height:72px;font-size:42px;color:#fff;text-align:center;border-radius:50%;display:inline-block;margin-bottom:24px;background-color:#52c41a!important}
.status-error{background-color:#ff4d4f!important}
@media screen and (max-width:580px){padding:30px 5%}
.head-line{font-size:36px}
.subheader{font-size:27px;line-height:37px}
.hr{margin:30px auto;width:215px}
}@media screen and (max-width:450px){padding:30px}
.head-line{font-size:32px}
.hr{margin:25px auto;width:180px}
.context{font-size:15px;line-height:22px}
.context p:nth-child(n+2){margin-top:10px}
.buttons-container{margin-top:29px}
.buttons-container a{float:none !important;width:65%;margin:0 auto;font-size:13px;padding:9px 0}
.buttons-container a:nth-child(2){margin-top:12px}
}
</style>
</head>
<body>

    <div class="exception-container">
        <div class="head-line"><span class="status-ico status-error">X</span></div>
        <div class="subheader">服务器异常</div>
        <div class="hr"></div>
        <div class="context">
            <p>你可以返回上一页重试，或直接向我们反馈错误报告</p>
        </div>
        <div class="buttons-container">
            <a href="/">返回主页</a>
            <a href="/">反馈错误</a>
        </div>
    </div>

</body>
</html>
