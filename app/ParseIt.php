<?php

namespace App;

use App\Models\Source;
use Illuminate\Database\Eloquent\Model;

class ParseIt extends Model
{
    public static function notDone ()
    {
        if ( $source = Source::where('review', '=', 0)->first() )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Возвращает информацию по процессу сбора данных
     *
     * @return array $parsing_info
     */
    public static function parsingInfo ()
    {
        $parsing_info['всего'] = Source::count();
        $parsing_info['просмотренно'] = Source::where('review', '=', 1)->get()->count();
        $parsing_info['процент'] = @(int) ( ( $parsing_info['просмотренно'] * 100) / $parsing_info['всего'] );
        $parsing_info['работает'] = static::notDone();
        return $parsing_info;
    }
}
