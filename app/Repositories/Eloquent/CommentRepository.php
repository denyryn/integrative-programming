<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Comment;
use App\Models\User;
use App\Repositories\Interfaces\CommentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommentRepository implements CommentRepositoryInterface
{
  public function paginate(int $perPage = 5): LengthAwarePaginator
  {
    return Comment::query()->paginate($perPage);
  }

  public function createForUser(User $user, array $data): Comment
  {
    return $user->comments()->create($data);
  }

  public function update(Comment $comment, array $data): bool
  {
    return $comment->update($data);
  }

  public function delete(Comment $comment): bool
  {
    return (bool) $comment->delete();
  }
}
