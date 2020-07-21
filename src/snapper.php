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

$params = [
    'h'=>'help',
    'c:'=>'config:',
    'F'=>'full'
];

$opts = getopt(join('',array_keys($params)),$params);

if(array_key_exists('h',$opts) || array_key_exists('help',$opts))
{
    print_r($params);
    exit();
}

$configName = '.btrfsSnapper.conf';
$configLocationList = [
    $configName,
    $_SERVER['HOME']."/{$configName}",
    "/etc/btrfsSnapper"
];

if(array_key_exists('c',$opts) || array_key_exists('config',$opts))
{
    $configLocationList = [];

    if(array_key_exists('c',$opts))
    {
        $configLocation = $opts['c'];

        $configLocationList = array_merge(
        	$configLocationList,
			!is_array($configLocation)
				?[$configLocation]
				:$configLocation
		);
    }
    if(array_key_exists('config',$opts))
    {
        $configLocation = $opts['config'];

        $configLocationList = array_merge(
        	$configLocationList,
			!is_array($configLocation)
				?[$configLocation]
				:$configLocation
		);
    }
}

while($configFile = array_shift($configLocationList))
{
    echo "Checking {$configFile} ... ";
    if(file_exists($configFile))
    {
        $configList = json_decode(file_get_contents($configFile),true);
        echo "OK".PHP_EOL;
        break;
    }
    else
    {
        echo "nope.".PHP_EOL;
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
	/** @var SnapperConfig $config */
	$config = ConfigBuilder::fromArray("SnapperConfig",$config);
	
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
    if(array_key_exists('F',$opts) || array_key_exists('full',$opts))
    {
        $snap->setForceFullDump(true);
    }
    $snap->run();
}
