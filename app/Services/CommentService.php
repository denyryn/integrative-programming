<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Comment;
use App\Models\User;
use App\Repositories\Interfaces\CommentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommentService
{
  public function __construct(private readonly CommentRepositoryInterface $comments)
  {
  }

  public function paginateComments(int $perPage = 5): LengthAwarePaginator
  {
    return $this->comments->paginate($perPage);
  }

  /**
   * @param  array<string, mixed>  $data
   */
  public function createComment(User $user, array $data): Comment
  {
    return $this->comments->createForUser($user, $data);
  }

  public function getComment(Comment $comment): Comment
  {
    return $comment;
  }

  /**
   * @param  array<string, mixed>  $data
   */
  public function updateComment(Comment $comment, array $data): Comment
  {
    $this->comments->update($comment, $data);

    return $comment;
  }

  public function deleteComment(Comment $comment): void
  {
    $this->comments->delete($comment);
  }
}
