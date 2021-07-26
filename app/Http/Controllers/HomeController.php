<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
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
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index()
    {
        return view('pages.home');
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function getUser()
    {
        $user['id'] = Auth::user()->id;
        $user['name'] = Auth::user()->name;
        $user['is_admin'] = Auth::user()->is_admin;
        return new Response(json_encode($user, true) , Response::HTTP_OK);
    }
}
