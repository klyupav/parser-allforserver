<?php

namespace App\Http\Controllers;

use App\Models\ProxyList;
use Illuminate\Http\Request;

class ProxyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
//        print_r(ProxyList::getListArray());die();
//        print_r(ProxyList::getNextProxy());die();
        $list = '';
        foreach ( ProxyList::get()->all() as $row)
        {
            $list .= $row->proxy."\n";
        }
        return view('proxy.index', [
            'proxy_list' => $list,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  IlluminateHttpRequest  $request
     * @param  int  $id
     * @return IlluminateHttpResponse
     */
    public function update(Request $request, $id)
    {
        // validation
        $this->validate($request,[
            'list' => 'required',
        ]);
//        print_r($request->list);die();
        $list = ProxyList::getListArray($request->list);
        ProxyList::truncate();
        foreach ( $list as $proxy )
        {
            ProxyList::insert(['proxy' => $proxy]);
        }

        return redirect()->route('proxy.index')->with('alert-success','Список прокси сохранен!');
    }
}
