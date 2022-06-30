FlAdmin是一款基于ThinkPHP+Vue的稳健智能后台开发框架，
FlAdmin后端微服务解耦级解决方案
FlAdmin是蜂雷自研发系统后端框架，
FlAdmin主要用于构建 蜂雷直播基地产业园pass平台，
FlAdmin 是 FengleiAdmin 简称 FlAdmin
版权归 © 蜂雷 京ICP证030173号  科权科技（上海）有限公司 所有
© 2022 上海蜂雷网络科技有限公司 版权所有 All Rights Reserved 沪ICP备15022866号-2 

## 主要特性

* 基于`Auth`验证的权限管理系统
    * 支持基于 RSA对称加密算法基础上的 Auth 2.0 token授权安全机制
    * 支持无限级父子级权限继承，父级的管理员可任意增删改子级管理员及权限设置
    * 支持单管理员多角色
    * 支持管理子级数据或个人数据
* 强大的一键生成功能 GII 开发者规范
    * 一键生成CRUD,包括控制器、模型、视图、JS、语言包、菜单、回收站等
    * 一键压缩打包JS和CSS文件，一键CDN静态资源部署
    * 一键生成控制器菜单和规则
    * 一键生成API接口文档
* 强大的后台前端支持 
    * 基于复用原理，完善的前端功能组件开发 
    * 基于`AdminLTE`二次开发
    * 基于`Bootstrap`开发，自适应手机、平板、PC
    * 基于`RequireJS`进行JS模块管理，按需加载
    * 基于`Less`进行样式开发
* 强大的服务扩展功能

## 安装使用

https://doc.FlAdmin.net

## 在线演示

https://demo.FlAdmin.net

用户名：admin

密　码：123456

提　示：演示站数据无法进行修改，请下载源码安装体验全部功能

## 界面截图 

## 问题反馈

在使用中有任何问题，请使用以下联系方式联系我们

交流社区: https://ask.FlAdmin.net

