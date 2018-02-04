<?php

namespace App\Donors;

use App\Http\Controllers\LoggerController;
use App\Models\ProxyList;
use ParseIt\_String;
use ParseIt\nokogiri;
use ParseIt\ParseItHelpers;
use ParseIt\simpleParser;

Class MvideoRu extends simpleParser {

    public $data = [];
    public $reload = [];
    public $cookieFile = '/cookie-MvideoRu.txt';
    public $project = 'www.mvideo.ru';
    public $project_link = 'www.mvideo.ru';

    function __construct()
    {
        $this->cookieFile = $_SERVER['DOCUMENT_ROOT'].'/parseit/public/cookie'.$this->cookieFile;
    }

    public function getSources($url = '')
    {
//        die($this->cookieFile);
        $sources = [];
        $source['headers'] = 'Cookie: MVID_CITY_ID=CityCZ_2446;';
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
        $product_link = $nokogiri->get("h2.product-tile-title a")->toArray();
        foreach ($product_link as $pl)
        {
            $sources[] = [
                'url' => $this->fixUrl($pl['href']),
                'cookieFile' => $this->cookieFile,
                'referer' => $url
            ];
        }
//        return $sources;
        if ( $next = @$nokogiri->get(".ico-pagination-next")->toArray()[0]['href'] )
        {
            $next_sources = $this->getSources($this->fixUrl($next));
            foreach ( $next_sources as $source )
            {
                $sources[] = $source;
            }
        }
//print_r($sources);die();
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
        $stockin = 'in stock';
        $sku = trim(@$nikogiri->get(".c-product-code")->toArray()[0]['__ref']->nodeValue);
        $product_name = trim(@$nikogiri->get(".sel-product-title")->toArray()[0]['__ref']->nodeValue);
        $description = trim(@$nikogiri->get(".collapse-text-initial")->toArray()[0]['__ref']->nodeValue);
        $price = @$nikogiri->get(".c-pdp-price__current")->toArray()[0]['__ref']->nodeValue;
        $price = preg_replace('%[^\d]+%uis', '', $price);
        $old_price = @$nikogiri->get(".c-pdp-price__old")->toArray()[0]['__ref']->nodeValue;
        $old_price = preg_replace('%[^\d]+%uis', '', $old_price);

        $photo = @$nikogiri->get(".c-carousel__images .c-carousel__item")->toArray();
        $images = [];
        foreach ( $photo as $a )
        {
            $images[] = "http:".$a['data-src'];
        }

        $brand = @$nikogiri->get(".c-brand-logo meta")->toArray()[0]['content'];
        $breadcrumbs = @$nikogiri->get(".c-breadcrumbs__list li a span")->toArray();
        $categories = [];
        foreach ( $breadcrumbs as $breadcrumb )
        {
            $categories[] = $breadcrumb['#text'];
        }
        $content = $this->loadUrl($url."/specification?ssb_block=descriptionTabContentBlock", $source);
        if ( !$content )
        {
//            LoggerController::logToFile("Url {$url} return blank page with {$source['proxy']} proxy", 'error');
            return [];
        }
        $content = ParseItHelpers::fixEncoding($content);
        $content = ParseItHelpers::fixHeader($content);
        $nokogiri = new nokogiri($content);
        $spec_headers = $nokogiri->get(".product-details-tables-holder h3")->toArray();
        $spec_tables = $nokogiri->get(".product-details-tables-holder table tbody")->toArray();
        $productAttributes = [];

        foreach ($spec_headers as $k => $val)
        {
            $productAttributes[$k]['atr_group_mame'] = trim($spec_headers[$k]['__ref']->nodeValue);
            unset($spec_tables[$k]['tr']['#text'], $spec_tables[$k]['tr']['__ref']);
            if ( count($spec_tables[$k]['tr']) > 1 )
            {
                foreach ( @$spec_tables[$k]['tr'] as $tr )
                {
                    $title = trim(@$tr['td'][0]['span'][0]['__ref']->nodeValue);
                    $value = trim(@$tr['td'][1]['span'][0]['__ref']->nodeValue);
                    $productAttributes[$k]['attributes'][] = [
                        $title => $value
                    ];
                }
            }
            else
            {
                $tr = $spec_tables[$k]['tr'];

                $title = trim(@$tr['td'][0]['span'][0]['__ref']->nodeValue);
                $value = trim(@$tr['td'][1]['span'][0]['__ref']->nodeValue);
                if ( !empty( $title ) && !empty( $value ) )
                {
                    $productAttributes[$k]['attributes'][] = [
                        $title => $value
                    ];
                }
            }
        }
//        print_r($productAttributes);die();

        $data = [
            'source' => $url,
            'categories' => serialize($categories),
            'manufacturer' => @$brand,
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