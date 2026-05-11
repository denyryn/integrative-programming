<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Repositories\Interfaces\PostRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PostService
{
  public function __construct(private readonly PostRepositoryInterface $posts)
  {
  }

  public function paginatePosts(int $perPage = 5): LengthAwarePaginator
  {
    return $this->posts->paginate($perPage);
  }

  /**
   * @param  array<string, mixed>  $data
   */
  public function createPost(User $user, array $data): Post
  {
    return $this->posts->createForUser($user, $data);
  }

  public function getPost(Post $post): Post
  {
    return $post;
  }

  /**
   * @param  array<string, mixed>  $data
   */
  public function updatePost(Post $post, array $data): Post
  {
    $this->posts->update($post, $data);

    return $post;
  }

  public function deletePost(Post $post): void
  {
    $this->posts->delete($post);
  }
}
