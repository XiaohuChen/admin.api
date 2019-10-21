<?php
/**
 * Created by PhpStorm.
 * User: ChenJulong
 * Date: 2019/9/11
 * Time: 10:28
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class BaseModel extends Model
{
    public $primaryKey = 'Id';

    public $timestamps = false;
    /**
     * 基类模型，用于写模型日志
     */
}
