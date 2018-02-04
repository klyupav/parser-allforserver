<?php

namespace App\Http\Controllers;

use App\Donors\MvideoRu;
use App\Donors\CitilinkRu_Category;
use App\Donors\Watches2uCom;
use App\Models\DataSet;
use App\Models\Donor;
use App\Models\Source;
use App\Models\ProxyList;
use App\ParseIt;
use Illuminate\Http\Request;
use App\ParseIt\DB;
use App\ParseIt\export\openCart;
use Intervention\Image\Facades\Image;

class ParseitController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except([
            'ajaxGetParsingInfo',
            'run',
            'export'
        ]);
    }

    /**
     * Показываем панель управления сбором данных.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $parsing_info = ParseIt::parsingInfo();
        return view('parseit.index', [
            'parsing_info' => $parsing_info
        ]);
    }

    /**
     * Возвращает информацию парсинга в json массиве.
     *
     * @return string json
     */
    public function ajaxGetParsingInfo()
    {
        $parsing_info = ParseIt::parsingInfo();
        return json_encode([
            'success' => true,
            'parsing_info' => $parsing_info
        ]);
    }

    public function start()
    {
        LoggerController::logToFile('Parsing process - run');
        Source::reset();

        return redirect(url('/'))->with('alert-success','Парсер запущен!');
    }

    public function stop()
    {
        Source::stop();
        return redirect(url('/'))->with('alert-success','Парсер остановлен!');
    }

    /**
     * Сбор данных со всех источоников
     *
     */
    public function run()
    {
        set_time_limit(config('app.max_execute_time')+60);
        $time_start = time();

        $lockfile = "lock-parsing.log";
        //@unlink($lockfile);die('aga');

//        if( file_exists($lockfile))
//        {
//            $locktime = file_get_contents($lockfile);
//            echo time()-$locktime;
//            if( time()-$locktime > config('app.max_execute_time') + 180 )
//            {
//                @unlink($lockfile);
//            }
//            else
//            {
//                echo "ОТМЕНА - скрипт все еще работает<br />\n";
//                exit;
//            }
//        }
        file_put_contents($lockfile, "$time_start");

        $parser['MvideoRu'] = new MvideoRu();

//        $proxy = ProxyList::getNextProxy();
//        $content = $parser['CitilinkRu']->loadUrl('https://www.citilink.ru/geo/space/on_error_resolving/', [
//            'cookieFile' => $parser['CitilinkRu']->cookieFile,
//            'proxy' => $proxy,
//            'referer' => "https://www.citilink.ru/",
//            'post' => [
//                'PERMISSION_DENIED' => 1,
//                'POSITION_UNAVAILABLE' => 2,
//                'TIMEOUT' => 3,
//                'code' => 1,
//                'message' => 'User denied Geolocation',
//            ]
//        ]);
//        print_r($content);@unlink($lockfile);die();
//        $parser['Watches2uCom'] = new Watches2uCom();
        foreach (Donor::whereUsed(true)->get() as $donor)
        {
            while ($source = Source::getNextByDonor($donor->id))
            {
                switch ($source->type_id)
                {
                    case 0:
                        try
                        {
                            $new = 0;
                            $upd = 0;
                            LoggerController::logToFile("Parsing source - {$source->url}");
                            $results = $parser[$donor->class]->getSources($source->url);
//                            print_r($source->url);
//                            die();
                            foreach ($results as $k => $result)
                            {
//                                sleep(2);
                                $param['headers'] = 'Cookie: MVID_CITY_ID=CityCZ_2446;';
                                $param['cookieFile'] = $result['cookieFile'];

//                                $result['url'] = "http://www.mvideo.ru/products/televizor-hisense-55k321uw-10008790";
                                print_r($result);
                                $content = $parser[$donor->class]->loadUrl($result['url'], $param);
                                $data = $parser[$donor->class]->onSourceLoaded($content, $result['url'], $param);
                                print_r($data);
//                                die();
//                                continue;
//die();
//                                sleep(15);

                                if (empty($data)) {
                                    $data['hash'] = md5(md5($result['url']) . $source->donor_id);
                                    $find_data = DataSet::whereHash($data['hash'])->first();
                                    if ( !empty($find_data) )
                                    {
                                        \Illuminate\Support\Facades\DB::setTablePrefix('oc_');
                                        \Illuminate\Support\Facades\DB::table('product')->where(['product_id' => $find_data->product_id])->update(['status' => 0]);
                                        \Illuminate\Support\Facades\DB::setTablePrefix('pii_');
                                    }
                                    continue;
                                }


                                $data['donor_id'] = $source->donor_id;
                                $data['hash'] = md5($data['hash'] . $source->donor_id);
                                $data['exported'] = 0;
                                $data['updated_at'] = date('Y-m-d H:i:s');
                                $data['procent_nakrutki'] = $source->procent_nakrutki;
                                $data['category_id'] = $source->category_id;

                                $validator = \Validator::make($data, [
                                    'exported' => 'required|boolean',
                                    'hash' => 'required|string|size:32',
                                    'donor_id' => 'required|int|max:11',
                                    'source' => 'required|string|max:255',
                                    'manufacturer' => 'required|string|max:255',
                                    'product_name' => 'required|string|max:255',
                                    'sku' => 'required|string|max:255',
                                    'stockin' => 'required|string|max:255',
//                                    'categories' => 'required|string|max:255',
                                    'old_price' => 'required|numeric',
                                    'price' => 'required|numeric',
                                    'description' => 'string',
//                                'main_image' => 'required|string|max:255',
//                                'gallery' => 'required|string',
                                    'product_attributes' => 'required|string',
//                                    'category_id' => 'required|int',
                                    'procent_nakrutki' => 'required|int'
                                ]);
                                if ($validator->errors()->count())
                                {
                                    $context['errors'] = $validator->errors()->messages();
                                    $context['data'] = $data;
                                    echo '<pre>';
                                    print_r($context);
                                    echo '</pre>';
                                    LoggerController::logToFile('ParseitController.run()', 'warning', $context);
                                    continue;
                                }
                                if ( DataSet::SaveOrUpdate($data) === 'update' )
                                    $upd++;
                                else
                                    $new++;
                                //LoggerController::logToFile('Parsing process - run', 'info', $data);
                            }
                            LoggerController::logToFile("New - {$new}, update - {$upd}, from - {$source->url}");
                        }
                        catch (\Exception $e)
                        {
                            $exceptions[] = $e;
                            $context['errors'] = $exceptions;
                            $context['source'] = $source->url;
                            if ( config('app.errors_to_logger') )
                            {
                                LoggerController::logToFile('ParseitController.run()', 'error', $context);
                            }
                            else
                            {
                                echo '<pre>';
                                print_r($context);
                                echo '</pre>';
                            }
                        }
                        break;
                }
                break;
//                die();
                $now = time();
                if ( $now - $time_start > config('app.max_execute_time') )
                {
                    unlink($lockfile);
                    die('end time execute');
                }
            }
        }
        unlink($lockfile);
        // $source = Source::where('review', '!=' , 1)->first();
        // if( empty($source) )
        //     LoggerController::logToFile('Parsing process - done');
    }

    /**
     * Экспорт в базу данных магазина
     *
     */
    public function export()
    {
        // LoggerController::logToFile('Export to database - run');
        set_time_limit(config('app.max_execute_time')+60);
        $time_start = time();

        $lockfile = "lock-export.log";
        //@unlink($lockfile);die('aga');

        if( file_exists($lockfile))
        {
            $locktime = file_get_contents($lockfile);
            echo time()-$locktime;
            if( time()-$locktime > config('app.max_execute_time') + 180 ) 
            {
                @unlink($lockfile);
            } 
            else 
            {
                echo "ОТМЕНА - скрипт все еще работает<br />\n";
                exit;
            }
        }
        file_put_contents($lockfile, "$time_start");

        include_once(base_path(). '/../config.php');

        $db           = new DB();
        $db->host     = DB_HOSTNAME;
        $db->username = DB_USERNAME;
        $db->password = DB_PASSWORD;
        $db->database = DB_DATABASE;
        $db->port     = DB_PORT;

        $oc = new openCart();
        $oc->db = $db;
        
        while ($data = DataSet::getNext())
        {
            try
            {
                $data = $data->toArray();
                $data['quantity'] = 999;
                if ( $data['stockin'] === 'in stock' )
                    $data['stock_status_id'] = 7;
                else
                    $data['stock_status_id'] = 5;

                if ( $data['old_price'] )
                {
                    $data['special'] = $data['price']; // Акционная цена
                    $data['price'] = $data['old_price']; // Перечеркнутая цена
                    $data['special'] = (int) ($data['special'] + (($data['special'] / 100) * $data['procent_nakrutki'])); // Добавляем процент к акционной цене
                }
                $data['price_purchase'] = $data['price']; // Закупочная цена
                $data['price'] = (int) ($data['price'] + (($data['price'] / 100) * $data['procent_nakrutki'])); // Добавляем процент к цене

                if ( $pid = $oc->findProduct($data['product_id']) )
                    $oc->productUpdate($data, $pid);
                else
                    $oc->productInsert($data);
            }
            catch (\Exception $e)
            {
                $exceptions[] = $e;
                $context['errors'] = $exceptions;
                $context['data'] = $data;
                if ( config('app.errors_to_logger') )
                {
                    LoggerController::logToFile('ParseitController.run()', 'error', $context);
                }
                else
                {
                    echo '<pre>';
                    print_r($context);
                    echo '</pre>';
                }
            }
            $now = time();
            if ( $now - $time_start > config('app.max_execute_time') )
            {
                unlink($lockfile);
                die('end time execute');
            }
        }
        unlink($lockfile);
        // LoggerController::logToFile('Export to database - done');
    }

    /**
     * Перенос структуры каталога с ситилинка
     *
     */
    public function parsingCategoryFromCitilink()
    {
        set_time_limit(0);

        include_once(base_path(). '/../config.php');
        $db           = new DB();
        $db->host     = DB_HOSTNAME;
        $db->username = DB_USERNAME;
        $db->password = DB_PASSWORD;
        $db->database = DB_DATABASE;
        $db->port     = DB_PORT;

        $oc = new openCart();
        $oc->db = $db;

        $parser = new CitilinkRu_Category();
        $categories = $parser->getSources();
//        print_r($categories);die();

        $donor_id = Donor::whereClass('CitilinkRu')->first()->id;
        foreach ( $categories as $path => $link )
        {
            $cats = explode('|', $path);
            $category_id = 0;
            foreach ($cats as $k => $value) {
                // создание категорий
                $cat = [
                    'name' => $value,
                    'image' => '',
                    'description' => ''
                ];
                if (!$k) {
                    $category_id = $oc->addCatetgory($cat);// first cat - parent_id = 0, root
                } else {
                    $category_id = $oc->addCatetgory($cat, $category_id);
                }
                //прописываем пути категорий, необходимо для выпадающих списков в админ панеле
                $cats_id[] = $category_id;
                //oc_category_path
                foreach ($cats_id as $key => $val) {
                    $oc->insert('oc_category_path', array('category_id' => $category_id, 'path_id' => $val, 'level' => $key));
                }
            }

            $source['url'] = $link;
            $source['category_id'] = $category_id;
            $source['procent_nakrutki'] = 10;
            $source['type_id'] = 0;
            $source['review'] = false;
            $source['hash'] = md5($donor_id.$link);
            $source['donor_id'] = $donor_id;
            $source['source'] = '';
            Source::SaveOrUpdate($source);
//            print_r(['cats' => $cats, 'category_id' => $category_id]);die();
        }
    }
}