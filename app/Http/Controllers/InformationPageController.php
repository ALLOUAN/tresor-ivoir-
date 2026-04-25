<?php

namespace App\Http\Controllers;

use App\Models\InformationPage;
use Illuminate\View\View;

class InformationPageController extends Controller
{
    public function show(InformationPage $informationPage): View
    {
        return view('information.show', [
            'page' => $informationPage,
        ]);
    }
}
