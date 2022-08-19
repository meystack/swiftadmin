

<div align="center">
<img src="https://www.swiftadmin.net/static/images/sademo/110400_6a5e130d_904542.png" width="98" height="98" />
</div>
<h4 align="center">基于PHP MySQL开发的轻量级高性能快速开发框架</h4>
<p align="center">
<a href="https://gitee.com/meystack/swiftadmin/"><img src="https://badgen.net/badge/PHPCLI/MySQL/green" alt="thinkphp"></a>
<a href="https://gitee.com/meystack/swiftadmin/stargazers"><img src="https://gitee.com/meystack/swiftadmin/badge/star.svg?theme=gvp" alt="star"></a>
<a href="https://gitee.com/meystack/swiftadmin/">
<img src="https://badgen.net/badge/License/Apache/" alt="swiftadmin"></a>
</p>

### 软件介绍

`SWIFTADMIN` 是一款基于WebMan + Layui的高性能开源框架，提供了提供了一个简单易用的（权限）后台管理系统。

拥有极简的封装扩展，特别适合中小企业和个人站长在短期内开发自己的CRM系统、小程序接口、以及其他中后台系统应用。

### 软件架构

|  依赖   |  版本   | 说明                                |
|-----|-----|:----------------------------------|
| PHP               | \>= 7.3   | 最低支持PHP7.3 - 8.0                  |
| WebMan            | \>= 1.4.3 | 基于1.4.3.不支持action-hook/auto-route |
| MySQL             | \>= 5.7   | 最低 5.7,注意5.6版本无JSON字段，会报错         |
| Layui             | \>= 2.7   | 特殊开发版，无法直接用官网替换                   |
| layui-form-design | \>= 1.0   | 表单设计器，基于Sortable专为SAPHP框架开发       |
| Admin Theme       | \>= 1.2   | 专为本框架开发、封装超多功能、支持多种菜单布局           |

### 软件功能

- [x] `用户管理` 用户是系统操作者，该功能主要完成系统用户配置。
- [x] `公司管理` 设置公司常用信息，前端标签调用
- [x] `部门管理` 配置系统组织机构（部门、小组），树结构展现支持数据权限。
- [x] `岗位管理` 配置系统用户所属担任职务。
- [x] `菜单管理` 配置系统菜单，操作权限，按钮、栏目等权限标识等。
- [x] `角色管理` 角色菜单权限分配、设置角色按机构进行数据范围权限划分。
- [x] `插件管理` 可开发定制属于自己的插件，可安装升级社区插件！！！
- [x] `数据字典` 对系统中经常使用的一些较为固定的数据进行维护。
- [x] `操作日志` 用户后台操作日志，全局异常、SQL注入等记录

### 软件优势

- **开箱即用**  分钟快速安装，安装后即可进入开发无需复杂配置。
- **精美样式**  针对Layui2.x管理端开发独立ant Design精美样式。
- **方便快捷**  没用VUE框架，免编译，基于原生jQuery并封装统一入口。
- **菜单接入**  接口化菜单接入，可将应用轻松接入任意菜单模块中，无需后台配置。
- **表单构建**  基于Layui2.x开发的可视化表单设计器，可自定义表单控件，极大提高开发效率。
- **代码生成**  轻松一键CURD，自动生成代码，自动生成表单，自动生成接口，自动生成菜单，自动生成权限。
- **插件开发**  支持自定义插件开发，可定制自己的插件，并支持一键打包/升级/测试。
- **性能强悍**  底层WebMan-PHPCli常驻内存模式，性能是TP/Laravel的10倍以上。

### 安装使用

1、首先将本框架直接clone到你本地,或者直接下载
```
git clone https://gitee.com/meystack/swiftadmin.git
```
<font color="#dd0000">2、Windows环境下启动开发服务</font>
```
找到根目录下的Windows.bat文件，执行该文件即可启动服务。
```
3、Linux环境下启动开发服务
```
执行命令：php start.php start        # 启动开发服务
执行命令：php start.php stop         # 停止开发服务
执行命令：php start.php restart      # 重启开发服务
守护进程：php start.php start -d     # 启动守护进程 一般为生产环境
```
4、打开浏览器输入网址，访问项目
```
http://localhost:8787/              # 访问项目执行安装
```
5、安装完毕后，访问后台URL登录系统
```
http://localhost:8787/manage        # 登录后台/生产环境下可自行修改后台地址
```

### 反馈BUG

> 前往论坛反馈BUG

论坛反馈 : https://ask.swiftadmin.net/

> 加入反馈QQ群更快获得解答

<a href="https://qm.qq.com/cgi-bin/qm/qr?k=Idivrh-log25t0ryx19nWeqUk8oFrI-X&jump_from=webapi"><img src="https://badgen.net/badge/qq2000人群/68221484/" alt="一群"></a>
<a href="https://qm.qq.com/cgi-bin/qm/qr?k=L_SKDh46TnWDVrudKEON2XAlgm02RNic&jump_from=webapi"><img src="https://badgen.net/badge/qq二群/68221585/" alt="二群"></a>
<a href="https://qm.qq.com/cgi-bin/qm/qr?k=p6N-b7AkWiESpcrZmOKWpm3t05qt4MQ-&jump_from=webapi"><img src="https://badgen.net/badge/qq三群/68221618/" alt="三群"></a>

### 项目演示
GOTO: <a href="http://demo.swiftadmin.net/manage" target="_blank">
http://demo.swiftadmin.net/manage </a> </b><br/>
<b>管理账号  admin admin888 </b><br/>
<b>测试账号  ceshi admin888 </b>如正式运营环境请删除测试账号;<br/>

<table>
	<tr>
		<td><img src="https://www.swiftadmin.net/static/images/sademo/135519_aa76fdcf_904542.gif"/></td>
	</tr>
	<tr>
		<td><img src="https://gitee.com/meystack/swiftadmin/raw/master/public/upload/1.gif"/></td>
	</tr>	
	<tr>
		<td><img src="https://www.swiftadmin.net/static/images/sademo/140708_8baf92f1_904542.gif"/></td>
	</tr>	
</table>

### ✔️ 特别鸣谢

感谢以下的项目,排名不分先后

jQuery：http://jquery.com

Layui: https://www.layuion.com

ThinkPHP：http://www.thinkphp.cn

WebMan：http://www.workerman.net

Jetbrains：https://www.jetbrains.com/


### 版权信息

[`SwiftAdmin`] 遵循Apache2开源协议发布，并提供免费使用。

使用本框架不得用于开发违反国家有关政策的相关软件和应用，否则要付法律责任！

本软件依法享有国家著作权保护，故使用本软件者不得恶意篡改本源码，包括但不限于（植入木马病毒，违法应用）进行恶意传播。

不得对本软件进行恶意篡改或倒卖，不得对本软件进行二次包装后声称为自己的产品等，请遵守国家著作权法！

本项目著作权号 `2021SR0761953`, 其中包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2020-2030 by swiftadmin (https://www.swiftadmin.net)

All rights reserved。