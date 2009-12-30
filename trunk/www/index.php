<?php
/**
 * The router file of ZenTaoMS.
 *
 * All request should be routed by this router.
 *
 * ZenTaoMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * ZenTaoMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with ZenTaoMS.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright   Copyright: 2009 Chunsheng Wang
 * @author      Chunsheng Wang <wwccss@263.net>
 * @package     ZenTaoMS
 * @version     $Id$
 * @link        http://www.zentao.cn
 */
/* 记录最开始的时间。*/
$timeStart = _getTime();

/* 包含必须的类文件。*/
include '../../../framework/router.class.php';
include '../../../framework/control.class.php';
include '../../../framework/model.class.php';
include '../../../framework/helper.class.php';
include './myrouter.class.php';

/* 实例化路由对象，加载配置，连接到数据库。*/
$app    = router::createApp('pms', '', 'myRouter');
$config = $app->loadConfig('common');
$dbh    = $app->connectDB();

/* 根据debug选项设置错误信息。*/
$config->debug ? error_reporting(E_ALL) : error_reporting(0);

/* 如果是debug模式，记录sql查询。*/
if($config->debug) register_shutdown_function('_saveSQL');

/* 设置客户端所使用的语言、风格。*/
$app->setClientLang();
$app->setClientTheme();

/* 加载语言文件，加载公共模块。*/
$lang   = $app->loadLang('common');
$common = $app->loadCommon();

/* 加载相应的lib文件，并设置超全局变量的引用。*/
$app->loadClass('front',  $static = true);
$app->loadClass('filter', $static = true);
$app->setSuperVars();

/* 处理请求，验证权限，加载相应的模块。*/
$app->parseRequest();
$common->checkPriv();
$app->loadModule();

/* Debug信息，监控页面的执行时间和内存占用。*/
if($config->debug)
{
    $timeUsed = round(_getTime() - $timeStart, 4) * 1000;
    $memory   = round(memory_get_peak_usage() / 1024, 1);
    $querys   = count(dao::$querys);
    echo "<div id='debugbar'>TIME: $timeUsed ms, MEM: $memory KB, SQL: $querys.  </div>";
    echo '<style>body{padding-bottom:50px}</style>';
}

/* 获取系统时间，微秒为单位。*/
function _getTime()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

/* 保存query记录。*/
function _saveSQL()
{
    global $app;
    $sqlLog = $app->getLogRoot() . 'sql.' . date('Ymd') . '.log';
    $fh = @fopen($sqlLog, 'a');
    if(!$fh) return false;
    fwrite($fh, date('Ymd H:i:s') . ": " . $app->getURI() . "\n");
    foreach(dao::$querys as $query) fwrite($fh, "  $query\n");
    fwrite($fh, "\n");
    fclose($fh);
}

/* print_r。*/
function a($var)
{
    echo "<xmp class='a-left'>";
    print_r($var);
    echo "</xmp>";
}
