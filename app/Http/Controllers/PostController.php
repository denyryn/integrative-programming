<?php

namespace App\Http\Controllers;

use App\ApiResponseTrait;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\User;
use App\Services\PostService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{
    use ApiResponseTrait, AuthorizesRequests;

    public function __construct(private readonly PostService $postService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = $this->postService->paginatePosts(5);

        return $this->successResponse(
            PostResource::collection($posts)->response()->getData(true),
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

        $user = $request->user();
        abort_unless($user instanceof User, Response::HTTP_UNAUTHORIZED);

        $post = $this->postService->createPost($user, $validated);

        return $this->successResponse(
            PostResource::make($post),
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

        $post = $this->postService->getPost($post);

        return $this->successResponse(
            PostResource::make($post),
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

        $post = $this->postService->updatePost($post, $validated);

        return $this->successResponse(
            PostResource::make($post),
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

        $this->postService->deletePost($post);

        return response()->noContent(HttpResponse::HTTP_NO_CONTENT);
    }
}
