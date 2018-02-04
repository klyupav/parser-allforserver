<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Source
 *
 * @property int $id
 * @property string $url
 * @property string $source
 * @property int $type_id
 * @property int $review
 * @property string $hash
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property int $donor_id
 * @property int $category_id
 * @property int $category_id_2
 * @property int $procent_nakrutki
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Source whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Source whereDonorId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Source whereHash($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Source whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Source whereUrl($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Source whereReview($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Source whereTypeId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Source whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Source extends Model
{
    protected $table = 'sources';

    public $timestamps = true;

    protected $fillable = [
        'url',
        'source',
        'type_id',
        'review',
        'hash',
        'donor_id',
        'category_id',
        'category_id_2',
        'procent_nakrutki'
    ];

    protected $guarded = [];

    public function Donor()
    {
        return Donor::whereId($this->donor_id)->first();
    }

    public static function getNextByDonor ($donor_id)
    {
        if ( $source = static::where('review', '!=' , 1)->where('donor_id', $donor_id)->orderBy('donor_id')->orderBy('type_id')->first() )
        {
            $source->update(['review' => 1]);
            return $source;
        }
        return false;
    }

    public static function SaveOrUpdate ($source)
    {
        if ( $find = Source::whereHash(@$source['hash'])->first() )
        {
            $find->update($source);
            return 'update';
        }
        else
        {
            Source::create($source);
            return 'save';
        }
    }

    /**
     * Меняет статус источников на "не просмотренно".
     */
    public static function reset ()
    {
        static::where('id', '<>', 0)->update(['review' => 0]);
    }

    /**
     * Меняет статус источников на "просмотренно".
     */
    public static function stop ()
    {
        static::where('id', '<>', 0)->update(['review' => 1]);
    }
}