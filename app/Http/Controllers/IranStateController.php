<?php

namespace App\Http\Controllers;

use App\Models\IranState;
use Illuminate\Http\Request;

class IranStateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getIranStateList()
    {
        return IranState::all();
    }

}