QQ群: [1276789849](https://jq.qq.com/?_wv=1027&k=487PNBb)(满) [1154638287](https://jq.qq.com/?_wv=1027&k=5ObjtwM)(群2) 

Github: https://github.com/karsonzhang/FlAdmin

Gitee: https://gitee.com/karson/FlAdmin

## 特别鸣谢

感谢以下的项目,排名不分先后

ThinkPHP：http://www.thinkphp.cn

AdminLTE：https://adminlte.io

Bootstrap：http://getbootstrap.com

jQuery：http://jquery.com

Bootstrap-table：https://github.com/wenzhixin/bootstrap-table

Nice-validator: https://validator.niceue.com

SelectPage: https://github.com/TerryZ/SelectPage

Layer: https://layer.layui.com

DropzoneJS: https://www.dropzonejs.com


## 版权信息

FlAdmin遵循Apache2开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2017-2022 by FlAdmin （http://www.fenglei.shop/）
© 2022 上海蜂雷网络科技有限公司 版权所有 All Rights Reserved 沪ICP备15022866号-2 
All rights reserved。(https://www.fladmin.net) 












架构
架构总览
FlAdmin基于MSP的设计模式，将我们的应用分为三层（模型M、服务S（API）、控制器C）。

目录结构
FlAdmin目录结构遵循ThinkPHP5,Thinkphp6官方建议的模块设计：

FlAdmin项目目录
├── addons                  //扩展服务存放目录
├── application             //应用目录
│   ├── admin               //后台管理应用模块
│   ├── api                 //API应用模块
│   ├── common              //通用应用模块
│   ├── extra               //扩展配置目录
│   ├── index               //前台应用模块
│   ├── build.php
│   ├── command.php         //命令行配置
│   ├── common.php          //通用辅助函数
│   ├── config.php          //基础配置
│   ├── database.php        //数据库配置
│   ├── route.php           //路由配置
│   ├── tags.php            //行为配置
├── extend
│   └── fast                //FlAdmin扩展辅助类目录
├── public                  //框架入口目录
│   ├── assets
│   │   ├── build           //打包JS、CSS的资源目录
│   │   ├── css             //CSS样式目录
│   │   ├── fonts           //字体目录
│   │   ├── img             //图片资源目录
│   │   ├── js
│   │   │   ├── backend
│   │   │   └── frontend    //后台功能模块JS文件存放目录
│   │   ├── libs            //Bower资源包位置
│   │   └── less            //Less资源目录
│   └── uploads             //上传文件目录
│   ├── index.php           //应用入口主文件
│   ├── install.php         //FlAdmin安装引导
│   ├── admin.php           //后台入口文件(自动安装后会被修改为随机文件名）
│   ├── robots.txt
│   └── router.php
├── runtime                 //缓存目录
├── thinkphp                //ThinkPHP5,ThinkPHP6框架核心目录
├── vendor                  //Compposer资源包位置
├── .bowerrc                //Bower目录配置文件
├── .env.sample             //环境配置模板（可复制一份为 .env 生效）
├── LICENSE
├── README.md               //项目介绍
├── bower.json              //Bower前端包配置
├── build.php
├── composer.json           //Composer包配置
└── think                   //命令行控制台入口（使用 php think 命令进入）

一键生成API文档
FlAdmin中的一键生成API文档可以在命令行或后台一键生成我们API接口的接口测试文档，可以直接在线模拟接口请求，查看参数示例和返回示例。

准备工作
请确保你的API模块下的控制器代码没有语法错误，控制器类注释、方法名注释完整，注释规则请参考下方注释规则。

请确保你的FlAdmin已经安装成功且能正常登录后台。

请确保php所在的目录已经加入到系统环境变量，否则会提示找不到该命令。

打开命令行控制台进入到你的站点根目录，也就是think文件所在的目录。

常用命令
//一键生成API文档
php think api --force=true
//指定https://www.example.com为API接口请求域名,默认为空
php think api -u https://www.example.com --force=true
//输出自定义文件为myapi.html,默认为api.html
php think api -o myapi.html --force=true
//修改API模板为mytemplate.html，默认为index.html
php think api -e mytemplate.html --force=true
//修改标题为Demo,作者为Lily
php think api -t Demo -a Lily --force=true
//生成服务标识为cms的API文档
php think api -a cms -o cmsapi.html --force=true
//查看API接口命令行帮助
php think api -h
参数介绍
-u, --url[=URL]            默认API请求URL地址 [default: ""]
-m, --module[=MODULE]      模块名(admin/index/api) [default: "api"]
-a, --addon[=ADDON]      服务标识(addons目录下的服务标识) [default: ""]
-o, --output[=OUTPUT]      输出文件 [default: "api.html"]
-e, --template[=TEMPLATE]  模板文件 [default: "index.html"]
-f, --force[=FORCE]        覆盖模式 [default: false]
-t, --title[=TITLE]        文档标题 [default: "FlAdmin"]
-c, --class[=CLASS]        扩展类 (multiple values allowed)
-l, --language[=LANGUAGE]  语言 [default: "zh-cn"]
注释规则
在我们的控制器中通常分为两部分注释，一是控制器头部的注释，二是控制器方法的注释。

控制器注释

名称	描述	示例
@ApiSector	API分组名称	(测试分组)
@ApiRoute	API接口URL，此@ApiRoute只是基础URL	(/api/test)
@ApiInternal	忽略的控制器,表示此控制将不加入API文档	无
@ApiWeigh	API方法的排序,值越大越靠前	(99)
控制器方法注释

名称	描述	示例
@ApiTitle	API接口的标题,为空时将自动匹配注释的文本信息	(测试标题)
@ApiSummary	API接口描述	(测试描述)
@ApiRoute	API接口地址,为空时将自动计算请求地址	(/api/test/index)
@ApiMethod	API接口请求方法,默认为GET	(POST)
@ApiSector	API分组,默认按钮控制器或控制器的@ApiSector进行分组	(测试分组)
@ApiParams	API请求参数,如果在@ApiRoute中有对应的{@参数名}，将进行替换	(name="id", type="integer", required=true, description="会员ID")
@ApiHeaders	API请求传递的Headers信息	(name=token, type=string, required=true, description="请求的Token")
@ApiReturn	API返回的结果示例	({"code":1,"msg":"返回成功"})
@ApiReturnParams	API返回的结果参数介绍	(name="code", type="integer", required=true, sample="0")
@ApiReturnHeaders	API返回的Headers信息	(name="token", type="integer", required=true, sample="123456")
@ApiInternal	忽略的方法,表示此方法将不加入文档	无
@ApiWeigh	API方法的排序,值越大越靠前	(99)
标准范例
<?php

namespace app\api\controller;

/**
 * 测试API控制器
 */
class Test extends \app\common\controller\Api
{

    // 无需验证登录的方法
    protected $noNeedLogin = ['test'];
    // 无需要判断权限规则的方法
    protected $noNeedRight = ['*'];

    /**
     * 首页
     *
     * 可以通过@ApiInternal忽略请求的方法
     * @ApiInternal
     */
    public function index()
    {
        return 'index';
    }

    /**
     * 私有方法
     * 私有的方法将不会出现在文档列表
     */
    private function privatetest()
    {
        return 'private';
    }

    /**
     * 测试方法
     *
     * @ApiTitle    (测试名称)
     * @ApiSummary  (测试描述信息)
     * @ApiSector   (测试分组)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/test/test/id/{id}/name/{name})
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="id", type="integer", required=true, description="会员ID")
     * @ApiParams   (name="name", type="string", required=true, description="用户名")
     * @ApiParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
        'code':'1',
        'mesg':'返回成功'
     * })
     */
    public function test($id = '', $name = '')
    {
        $this->success("返回成功", $this->request->request());
    }

}
常见问题
如果控制器的方法是private或protected的，则将不会生成相应的API文档。

如果注释不生效，请检查注释文本是否正确。



一键管理服务
FlAdmin中的服务可以通过命令行快速的进行安装、卸载、禁用和启用。

准备工作
请确保你的FlAdmin已经能正常登录后台。

请确保php所在的目录已经加入到系统环境变量，否则会提示找不到该命令。

打开命令行控制台进入到你的站点根目录，也就是think文件所在的目录。

常用命令
//创建一个myaddon本地服务，常用于开发自己的服务时使用
php think addon -a myaddon -c create
//刷新服务缓存，如果禁用启用了服务，部分文件需要刷新才会生效
php think addon -a example -c refresh
//卸载本地的example服务
php think addon -a example -c uninstall
//启用本地的example服务
php think addon -a example -c enable
//禁用本地的example服务
php think addon -a example -c disable
//将本地的example服务打包成zip文件
php think addon -a example -c package
常见问题 
如果管理服务后不生效，请在后台右上角清除缓存重试。
更多一键管理服务的参数请使用php think addon --help查看。

文档最后更新时间：2022-06-21 09:30:37



一键生成菜单
FlAdmin可通过命令控制台快速的一键生成后台的权限节点菜单规则，同时后台的管理菜单也会同步改变，操作非常简单。

准备工作
首先确保已经将FlAdmin配置好，数据库连接正确，同时确保已经通过上一步的一键生成CRUD已经生成了test的CRUD。

请确保php所在的目录已经加入到系统环境变量，否则会提示找不到该命令。

打开命令行控制台进入到你的站点根目录，也就是think文件所在的目录。

常用命令
//一键生成test控制器的权限菜单
php think menu -c test
//一键生成mydir/test控制器的权限菜单
php think menu -c mydir/test
//删除test控制器生成的菜单
php think menu -c test -d 1
//一键生成所有控制器的权限菜单，执行前请备份数据库。
php think menu -c all-controller
常见问题
在使用php think menu前确保你的控制器已经添加或通过php think crud生成好。
如果之前已经生成了菜单,需要再次生成,请登录后台手动删除之前生成的菜单或使用php think menu -c 控制器名 -d 1来删除。
如果生成层级目录的菜单，在后台展示时父级菜单会以目录名称显示，如果需要修改可以在application/admin/lang/zh-cn.php中追加相应的语言包即可。
使用范例
示例

更多CRUD一键生成可使用的参数请使用php think menu --help查看。

文档最后更新时间：2022-06-20 15:29:21 

关联字段
//生成fa_fltest表的CRUD
php think crud -t fltest
//生成fa_fltest表的CRUD且一键生成菜单
php think crud -t fltest -u 1
//删除fa_fltest表生成的CRUD
php think crud -t fltest -d 1
//生成fa_fltest表的CRUD且控制器生成在二级目录下
php think crud -t fltest -c mydir/fltest
//生成fa_fltest_log表的CRUD且生成对应的控制器为fltestlog
php think crud -t fltest_log -c fltestlog
//生成fa_fltest表的CRUD且对应的模型名为fltestmodel
php think crud -t fltest -m fltestmodel
//生成fa_fltest表的CRUD且生成关联模型category，外链为category_id，关联表主键为id
php think crud -t fltest -r category -k category_id -p id
//生成fa_fltest表的CRUD且所有以list或data结尾的字段都生成复选框
php think crud -t fltest --setcheckboxsuffix=list --setcheckboxsuffix=data
//生成fa_fltest表的CRUD且所有以image和img结尾的字段都生成图片上传组件
php think crud -t fltest --imagefield=image --imagefield=img
//关联多个表,参数传递时请按顺序依次传递，支持以下几个参数relation/relationmodel/relationforeignkey/relationprimarykey/relationfields/relationmode
php think crud -t fltest --relation=category --relation=admin --relationforeignkey=category_id --relationforeignkey=admin_id
//生成v_phealth_db2数据库下的fa_fltest表的CRUD
php think crud -t fltest --db=v_phealth_db2