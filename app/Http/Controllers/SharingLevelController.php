<?php

namespace App\Http\Controllers;

use App\SharingLevel;

class SharingLevelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sharingLevel = SharingLevel::all();

        return compact('sharingLevel');
    }
}
