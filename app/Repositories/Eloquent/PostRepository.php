<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Post;
use App\Models\User;
use App\Repositories\Interfaces\PostRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PostRepository implements PostRepositoryInterface
{
  public function paginate(int $perPage = 5): LengthAwarePaginator
  {
    return Post::query()->paginate($perPage);
  }

  public function createForUser(User $user, array $data): Post
  {
    return $user->posts()->create($data);
  }

  public function update(Post $post, array $data): bool
  {
    return $post->update($data);
  }

  public function delete(Post $post): bool
  {
    return (bool) $post->delete();
  }
}
