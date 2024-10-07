<?php

namespace App\Http\Controllers;

use App\Models\IranCounty;
use Illuminate\Http\Request;

class IranCountyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getIranCountyByStateId($id)
    {
        return IranCounty::where('state_id',$id)->get();
    }


}
