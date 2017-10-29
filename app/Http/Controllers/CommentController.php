<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Repositories\Comment\CommentRepositoryInterface;
use LRedis;

class CommentController extends Controller
{
    protected $comment;
    protected $commentRepository;

    public function __construct(Comment $comment, CommentRepositoryInterface $commentRepository)
    {
        $this->comment = $comment;
        $this->commentRepository = $commentRepository;
    }

    public function store(CommentRequest $request)
    {
        if ($request->ajax()) {
            $inputs = $request->only([
                'campaign_id',
                'name',
                'email',
                'text',
                'event_id',
            ]);

            $comment = $this->commentRepository->createComment($inputs);

            if (is_null($inputs['campaign_id'])) {
                $count = $this->commentRepository->getTotalCommentByCampaign('event_id', $inputs['event_id']);
            } else {
                $count = $this->commentRepository->getTotalCommentByCampaign('campaign_id', $inputs['campaign_id']);
            }

            $result = [
                'html' => view('layouts.comment', ['comment' => $comment])->render(),
                'count' => $count,
                'success' => true,
            ];

            if (!$comment->user_id) {
                $result['name'] = $comment->name;
            } else {
                $comment = $this->commentRepository->getDetail($comment->id);
                $result['name'] = $comment->user->name;
            }

            $result['campaign_id'] = $inputs['campaign_id'] ?: $inputs['event_id'];

            $redis = LRedis::connection();
            $redis->publish('comment', json_encode($result));
        }

        // asdfasfasfd
        return response()->json(['success' => false]);
    }
}
