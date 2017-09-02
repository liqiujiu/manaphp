<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 2015/12/28
 * Time: 0:05
 */
namespace Mongodb\Models;

use ManaPHP\Mongodb\Model;

class Student extends Model
{
    public $id;
    public $age;
    public $name;

    public static function getSource($context = null)
    {
        return '_student';
    }

    public static function getFieldTypes()
    {
        return [
            '_id' => 'objectid',
            'id' => 'integer',
            'age' => 'integer',
            'name' => 'string'
        ];
    }
}