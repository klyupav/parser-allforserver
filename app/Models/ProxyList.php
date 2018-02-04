<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProxyList
 *
 * @property string $proxy
 * @property int $used
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Donor whereProxy($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Donor whereUsed($value)
 * @mixin \Eloquent
 */
class ProxyList extends Model
{
    protected $table = 'proxy_list';

    public $timestamps = false;

    protected $fillable = [
        'proxy',
        'used'
    ];

    protected $guarded = [];

    public static function getListArray ($list)
    {
        $list = str_replace("\r", "\n", $list);
        $list = str_replace("\n\n", "\n", $list);
        $array = explode("\n", $list);

        foreach ( $array as $k => $ar)
        {
            if ( empty( trim($ar) ) )
            {
                unset($array[$k]);
            }
        }

        return $array;
    }

    public static function getRandomProxy()
    {
        $list = static::getListArray(static::get()->first()->proxy);
        return $list[array_rand($list)];
    }

    public static function getNextProxy()
    {
        $row = static::where(['used' => 0])->get()->first();
        if ( empty($row) )
        {
            static::where(['used' => 1])->update(['used' => 0]);
            $row = static::where(['used' => 0])->get()->first();
        }
        $proxy = $row->proxy;
        static::where(['proxy' => $row->proxy])->update(['used' => 1]);

        return $proxy;
    }
}