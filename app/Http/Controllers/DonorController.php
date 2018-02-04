<?php

namespace App\Http\Controllers;

use App\Models\Donor;
use App\Models\Source;
use Illuminate\Http\Request;

class DonorController extends Controller
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
     * @return IlluminateHttpResponse
     */
    public function index()
    {
        return view('donor.index', [
            'donors' => Donor::orderBy('name')->get()
        ]);
    }

    /**
     * Display a listing of the sources donor.
     *
     * @param  string  $name
     * @return IlluminateHttpResponse
     */
    public function sources($donor_name)
    {
        if ( $donor = Donor::whereName($donor_name)->first() )
        {
            $sources = Source::whereDonorId($donor->id)->get();
            return view('source.index', [
                'sources' => $sources
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return IlluminateHttpResponse
     */
    public function create()
    {
        //create new data
        return view('donor.create');
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
            'name'=> 'required',
            'link' => 'required',
            'class' => 'required',
//            'used' => 'required|boolean',
        ]);
        // create new data
        $donor = new Donor();
        $donor->name = $request->name;
        $donor->link = $request->link;
        $donor->class = $request->class;
        @$request->used == 'on' ? $donor->used = true : $donor->used = false;
        $donor->save();
        return redirect()->route('donor.index')->with('alert-success','Донор создан!');

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return IlluminateHttpResponse
     */
    public function edit($id)
    {
        $donor = Donor::findOrFail($id);
        // return to the edit views
        return view('donor.edit',compact('donor'));
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
            'name'=> 'required',
            'link' => 'required',
            'class' => 'required',
            //'used' => 'required|boolean',
        ]);
        $donor = Donor::findOrFail($id);
        $donor->name = $request->name;
        $donor->link = $request->link;
        $donor->class = $request->class;
        @$request->used == 'on' ? $donor->used = true : $donor->used = false;
        $donor->save();

        return redirect()->route('donor.index')->with('alert-success','Донор изменен!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return IlluminateHttpResponse
     */
    public function destroy($id)
    {
        // delete data
        $donor = Donor::findOrFail($id);
        $donor->delete();
        return redirect()->route('donor.index')->with('alert-success','Донор удален!');
    }
}
