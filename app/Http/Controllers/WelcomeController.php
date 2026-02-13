<?php

/* ----------------------------------------------------------------------------
 * Clientverse - Simple Bookmark Manager
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://github.com/alextselegidis/clientverse
 * ---------------------------------------------------------------------------- */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index(Request $request)
    {
        return view('pages.welcome');
    }
}
