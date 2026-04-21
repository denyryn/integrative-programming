<?php

namespace App\Http\Controllers;

use App\ApiResponseTrait;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Comment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{
    use ApiResponseTrait, AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $comments = Comment::paginate(5);
        return $this->successResponse(
            \App\Http\Resources\CommentResource::collection($comments)->response()->getData(true),
            'Comments retrieved successfully',
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
    public function store(StoreCommentRequest $request)
    {
        $validated = $request->validated();
        $comment = auth()->user()->comments()->create($validated);
        return $this->successResponse(
            \App\Http\Resources\CommentResource::make($comment),
            'Comment created successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        $this->authorize('view', $comment);
        return $this->successResponse(
            \App\Http\Resources\CommentResource::make($comment),
            'Comment retrieved successfully',
            Response::HTTP_OK
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $this->authorize('update', $comment);
        $validated = $request->validated();
        $comment->update($validated);
        return $this->successResponse(
            \App\Http\Resources\CommentResource::make($comment),
            'Comment updated successfully',
            Response::HTTP_OK
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete();
        return $this->successResponse(
            null,
            'Comment deleted successfully',
            Response::HTTP_NO_CONTENT
        );
    }
}
