<?php

namespace App\Http\Controllers;

use App\Http\Requests\CampaignRequest;
use App\Models\Campaign;
use App\Models\CategoryUnit;
use App\Models\Event;
use App\Repositories\Campaign\CampaignRepositoryInterface;
use App\Repositories\Category\CategoryRepositoryInterface;
use App\Repositories\Contribution\ContributionRepositoryInterface;
use App\Repositories\Group\GroupRepositoryInterface;
use App\Repositories\Message\MessageRepositoryInterface;
use App\Repositories\Rating\RatingRepositoryInterface;
use App\Repositories\Timeline\TimelineRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\Purifier;
use Illuminate\Http\Request;
use Validator;

class CampaignController extends BaseController
{

    protected $campaignRepository;
    protected $campaign;
    protected $event;
    protected $categoryRepository;
    protected $contributionRepository;
    protected $ratingRepository;
    protected $categoryCampaignRepository;
    protected $timelineRepository;
    protected $userRepository;
    protected $messageRepository;
    protected $groupRepository;

    public function __construct(
        CampaignRepositoryInterface $campaignRepository,
        Campaign $campaign,
        Event $event,
        CategoryRepositoryInterface $categoryRepository,
        ContributionRepositoryInterface $contributionRepository,
        RatingRepositoryInterface $ratingRepository,
        UserRepositoryInterface $userRepository,
        TimelineRepositoryInterface $timelineRepository,
        MessageRepositoryInterface $messageRepository,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->campaignRepository = $campaignRepository;
        $this->campaign = $campaign;
        $this->event = $event;
        $this->categoryRepository = $categoryRepository;
        $this->timelineRepository = $timelineRepository;
        $this->contributionRepository = $contributionRepository;
        $this->ratingRepository = $ratingRepository;
        $this->userRepository = $userRepository;
        $this->messageRepository = $messageRepository;
        $this->groupRepository = $groupRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->dataView['campaigns'] = $this->campaignRepository->getAll()->paginate(config('constants.INDEX_CAMPAIGNS'));
        $this->dataView['countUsers'] = $this->userRepository->count();
        $this->dataView['countCampaigns'] = $this->campaign->count();
        $this->dataView['countEvents'] = $this->event->count();
        $this->dataView['countInteractives'] = $this->contributionRepository->getInteractive();
        $this->dataView['topUser'] = $this->userRepository->getUserByRating()->take(config('constants.TOP_USER'));

        return view('campaign.index', $this->dataView);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->dataView['validateMessage'] = json_encode(trans('campaign.validate'));
        $this->dataView['categoryUnit'] = CategoryUnit::all();

        return view('campaign.create', $this->dataView);
    }

