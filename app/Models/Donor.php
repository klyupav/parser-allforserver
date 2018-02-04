<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Donor
 *
 * @property int $id
 * @property string $name
 * @property string $link
 * @property string $class
 * @property bool $used
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Donor whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Donor whereLink($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Donor whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Donor whereClass($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Donor whereUsed($value)
 * @mixin \Eloquent
 */
class Donor extends Model
{
    protected $table = 'donors';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'link',
        'class',
        'used'
    ];

    protected $guarded = [];
}