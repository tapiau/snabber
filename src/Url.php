<?php
/**
 * Created by PhpStorm.
 * User: zibi
 * Date: 2018-06-05
 * Time: 11:06
 */

class Url
{
    public $scheme;
    public $host;
    public $port;
    public $user;
    public $pass;
    public $path;
    public $query;
    public $fragment;

    public static function fromString($string)
    {
        $array = parse_url($string);

        $url = new Url();
        foreach($array as $key=>$value)
        {
            $url->{$key} = $value;
        }

        return $url;
    }
}
