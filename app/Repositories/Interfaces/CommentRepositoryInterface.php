<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CommentRepositoryInterface
{
  public function paginate(int $perPage = 5): LengthAwarePaginator;

  /**
   * @param  array<string, mixed>  $data
   */
  public function createForUser(User $user, array $data): Comment;

  /**
   * @param  array<string, mixed>  $data
   */
  public function update(Comment $comment, array $data): bool;

  public function delete(Comment $comment): bool;
}
