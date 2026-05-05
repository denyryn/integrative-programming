<?php

namespace App\Http\Controllers;

use App\ApiResponseTrait;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{

    use ApiResponseTrait, AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::paginate(5);
        return $this->successResponse(
            \App\Http\Resources\PostResource::collection($posts)->response()->getData(true),
            'Posts retrieved successfully',
            Response::HTTP_OK
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        $validated = $request->validated();
        $post = auth()->user()->posts()->create($validated);
        return $this->successResponse(
            \App\Http\Resources\PostResource::make($post),
            'Post created successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        $this->authorize('view', $post);
        return $this->successResponse(
            \App\Http\Resources\PostResource::make($post),
            'Post retrieved successfully',
            Response::HTTP_OK
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $this->authorize('update', $post);
        $validated = $request->validated();
        $post->update($validated);
        return $this->successResponse(
            \App\Http\Resources\PostResource::make($post),
            'Post updated successfully',
            Response::HTTP_OK
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        $post->delete();
        return $this->successResponse(
            null,
            'Post deleted successfully',
            Response::HTTP_OK
        );
    }
}
