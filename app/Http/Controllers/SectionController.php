<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Section;

class SectionController extends Controller
{
    public function index() {
        return response()->json([
            'message' => 'Getting sections successfully.',
            'data'    => Section::all()
        ], 200);
    }
}