    /**
     * @param CampaignRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CampaignRequest $request)
    {
        $inputs = $request->only([
            'name',
            'image',
            'start_date',
            'end_date',
            'address',
            'lattitude',
            'longitude',
            'description',
            'contribution_type',
            'goal',
            'unit',
        ]);

        $inputs['description'] = Purifier::clean($inputs['description']);
        $campaign = $this->campaignRepository->createCampaign($inputs);

        if (!$campaign) {
            return redirect(action('CampaignController@create'))
                ->withMessage(trans('campaign.create_error'));
        }

        if (!$this->timelineRepository->createTimeline(['campaign_id' => $campaign->id])) {
            return redirect(action('UserController@listUserCampaign', ['id' => auth()->id()]))
                ->with(['alert-danger' => trans('timeline.create_error')]);
        }

        return redirect(action('UserController@listUserCampaign', ['id' => auth()->id()]))
            ->with(['alert-success' => trans('campaign.create_success')]);
    }

    public function showCampaigns()
    {
        $this->dataView['campaigns'] = $this->campaignRepository
            ->getAll()
            ->paginate(config('constants.PAGINATE_CAMPAIGN'));

        return view('campaign.campaigns', $this->dataView);
    }

    public function confirmed($id)
    {
        $this->dataView['detailCampaign'] = $this->campaignRepository->getDetail($id);

        if (!$this->dataView['detailCampaign']) {
            return abort(404);
        }
        $this->dataView['contributionConfirmed'] = $this->contributionRepository->getUserContributionConfirmed($id);

        return view('campaign.list_contribution_confirmed', $this->dataView);
    }

    public function unconfirmed($id)
    {
        $this->dataView['detailCampaign'] = $this->campaignRepository->getDetail($id);

        if (!$this->dataView['detailCampaign']) {
            return abort(404);
        }
        $this->dataView['contributionUnConfirmed'] = $this->contributionRepository->getUserContributionUnConfirmed($id);

        return view('campaign.list_contribution_unconfirmed', $this->dataView);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $this->dataView['detailCampaign'] = $this->campaignRepository->getDetail($id);

        if (!$this->dataView['detailCampaign']) {
            return abort(404);
        }

        // get total contributions
        $this->dataView['results'] = $this->contributionRepository->getValueContribution($id);

        // check user had join campaign
        $this->dataView['userCampaign'] = $this->campaignRepository->checkUserCampaign([
            'user_id' => auth()->id(),
            'campaign_id' => $id,
        ]);

        $this->dataView['members'] = $this->campaignRepository->getMembers($id);
        $this->dataView['contributionConfirmed'] = $this->contributionRepository->getUserContributionConfirmed($id);
        $this->dataView['contributionUnConfirmed'] = $this->contributionRepository->getUserContributionUnConfirmed($id);
        $this->dataView['averageRanking'] = $this->ratingRepository->averageRatingCampaign($this->dataView['detailCampaign']->id);
        $this->dataView['ratingChart'] = $this->ratingRepository->getRatingChart($id);
        $this->dataView['averageRankingUser'] = $this->ratingRepository->averageRatingUser($this->dataView['detailCampaign']->owner->user_id);
        $this->dataView['userRatings'] = $this->ratingRepository->listUserRating($this->dataView['detailCampaign']->owner->user_id);
        $groupId = $this->groupRepository->getGroupIdByCampaignId($id);

        if ($groupId) {
            $this->dataView['messages'] = $this->messageRepository->getMessagesByGroupId($groupId);
        }

        $this->dataView['groupName'] = $this->groupRepository->getGroupNameByCampaignId($id);

        return view('campaign.detail', $this->dataView);
    }

    public function review(Request $request)
    {
        if ($request->ajax()) {
            $campaignId = $request->only('id');
            $this->dataView['results'] = $this->contributionRepository->getValueContribution($campaignId['id']);
            $this->dataView['campaign'] = $this->campaignRepository->getDetail($campaignId['id']);

            if (!$this->dataView['campaign']) {
                return abort(404);
            }

            return view('campaign.review', $this->dataView);
        }
    }

    public function joinOrLeaveCampaign(Request $request)
    {
        if ($request->ajax()) {
            $inputs = $request->only('campaign_id');

            $inputs['user_id'] = auth()->id();

            $result = $this->campaignRepository->joinOrLeaveCampaign($inputs);

            return response()->json($result);
        }
    }

    public function approveOrRemove(Request $request)
    {
        if ($request->ajax()) {
            $inputs = $request->only([
                'campaign_id',
                'user_id',
            ]);

            $result = $this->campaignRepository->approveOrRemove($inputs);

            return response()->json($result);
        }
    }

    public function activeOrCloseCampaign(Request $request)
    {
        if ($request->ajax()) {
            $inputs = $request->only([
                'campaign_id',
            ]);

            $result = $this->campaignRepository->activeOrCloseCampaign($inputs);

            return response()->json($result);
        }
    }

    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), $this->campaign->ruleImage);

        if ($validator->fails()) {
            $message = implode(' ', $validator->errors()->all());

            return view('layouts.upload', [
                'CKEditorFuncNum' => $request->CKEditorFuncNum,
                'data' => [
                    'url' => '',
                    'message' => $message,
                ],
            ]);
        }

        try {
            $image = $this->campaignRepository->uploadImageCampaign($request->file('upload'));

            return view('layouts.upload', [
                'CKEditorFuncNum' => $request->CKEditorFuncNum,
                'data' => [
                    'url' => $image,
                    'message' => trans('campaign.upload_image_success'),
                ],
            ]);
        } catch (\Exception $ex) {
            return [
                'status' => false,
                'message' => trans('campaign.upload_image_error') . $ex->getMessage(),
            ];
        }
    }

    public function searchCampaign(Request $request)
    {
        $result = $this->campaignRepository->searchCampaign($request->get('q'));

        return response()->json($result);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // will remove item in database
    }
}
