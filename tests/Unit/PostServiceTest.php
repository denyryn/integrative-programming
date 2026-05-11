<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\User;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Services\PostService;
use Illuminate\Pagination\LengthAwarePaginator;

afterEach(function () {
  Mockery::close();
});

test('paginatePosts delegates to repository', function () {
  $paginator = new LengthAwarePaginator(items: [], total: 0, perPage: 5, currentPage: 1);

  $repo = Mockery::mock(PostRepositoryInterface::class);
  $repo->shouldReceive('paginate')
    ->once()
    ->with(5)
    ->andReturn($paginator);

  $service = new PostService($repo);

  expect($service->paginatePosts(5))->toBe($paginator);
});

test('createPost delegates to repository', function () {
  $user = new User;
  $data = [
    'title' => 'Hello',
    'status' => 'draft',
    'content' => 'World',
  ];

  $post = new Post($data);

  $repo = Mockery::mock(PostRepositoryInterface::class);
  $repo->shouldReceive('createForUser')
    ->once()
    ->with($user, $data)
    ->andReturn($post);

  $service = new PostService($repo);

  expect($service->createPost($user, $data))->toBe($post);
});

test('getPost returns same instance', function () {
  $repo = Mockery::mock(PostRepositoryInterface::class);
  $service = new PostService($repo);

  $post = new Post(['title' => 'X', 'status' => 'draft', 'content' => 'Y']);

  expect($service->getPost($post))->toBe($post);
});

test('updatePost delegates to repository and returns post instance', function () {
  $post = new Post(['title' => 'Old', 'status' => 'draft', 'content' => 'Y']);
  $data = ['title' => 'New'];

  $repo = Mockery::mock(PostRepositoryInterface::class);
  $repo->shouldReceive('update')
    ->once()
    ->with($post, $data)
    ->andReturnTrue();

  $service = new PostService($repo);

  expect($service->updatePost($post, $data))->toBe($post);
});

test('deletePost delegates to repository', function () {
  $post = new Post(['title' => 'X', 'status' => 'draft', 'content' => 'Y']);

  $repo = Mockery::mock(PostRepositoryInterface::class);
  $repo->shouldReceive('delete')
    ->once()
    ->with($post)
    ->andReturnTrue();

  $service = new PostService($repo);

  $service->deletePost($post);

  expect(true)->toBeTrue();
});
