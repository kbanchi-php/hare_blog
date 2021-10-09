<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $posts = Post::latest()->paginate(4);
        $posts = Post::with('user')->latest()->paginate(4);
        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostRequest $request)
    {
        $post = new Post();
        $post->fill($request->all());
        $post->user_id = auth()->user()->id;

        $file = $request->file('image');
        $post->image = self::createFileName($file);

        // dd($post);

        // begin transaction
        DB::beginTransaction();
        try {

            // insert db
            $post->save();

            // file save
            if (!Storage::putFileAs('images/posts', $file, $post->image)) {
                throw new \Exception('faild to save image...');
            }

            // commit
            db::commit();
        } catch (\Exception $e) {
            // rollback
            DB::rollback();
            return back()->withInput()->withErrors($e->getMessage());
        }

        return redirect()
            ->route('posts.show', $post)
            ->with('notice', 'complete create new post.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        $post = Post::with(['user'])->find($post->id);
        $comments = $post->comments()->latest()->get()->load(['user']);
        return view('posts.show', compact('post', 'comments'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PostRequest $request, Post $post)
    {
        if ($request->user()->cannot('update', $post)) {
            return redirect()->route('posts.show', $post)
                ->withErrors('you can not edit post written by others.');
        }

        $file = $request->file('image');
        if ($file) {
            $delete_file_path = $post->image_url;
            $post->image = self::createFileName($file);
        }

        $post->fill($request->all());

        // begin transaction
        DB::beginTransaction();
        try {

            // db insert
            $post->save();

            if ($file) {
                // file save
                if (!Storage::putFileAs('images/posts', $file, $post->image)) {
                    throw new \Exception('faild to save image...');
                }
                // delete old file
                if (!Storage::delete($delete_file_path)) {
                    throw new \Exception('faild to delete old image...');
                }
            }

            // commit
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->withErrors($e->getMessage());
        }

        return redirect()->route('posts.show', $post)
            ->with('notice', 'complete edit post.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        // begin transaction
        DB::beginTransaction();
        try {

            // db delete
            $post->delete();

            // file delete
            if (!Storage::delete($post->image_path)) {
                throw new \Exception('faild to delete image...');
            }

            // commit
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->withErrors($e->getMessage());
        }

        return redirect()->route('posts.index')
            ->with('notice', 'complete delete post.');
    }

    private static function createFileName($file)
    {
        return date('YmdHis') . '_' . $file->getClientOriginalName();
    }
}
