<?php
namespace app\models;

class UrlHelper
{
    /**
     * @param $parsed_url На входе массив вида, получаемого из функции parse_url
     * @return string На выходе собранная строка с url адресом
     */
    private static function unparse_url($parsed_url) {
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }

    /**
     * Входная строка с url - разбивается на части функцией parse_url,
     * затем ее 'path' (path это /asdsdas/sadasd.php) разбивается по
     * '/' на отдельные елементы массива и к этим массивам применяется
     * rawurlencode и строка собирается обратно. Затем отдельным методом
     * возвращаем распарсенный массив в вид url и возвращаем эту строку
     * запросчику.
     * @param $url
     * @return mixed|string
     */
    public static function UrlEncodePath($url){
        $url = parse_url($url);
        $url['path'] = implode('/', array_map('rawurlencode', explode('/', $url['path'])));
        $url = static::unparse_url($url);

        return $url;
    }
}

/**
 * Created by PhpStorm.
 * User: домашний
 * Date: 29.07.2015
 * Time: 10:42
 */