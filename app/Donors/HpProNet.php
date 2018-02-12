<?php

namespace App\Donors;

use App\Http\Controllers\LoggerController;
use App\Models\ProxyList;
use ParseIt\_String;
use ParseIt\nokogiri;
use ParseIt\ParseItHelpers;
use ParseIt\simpleParser;

Class HpProNet extends simpleParser {

    public $data = [];
    public $reload = [];
    public $cookieFile = '/cookie-HpProNet.txt';
    public $project = 'www.hp-pro.net';
    public $project_link = 'https://www.hp-pro.net/';

    function __construct()
    {
        $this->cookieFile = $_SERVER['DOCUMENT_ROOT'].'/parseit/public/cookie'.$this->cookieFile;
    }

    public function getSources($url = '')
    {
        $sources = [];
        $source['cookieFile'] = $this->cookieFile;
//        $proxy = ProxyList::getNextProxy();
//        $cookie = file_get_contents($this->cookieFile);
//        if ( preg_match("%msk_cl%is", $cookie) )
//        {
//            $cookie = str_replace('msk_cl', 'rnd_cl', $cookie);
//            file_put_contents($this->cookieFile, $cookie);
//        }
        $content = $this->loadUrl($url, $source);
        $nokogiri = new nokogiri($content);
        $sub_category = $nokogiri->get("div.lvl2_2 ul li a")->toArray();
        if ( !empty($sub_category) )
        {
            foreach ( $sub_category as $category )
            {
                foreach ($this->getSources($this->fixUrl($category['href'])) as $source)
                {
                    $sources[] = $source;
                }
            }
        }
        else
        {
            $products = $nokogiri->get("input[name=redirect_url]")->toArray();
            if ( !empty(@$products[0]['value']) )
            {
                foreach ($products as $product)
                {
                    $sources[] = [
                        'url' => $this->fixUrl($product['value']),
                        'cookieFile' => $this->cookieFile,
                        'referer' => $url
                    ];
                }
            }
            else
            {
                $products = $nokogiri->get(".good_close_look a")->toArray();
                foreach ($products as $product)
                {
                    $sources[] = [
                        'url' => $this->fixUrl($product['href']),
                        'cookieFile' => $this->cookieFile,
                        'referer' => $url
                    ];
                }
            }
            if ( $next = @$nokogiri->get(".prev_link")->toArray()[0] )
            {
                if ( trim($next['__ref']->nodeValue) === '>' )
                {
                    $next_sources = $this->getSources($this->fixUrl($next['href'].'&limit=3000'));
                    foreach ( $next_sources as $source )
                    {
                        $sources[] = $source;
                    }
                }
            }
        }

        return $sources;
    }

    public function onSourceLoaded($content, $url, $source)
    {
        //$content = iconv('UTF-8', 'UTF-8//IGNORE', $content);
        //$content = ParseItHelpers::fixEncoding($content);
        if ( !$content )
        {
//            LoggerController::logToFile("Url {$url} return blank page with {$source['proxy']} proxy", 'error');
            return [];
        }
        $nikogiri = new nokogiri($content);
        $stockin = trim(@$nikogiri->get(".buy_info small")->toArray()[0]['__ref']->nodeValue);
        $stockin = trim(str_replace('Наличие:', '', $stockin));
        $sku = trim(@$nikogiri->get(".good_code")->toArray()[0]['__ref']->nodeValue);
        $sku = trim(str_replace('Артикул:', '', $sku));
        $product_name = trim(@$nikogiri->get("h1")->toArray()[0]['__ref']->nodeValue);
        $description = trim(@$nikogiri->get(".tab_content")->toArray()[0]['__ref']->nodeValue);
        $price = @$nikogiri->get(".good_price")->toArray()[0]['__ref']->nodeValue;
        $price = preg_replace('%[^\d]+%uis', '', $price);
        $old_price = 0;

        $photo = @$nikogiri->get(".screenshot_list li a")->toArray();
        $images = [];
        $images[] = $this->fixUrl(@$nikogiri->get(".gsl_left img")->toArray()[0]['src']);
        foreach ( $photo as $a )
        {
            $images[] = $this->fixUrl($a['href']);
        }

//        $brand = @$nikogiri->get(".c-brand-logo meta")->toArray()[0]['content'];
//        $breadcrumbs = @$nikogiri->get(".c-breadcrumbs__list li a span")->toArray();
//        $categories = [];
//        foreach ( $breadcrumbs as $breadcrumb )
//        {
//            $categories[] = $breadcrumb['#text'];
//        }
//        $content = $this->loadUrl($url."/specification?ssb_block=descriptionTabContentBlock", $source);
//        if ( !$content )
//        {
//            LoggerController::logToFile("Url {$url} return blank page with {$source['proxy']} proxy", 'error');
//            return [];
//        }
//        $content = ParseItHelpers::fixEncoding($content);
//        $content = ParseItHelpers::fixHeader($content);
//        $nokogiri = new nokogiri($content);
        $chars = $nikogiri->get(".chars tr")->toArray();
        $productAttributes = [];
        $productAttributes[0]['atr_group_mame'] = 'Технические характеристики';
        foreach ( @$chars as $tr )
        {
            if (!isset($tr['td'][1]))
            {
                continue;
            }
            $title = trim(@$tr['td'][0]['__ref']->nodeValue);
            $value = trim(@$tr['td'][1]['__ref']->nodeValue);
            $productAttributes[0]['attributes'][] = [
                $title => $value
            ];
        }
//        print_r($productAttributes);die();

        $data = [
            'source' => $url,
            'categories' => '',
            'manufacturer' => '',
            'product_name' => $product_name,
            'sku' => $sku,
            'stockin' => $stockin,
            'old_price' => (float)$old_price,
            'price' => (float)$price,
            'description' => $description,
            'main_image' => !empty($images) ? $images[0] : '',
            'gallery' => !empty($images) ? serialize(@$images) : '',
            'product_attributes' => serialize($productAttributes),
            'hash' => md5($url),
        ];
//        print_r($data);die();
        return $data;
    }
}