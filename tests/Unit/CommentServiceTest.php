<?php

declare(strict_types=1);

use App\Models\Comment;
use App\Models\User;
use App\Repositories\Interfaces\CommentRepositoryInterface;
use App\Services\CommentService;
use Illuminate\Pagination\LengthAwarePaginator;

afterEach(function () {
  Mockery::close();
});

test('paginateComments delegates to repository', function () {
  $paginator = new LengthAwarePaginator(items: [], total: 0, perPage: 5, currentPage: 1);

  $repo = Mockery::mock(CommentRepositoryInterface::class);
  $repo->shouldReceive('paginate')
    ->once()
    ->with(5)
    ->andReturn($paginator);

  $service = new CommentService($repo);

  expect($service->paginateComments(5))->toBe($paginator);
});

test('createComment delegates to repository', function () {
  $user = new User;
  $data = [
    'comment' => 'Nice',
    'post_id' => 123,
  ];

  $comment = new Comment($data);

  $repo = Mockery::mock(CommentRepositoryInterface::class);
  $repo->shouldReceive('createForUser')
    ->once()
    ->with($user, $data)
    ->andReturn($comment);

  $service = new CommentService($repo);

  expect($service->createComment($user, $data))->toBe($comment);
});

test('getComment returns same instance', function () {
  $repo = Mockery::mock(CommentRepositoryInterface::class);
  $service = new CommentService($repo);

  $comment = new Comment(['comment' => 'X', 'post_id' => 123]);

  expect($service->getComment($comment))->toBe($comment);
});

test('updateComment delegates to repository and returns comment instance', function () {
  $comment = new Comment(['comment' => 'Old', 'post_id' => 123]);
  $data = ['comment' => 'New'];

  $repo = Mockery::mock(CommentRepositoryInterface::class);
  $repo->shouldReceive('update')
    ->once()
    ->with($comment, $data)
    ->andReturnTrue();

  $service = new CommentService($repo);

  expect($service->updateComment($comment, $data))->toBe($comment);
});

test('deleteComment delegates to repository', function () {
  $comment = new Comment(['comment' => 'X', 'post_id' => 123]);

  $repo = Mockery::mock(CommentRepositoryInterface::class);
  $repo->shouldReceive('delete')
    ->once()
    ->with($comment)
    ->andReturnTrue();

  $service = new CommentService($repo);

  $service->deleteComment($comment);

  expect(true)->toBeTrue();
});
