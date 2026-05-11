<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PostRepositoryInterface
{
  public function paginate(int $perPage = 5): LengthAwarePaginator;

  /**
   * @param  array<string, mixed>  $data
   */
  public function createForUser(User $user, array $data): Post;

  /**
   * @param  array<string, mixed>  $data
   */
  public function update(Post $post, array $data): bool;

  public function delete(Post $post): bool;
}
