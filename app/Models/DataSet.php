<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\VarDumper\Cloner\Data;

/**
 * Class DataSet
 *
 * @property int $id
 * @property int $exported
 * @property string $hash
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property int $donor_id
 * @property string $source
 * @property string $manufacturer
 * @property string $product_name
 * @property string $sku
 * @property string $stockin
 * @property float $price
 * @property float $old_price
 * @property string $description
 * @property string $main_image
 * @property string $gallery
 * @property string $categories
 * @property string $product_attributes
 * @property int $category_id
 * @property int $product_id
 * @property int $procent_nakrutki
 * @property int $categories
 * @method static \Illuminate\Database\Query\Builder|\App\Models\DataSet whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\DataSet whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\DataSet whereDonorId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\DataSet whereExported($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\DataSet whereGallery($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\DataSet whereHash($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\DataSet whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\DataSet whereMainImage($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\DataSet whereManufacturer($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\DataSet wherePriceRub($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\DataSet wherePriceUsd($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\DataSet whereProductAttributes($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\DataSet whereProductName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\DataSet whereSku($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\DataSet whereSource($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\DataSet whereStockin($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\DataSet whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DataSet extends Model
{
    protected $table = 'data_set';

    public $timestamps = true;

    public $fillable = [
        'exported',
        'donor_id',
        'donor',
        'manufacturer',
        'product_name',
        'sku',
        'stockin',
        'price',
        'old_price',
        'description',
        'main_image',
        'gallery',
        'product_attributes',
        'hash',
        'source',
        'procent_nakrutki',
        'category_id',
        'product_id',
        'categories'
    ];

    protected $guarded = [];

    public function Donor()
    {
        return Donor::whereId($this->donor_id)->first();
    }

    public static function SaveOrUpdate ($data_set)
    {
        if ( $find = DataSet::whereHash(@$data_set['hash'])->first() )
        {
            $find->update($data_set);
            return 'update';
        }
        else
        {
            DataSet::create($data_set);
            return 'save';
        }
    }

    public static function getFields ()
    {
        $model = new DataSet();
        $model->fillable[] = 'id';
        $model->fillable[] = 'created_at';
        $model->fillable[] = 'updated_at';
        return $model->fillable;
    }

    public static function getNext ()
    {
        if ( $source = static::where('exported', '!=' , 1)->orderBy('updated_at', 'desc')->first() )
        {
            $source->update(['exported' => 1]);
            return $source;
        }
        return false;
    }
}