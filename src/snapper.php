<?php
/**
 * Created by PhpStorm.
 * User: zibi
 * Date: 2018-06-05
 * Time: 11:02
 */

if(basename(__FILE__)=='snapper.php')
{
    include_once(__DIR__."/Url.php");
    include_once(__DIR__."/BtrfsSnapper.php");
}

$configName = '.btrfsSnapper.conf';
$configLocations = [
        $configName,
        $_SERVER['HOME']."/{$configName}",
        "/etc/btrfsSnapper"
];

while($configFile = array_shift($configLocations))
{
    if(file_exists($configFile))
    {
        $configList = json_decode(file_get_contents($configFile));
    }
}

if(!isset($configList))
{
    throw new Exception('No config file found!');
}

if(!is_array($configList))
{
    $configList = [$configList];
}

foreach($configList as $config)
{
    $snap = new BtrfsSnapper();
    $snap->setSource($config->source);
    if(isset($config->target))
    {
        $snap->setTarget($config->target);
    }
    if(isset($config->snapDir))
    {
        $snap->setSnapDir($config->snapDir);
    }
    $snap->run();
}
