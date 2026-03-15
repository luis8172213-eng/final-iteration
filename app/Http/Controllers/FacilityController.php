<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facility;

class FacilityController extends Controller
{
    public function list()
    {
        $facilities = Facility::active()->get(['name']);
        return response()->json($facilities);
    }
}
