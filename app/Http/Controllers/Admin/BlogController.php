<?php

namespace App\Http\Controllers\Admin;


use App\Blogpost;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

class BlogController extends Controller
{
    public function newBlogPost()
    {
        return view('blog.newblog');
    }

    public function create(Request $request)
    {
        $blogPost = new Blogpost;

        $blogPost->titel = $request->titel;
        $blogPost->inhoud = $request->inhoud;
        $blogPost->afbeelding = $request->afbeelding;

        $blogPost->save();

        return redirect('/beheer');
    }

    public function edit($id)
    {
        $blog = Blogpost::where('id', $id)->first();

        return view('blog.editBlogPost', compact('blog'));
    }

    public function blogUpdate($id, Request $request)
    {
        $blogPost = Blogpost::where('id', $id)->first();

        $blogPost->titel = $request->titel;
        $blogPost->inhoud = $request->inhoud;
        $blogPost->afbeelding = $request->afbeelding;

        $blogPost->save();

        return redirect()->back();
    }

    public function remove($id)
    {
        Blogpost::find($id)->delete();

        return redirect('/beheer');
    }
}
