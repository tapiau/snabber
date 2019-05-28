<?php
/**
 * Created by PhpStorm.
 * User: zibi
 * Date: 2018-06-05
 * Time: 11:03
 */

class BtrfsSnapper
{
    public $source;
    /** @var Url */
    public $target;
    public $snapDirDefault = '.snap';
    public $snapDir = null;
    public $snapName;
    public $forceFullDump = false;

    public function setSource($string)
    {
        if(substr($string,0,1)!='/')
        {
            $string = getcwd().'/'.$string;
        }

        $this->source = realpath($string);

        if($this->source===false)
        {
            throw new Exception("Source does not exists!");
        }
    }
    public function setSnapDir($string)
    {
        if(substr($string,0,1)=='/')
        {
            $this->snapDir = $string;
        }
        elseif(substr($string,0,2)=='./')
        {
            $string = getcwd()."/{$string}";
        }
        else
        {
            $string = "{$this->source}/{$string}";
        }

        $this->snapDir = $string;
    }
    public function setForceFullDump(bool $force)
    {
        $this->forceFullDump = $force;

        return $this;
    }
    public function setTarget($url)
    {
        $this->target = Url::fromString($url);
    }
    public function snap()
    {
        if(is_null($this->snapDir))
        {
            $this->setSnapDir($this->snapDirDefault);
        }
        if(!file_exists($this->snapDir))
        {
            @mkdir($this->snapDir);
        }
        if(!is_dir($this->snapDir))
        {
            throw new Exception("{$this->snapDir} is not a directory, cannot do snapshot!");
        }

        $this->snapName = $this->makeSnapName();

        file_put_contents(
            "{$this->source}/.snap.state",
            json_encode([
                'source'=>$this->source,
                'date'=>date('Y-m-d H:i:s')
            ])
        );

        $exe = "btrfs subvolume snapshot -r {$this->source} {$this->snapDir}/{$this->snapName}";

        $ret = `{$exe}`;

        return $ret;
    }
    public function makeSnapName()
    {
        $now = date('Ymd-His');
        $checksum = md5($this->source);
        $hostname = gethostname();

        return "ro-{$hostname}-{$checksum}-{$now}";
    }
    public function run()
    {
        $this->snap();
        if($this->target)
        {
            $this->send();
        }
    }
    public function getSnapList()
    {
        return glob("{$this->snapDir}/ro-*");
    }
    public function getSnapName($num)
    {
        $slice = array_slice($this->getSnapList(),$num,1);
        return reset($slice);
    }

    public function send()
    {
        $snapNameFull = $this->getSnapName(-1);
        $snapNameParentFull = '';
        $targetExe = 'sh -c';
        $targetUnpack = 'cat > /dev/null';
//        $targetUnpack = "btrfs receive {$this->target->path}";
        $snapFileName = basename($snapNameFull).'.btrfs';

        if(count($this->getSnapList())>1 && !($this->forceFullDump))
        {
            $snapNameParentFull = $this->getSnapName(-2);

            $snapNameParentFull = "-p {$snapNameParentFull}";
            $snapFileName = basename($snapNameParentFull).'_'.$snapFileName;
        }
        if($this->target->scheme=='ssh')
        {
            $targetExe = "ssh {$this->target->host}";

            if($this->target->port)
            {
                $targetExe .= ' -p '.$this->target->port;
            }
            if($this->target->user)
            {
                $targetExe .= ' -l '.$this->target->user;
            }
        }

        // $exe = "btrfs send -p {$snapFolder}/{$snapNameParent} {$snapFolder}/{$snapName} | ssh m.nora.pl -p 44224 'tee {$backupFolder}/{$snapNameParent}_{$snapName}.btrfs | btrfs receive {$backupFolder}' ";

        $exe = "btrfs send {$snapNameParentFull} {$snapNameFull} | {$targetExe} 'tee {$this->target->path}/{$snapFileName} | {$targetUnpack}'";

        echo $exe.PHP_EOL;

        $ret = `{$exe}`;
        print_r($ret);
    }
}
