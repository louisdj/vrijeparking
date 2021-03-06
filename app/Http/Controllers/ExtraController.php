<?php

namespace App\Http\Controllers;

use App\Blogpost;
use Illuminate\Http\Request;

use App\Http\Requests;

class ExtraController extends Controller
{
    public function team()
    {
        return view('extra.team');
    }

    public function blog()
    {
        $posts = Blogpost::all();

        return view('blog.blog', compact('posts'));
    }

    public function blogPost($titel)
    {
        $blog = Blogpost::where('titel', $titel)->first();

        return view('blog.blogPost', compact('blog'));
    }
}
