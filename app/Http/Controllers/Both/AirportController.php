<?php

namespace App\Http\Controllers\Both;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Airport;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
class AirportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $airports = Airport::OrderBy('name', 'ASC')->get();

        return $this->success("All airports" , ["airports" => $airports]);
    }


    
}
