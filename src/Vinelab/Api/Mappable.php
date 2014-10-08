<?php namespace Vinelab\Api;
/**
 * @author Mahmoud Zalt <mahmoud@vinelab.com>
 */
//interface Mappable
//{
//    public function map($data);
//}
trait Mappable
{
    public abstract function map(array $data);
}
