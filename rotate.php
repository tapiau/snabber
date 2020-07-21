<?php

require_once 'src/func.php';
paranoid();

$short = join('',[
    'h',
    'c:',
    's::'
]);
$long = [
    'config:',
    'snap:',
    'pre::',
    'help'
];

$a = getopt($short,$long);


//print_r($a);

require_once 'src/SnapperConfig.php';
require_once 'src/BtrfsSnapper.php';
require_once 'src/Url.php';
require_once 'src/Collection.php';

$array = json_decode(file_get_contents('/etc/btrfsSnapper'),true)[0];

/** @var SnapperConfig $config */
$config = ConfigBuilder::fromArray("SnapperConfig",$array);
printr($config);

$snap = new BtrfsSnapper();
$snap->setConfig($config);
$snapList = new Collection($snap->getSnapList());

$snapList = $snapList->map(function($item){
	$chunks = explode('-',$item);
	return [
		'path'=>$item,
		'hash'=>$chunks[2],
		'date'=>$chunks[3],
		'time'=>$chunks[4]
	];
});
$snapTree = $snapList->groupBy('hash');

foreach($snapTree as $hash=>$snapList)
{
	$exeList = new Collection();
	
	$snapListByDate = $snapList->groupBy('date')->krsort();
	
	$snapListDense = $snapListByDate->slice(0,$config->rotate->dense);
	$snapListSparse = $snapListByDate->slice($config->rotate->dense,$config->rotate->sparse);
	$snapListObsolete = $snapListByDate->slice($config->rotate->dense+$config->rotate->sparse);
	
//	printr($snapListByDate->count());
//	printr($snapListDense->count());
//	printr($snapListSparse->count());
//	printr($snapListObsolete->count());
//	printr($snapListDense);
//	printr($snapListSparse);
	
	// remove overflow snapshots from sparse section
	foreach($snapListSparse as $snapListForOneDay)
	{
		if($snapListForOneDay->count()>1)
		{
			foreach($snapListForOneDay->slice(1) as $snap)
			{
				$exe = "btrfs subvolume delete {$snap['path']}";
				$exeList[] = $exe;
			}
		}
	}
	// remove overflow snapshots from sparse section
	foreach($snapListObsolete as $snapListForOneDay)
	{
		foreach($snapListForOneDay as $snap)
		{
			$exe = "btrfs subvolume delete {$snap['path']}";
			$exeList[] = $exe;
		}
	}
	
	$exeList = $exeList->reverse();
	
	printr($exeList);
	
	foreach($exeList as $exe)
	{
		echo $exe.PHP_EOL;
		$status = `{$exe} 2>&1`;
		printr($status);
	}
}

