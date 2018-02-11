<?php

namespace App\Donors;

use App\Http\Controllers\LoggerController;
use App\Models\ProxyList;
use ParseIt\_String;
use ParseIt\nokogiri;
use ParseIt\simpleParser;

Class CitilinkRu_Category extends simpleParser {

    public $data = [];
    public $reload = [];
    public $cookieFile = '/cookie-CitilinkRu.txt';
    public $project_name = 'citilink.ru';
    public $project_link = 'citilink.ru';

    function __construct()
    {
        $this->cookieFile = $_SERVER['DOCUMENT_ROOT'].'/parseit/public/cookie'.$this->cookieFile;
    }

    public function getSources($url = '')
    {
//        die($this->cookieFile);
        $sources = [];
        $url = 'https://www.citilink.ru/catalog/';
        $content = $this->loadUrl($url, [
            'cookieFile' => $this->cookieFile,
            'referer' => $url
        ]);

        $proxy = ProxyList::getRandomProxy();

        $nokogiri = new nokogiri($content);
        $category_links = $nokogiri->get("a.category_link")->toArray();
        $children_list = $nokogiri->get("ul.category-catalog__children-list")->toArray();
        foreach ( $category_links as $k => $category_link )
        {
//            if ( $k < 1 )
//                continue;
            $cat_1 = trim($category_link['__ref']->nodeValue);
//            $categories[$cat_1] = ['href' => $category_link['href']];
            foreach ($children_list[$k]['li'] as $children)
            {
                $cat_2 = trim($children['__ref']->nodeValue);
//                $categories[$cat_1]['children'][$cat_2] = ['href' => $children['href']];
                $sub_cat_content = $this->loadUrl($children['a']['href'],[
                    'cookieFile' => $this->cookieFile,
                    'referer' => $url,
//                    'useContentCacheOnDate' => true,
//                    'saveContentCache' => true,
                    'proxy' => $proxy
                ]);
                $sub_nokogiri = new nokogiri($sub_cat_content);
//                die($sub_cat_content);
                $navigation__items  = $sub_nokogiri->get(".catalog-content-navigation__item a")->toArray();
                $category_other_distributor  = $sub_nokogiri->get(".category_other_distributor_text__no_print a")->toArray();

                if ( !empty($navigation__items) )
                {
                    foreach ( $navigation__items as $item )
                    {
                        $cat_3 = trim($item['__ref']->nodeValue);
                        $categories["{$cat_1}|{$cat_2}|{$cat_3}"] = $item['href'];
                    }
                }
                elseif ( !empty($category_other_distributor) )
                {
                    foreach ( $category_other_distributor as $item )
                    {
                        $cat_3 = trim($item['__ref']->nodeValue);
                        if ( !preg_match("%www\.citilink\.ru%is", $item['href']) )
                        {
                            $item['href'] = 'https://www.citilink.ru'.$item['href'];
                        }
                        $categories["{$cat_1}|{$cat_2}|{$cat_3}"] = $item['href'];
                    }
                }
                else
                {
                    $categories["{$cat_1}|{$cat_2}"] = $children['a']['href'];
                }
            }
//            break;
        }
//        print_r($categories);die();
        return $categories;
    }

    public function onSourceLoaded($content, $url, $source)
    {
        //$content = iconv('UTF-8', 'UTF-8//IGNORE', $content);
        //$content = ParseItHelpers::fixEncoding($content);
        if ( !$content )
        {
//            print_r($url);die();
            LoggerController::logToFile("Url {$url} return blank page", 'error');
            return;
        }

        $nikogiri = new nokogiri($content);

        $sku = @$nikogiri->get(".product_id")->toArray()[0]['__ref']->nodeValue;
        $product_name = $nikogiri->get("meta[itemprop=name]")->toArray()[0]['content'];
        $product_features = $nikogiri->get("table.product_features tr")->toArray();
//        print_r($product_features);die();
        $productAttributes = [];
        $i = 0;
        foreach ($product_features as $tr)
        {
            if ( isset($tr['class']) && $tr['class'] == 'header_row')
            {
                $i++;
                $productAttributes[$i]['atr_group_mame'] = trim($tr['__ref']->nodeValue);
            }
            else
            {
                if ( isset($tr['th'][0]['span'][0]) )
                {
                    $title = $tr['th'][0]['span'][0]['#text'];
                }
                else
                {
                    $title = $tr['th'][0]['__ref']->nodeValue;
                }
                $value = $tr['td'][0]['__ref']->nodeValue;
                $productAttributes[$i]['attributes'][] = [
                    $title => $value
                ];
            }
        }

        $description = '';
        if ( preg_match('%<h3>Описание.*?</h3>(.*?)<div class="product-card-info-error%uis', $content, $match) )
        {
            $description = $match[1];
        }

        $price = _String::parseNumber( str_replace(' ', '', @$nikogiri->get("div.price ins.num")->toArray()[0]['#text']) );
        $old_price = _String::parseNumber( str_replace(' ', '', @$nikogiri->get("span.old-price ins.num")->toArray()[0]['#text']) );
//        print_r($price);die();

        $photo = $nikogiri->get(".photo_carousel_link__js")->toArray();

        foreach ( $photo as $a )
        {
            $images[] = $a['href'];
        }
        $out_of_stock = @$nikogiri->get(".out-of-stock_js")->toArray();
        if ( empty($out_of_stock) )
        {
            $stockin = 'in stock';
        }
        else
        {
            $stockin = 'out of stock';
        }

        if ( preg_match('%"manufacturer":"([^"]+)"%uis', $content, $match) )
        {
            $brand = $match[1];
        }

        $breadcrumbs = $nikogiri->get(".breadcrumbs a")->toArray();
//        print_r($breadcrumbs);die();
        $i = 0;
        $categories = [];
        foreach ( $breadcrumbs as $breadcrumb )
        {
            $i++;
            if ( in_array($i, [1,2]) )
                continue;
            $categories[] = $breadcrumb['#text'];
        }

        $data = [
            'source' => $url,
            'categories' => serialize($categories),
            'manufacturer' => $brand,
            'product_name' => $product_name,
            'sku' => $sku,
            'stockin' => $stockin,
            'old_price' => (float)$old_price,
            'price' => (float)$price,
            'description' => $description,
            'main_image' => isset($images) ? $images[0] : '',
            'gallery' => isset($images) ? serialize(@$images) : '',
            'product_attributes' => serialize($productAttributes),
            'hash' => md5($url),
        ];
//        print_r($data);die();
        return $data;
    }
}