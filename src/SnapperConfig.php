<?php

class ConfigBuilder
{
	/**
	 * @param string $className
	 * @param array $array
	 * @return mixed
	 * @throws ReflectionException
	 */
	public static function fromArray(string $className, array $array)
	{
		$obj = new $className();
		
		$reflection = new ReflectionClass($obj);
		$propertyList = $reflection->getProperties();
		
		foreach($propertyList as $property)
		{
			if(array_key_exists($property->getName(),$array))
			{
				if($property->getName()=='rotate')
				{
					$obj->{$property->getName()} = self::fromArray('SnapperConfigRotate', $array[$property->getName()]);
				}
				else
				{
					$obj->{$property->getName()} = $array[$property->getName()];
				}
			}
			else
			{
				$obj->{$property->getName()} = null;
			}
		}
		
		return  $obj;
	}
	
// waiting for 7.4
//	public static function fromArray(string $className, array $array)
//	{
//		$obj = new $className();
//
//		$reflection = new ReflectionClass($obj);
//		$propertyList = $reflection->getProperties();
//
//		foreach($propertyList as $property)
//		{
//			if(array_key_exists($property->getName(),$array))
//			{
//				$propertyType = $property->getType();
//
//				if($propertyType->isBuiltin())
//				{
//					$obj->{$property->getName()} = $array[$property->getName()];
//				}
//				else
//				{
//					$obj->{$property->getName()} = self::fromArray($propertyType->getName(), $array[$property->getName()]);
//				}
//			}
//			else
//			{
//				$obj->{$property->getName()} = null;
//			}
//		}
//
//		return  $obj;
//	}
}


class SnapperConfig
{
// waiting for 7.4
//	/** @var string $source */
//	public string $source;
//	/** @var string|null $snapDir */
//	public ?string $snapDir;
//	/** @var string|null $target */
//	public ?string $target;
//
//	/** @var SnapperConfigRotate|null $rotate */
//	public ?SnapperConfigRotate $rotate;


	/** @var string $source */
	public  $source;
	/** @var string|null $snapDir */
	public $snapDir;
	/** @var string|null $target */
	public $target;
	
	/** @var SnapperConfigRotate|null $rotate */
	public $rotate;
}

class SnapperConfigRotate
{
// waiting for 7.4
//	/** @var integer $dense */
//	public int $dense;
//	/** @var integer $sparse */
//	public int $sparse;
//	/** @var string|null $dumpType */
//	public ?string $dumpType;


	/** @var integer $dense */
	public $dense;
	/** @var integer $sparse */
	public $sparse;
	/** @var string|null $dumpType */
	public $dumpType;
}

