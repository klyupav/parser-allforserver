<?php

namespace App\Http\Controllers;

use App\Models\Donor;
use App\Models\Source;
use Illuminate\Http\Request;

class SourceController extends Controller
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

    /**
     * Display a listing of the resource.
     *
     * @param  string  $donor_name
     * @return IlluminateHttpResponse
     */
    public function index($donor_name)
    {
        if ( $donor = Donor::whereName($donor_name)->first() )
        {
            $sources = Source::whereDonorId($donor->id)->where('type_id', '=', 0)->get();
            return view('source.index', [
                'sources' => $sources,
                'donor_name' => $donor_name,
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  string  $donor_name
     * @return IlluminateHttpResponse
     */
    public function create($donor_name)
    {
        if ( $donor = Donor::whereName($donor_name)->first() )
        {
            $donor_id = $donor->id;
        }
        else
        {
            $donor_id = 0;
        }
        //create new data
        return view('source.create', [
            'donor_name' => $donor_name,
            'donor_id' => $donor_id
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  IlluminateHttpRequest  $request
     * @return IlluminateHttpResponse
     */
    public function store(Request $request)
    {
        // validation
        $this->validate($request,[
            'url' => 'required',
//            'category_id' => 'integer|required',
//            'procent_nakrutki' => 'integer|required',
        ]);
        // create new data
        $source = new Source();
        $source->url = $request->url;
        $source->category_id = $request->category_id;
        $source->procent_nakrutki = isset($request->procent_nakrutki) && !empty($request->procent_nakrutki) ? $request->procent_nakrutki : 0;
        $source->type_id = 0;
        @$request->review == 'on' ? $source->review = true : $source->review = false;
        $source->hash = md5($request->donor_id.$request->url);
        $source->donor_id = $request->donor_id;
        $source->source = '';
        $source->save();
        $donor_name = Donor::findOrFail($source->donor_id)->first()->name;
        return redirect(url("/donor/{$donor_name}/sources"))->with('alert-success','Новый источник добавлен!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $donor_name
     * @param  int  $id
     * @return IlluminateHttpResponse
     */
    public function edit($donor_name, $id)
    {
        $source = Source::findOrFail($id);
        if ( $donor = Donor::whereName($donor_name)->first() )
        {
            $donor_id = $donor->id;
        }
        else
        {
            $donor_id = 0;
        }
        // return to the edit views
        return view('source.edit',[
            'source' => $source,
            'donor_name' => $donor_name,
            'donor_id' => $donor_id
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  IlluminateHttpRequest  $request
     * @param  string  $donor_name
     * @param  int  $id
     * @return IlluminateHttpResponse
     */
    public function update(Request $request, $donor_name, $id)
    {
        // validation
        $this->validate($request,[
            'url' => 'required',
//            'category_id' => 'integer|required',
            'procent_nakrutki' => 'integer|required',
        ]);
        $source = Source::findOrFail($id);
        $source->url = $request->url;
        $source->category_id = $request->category_id;
        $source->procent_nakrutki = isset($request->procent_nakrutki) && !empty($request->procent_nakrutki) ? $request->procent_nakrutki : 0;
        @$request->review == 'on' ? $source->review = true : $source->review = false;
        $source->save();

        return redirect(url("/donor/{$donor_name}/sources"))->with('alert-success','Источник отредактирован!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $donor_name
     * @param  int  $id
     * @return IlluminateHttpResponse
     */
    public function destroy($donor_name, $id)
    {
        // delete data
        $source = Source::findOrFail($id);
        $source->delete();
        return redirect()->back()->with('alert-success','Источник удален!');
    }

    /**
     * Remove the specified resource from storage.
     * @param  IlluminateHttpRequest  $request
     * @return IlluminateHttpResponse
     */
    public function massiveChangeNacenka(Request $request)
    {
        if (!empty($request->ids))
        {
            foreach ( $request->ids as $id )
            {
                Source::whereId($id)->update(['procent_nakrutki' => $request->nacenka]);
            }
        }
        return json_encode([
            'success' => true,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * @param  IlluminateHttpRequest  $request
     * @return IlluminateHttpResponse
     */
    public function massiveChangeReview(Request $request)
    {
        if (!empty($request->ids))
        {
            foreach ($request->ids as $id)
            {
                Source::whereId($id)->update(['review' => $request->review === 'true' ? 1 : 0]);
            }
        }

        return json_encode([
            'success' => true,
        ]);
    }
}
