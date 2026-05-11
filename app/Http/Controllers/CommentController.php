<?php

namespace App\Http\Controllers;

use App\ApiResponseTrait;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\User;
use App\Services\CommentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{
    use ApiResponseTrait, AuthorizesRequests;

    public function __construct(private readonly CommentService $commentService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Comment::class);

        $comments = $this->commentService->paginateComments(5);

        return $this->successResponse(
            CommentResource::collection($comments)->response()->getData(true),
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
        $this->authorize('create', Comment::class);

        $validated = $request->validated();

        $user = $request->user();
        abort_unless($user instanceof User, Response::HTTP_UNAUTHORIZED);

        $comment = $this->commentService->createComment($user, $validated);

        return $this->successResponse(
            CommentResource::make($comment),
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

        $comment = $this->commentService->getComment($comment);

        return $this->successResponse(
            CommentResource::make($comment),
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

        $comment = $this->commentService->updateComment($comment, $validated);

        return $this->successResponse(
            CommentResource::make($comment),
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

        $this->commentService->deleteComment($comment);

        return response()->noContent(HttpResponse::HTTP_NO_CONTENT);
    }
}
