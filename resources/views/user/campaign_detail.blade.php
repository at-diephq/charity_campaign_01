@extends('layouts.app')

@section('js')
    @parent
    {{ Html::script('js/user_campaign.js') }}
    <script type="text/javascript">
        $( document ).ready(function() {
            var approve = new Approve(
                '{{ action('CampaignController@approveOrRemove') }}',
                '{{ trans('campaign.approve') }}',
                '{{ trans('campaign.remove') }}',
                '{{ action('ContributionController@confirmContribution') }}',
                '{{ trans('campaign.confirm') }}',
                '{{ trans('campaign.message_confirm') }}'
            );
            approve.init();
        });
    </script>
@stop

@section('content')
    <div id="page-content">
        <div class="row">
        <div class="hide" data-token="{{ csrf_token() }}"></div>
            @include('user.profile')

            <div class="col-md-9 center-panel">
                <div class="block">
                    <div class="block-title themed-background-dark">
                        <h2 class="block-title-light campaign-title"><strong>{{ trans('campaign.request_join') }}</strong></h2>
                    </div>

                    <table class="table table-striped table-bordered table-hover table-responsive">
                        <tr>
                            <th>{{ trans('campaign.index') }}</th>
                            <th>{{ trans('user.avatar') }}</th>
                            <th>{{ trans('user.name') }}</th>
                            <th>{{ trans('user.email') }}</th>
                            <th>{{ trans('user.status') }}</th>
                        </tr>
                        <tbody>
                        @foreach ($campaignUsers as $key => $user)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    <img src="{{ $user->avatar }}" alt="avatar" class="img-responsive img-circle">
                                </td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if (!$user->userCampaign->status)
                                        <div data-campaign-id="{{ $campaign->id }}" data-user-id="{{ $user->id }}">
                                            {!! Form::submit(trans('campaign.approve'), ['class' => 'btn btn-sm btn-success approve']) !!}
                                        </div>
                                    @else
                                        <div data-campaign-id="{{ $campaign->id }}" data-user-id="{{ $user->id }}">
                                            {!! Form::submit(trans('campaign.remove'), ['class' => 'btn btn-sm btn-success approve']) !!}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {{ $campaignUsers->links() }}
                </div>

                <div class="block">
                    <div class="block-title themed-background-dark">
                        <h2 class="block-title-light campaign-title"><strong>{{ trans('campaign.contribute') }}</strong></h2>
                    </div>

                    <table class="table table-striped table-bordered table-hover table-responsive">
                        <tr>
                            <th>{{ trans('campaign.contribution.index') }}</th>
                            <th>{{ trans('campaign.contribution.avatar') }}</th>
                            <th>{{ trans('campaign.contribution.name') }}</th>
                            <th>{{ trans('campaign.contribution.email') }}</th>
                            <th>{{ trans('campaign.contribute') }}</th>
                            <th>{{ trans('campaign.contribution.description') }}</th>
                            <th>{{ trans('campaign.contribution.status') }}</th>
                        </tr>
                        <tbody>
                        @foreach ($contributions as $key => $contribution)
                            <tr>
                                <th scope="row">{{ $key + 1 }}</th>
                                @if ($contribution->user)
                                    <td><img src="{{ $contribution->user->avatar }}" alt="avatar" class="img-responsive img-circle"></td>
                                    <td>{{ $contribution->user->name }}</td>
                                    <td>{{ $contribution->user->email }}</td>
                                @else
                                    <td><img src="{{ config('path.to_avatar_default') }}"  alt="avatar" class="img-responsive img-circle"></td>
                                    <td>{{ $contribution->name }}</td>
                                    <td>{{ $contribution->email }}</td>
                                @endif

                                <td>
                                    @foreach ($contribution->categoryContributions as $value)
                                        <span>{{ $value->category->name }} : <small>{{ $value->amount }}</small></span>
                                        <br>
                                    @endforeach
                                </td>
                                <td>{{ $contribution->description }}</td>
                                <td>
                                    <div data-contribution-id="{{ $contribution->id }}">
                                    @if (!$contribution->status)
                                        {!! Form::submit(trans('campaign.confirm'), ['class' => 'btn btn-sm btn-success confirm']) !!}
                                    @else
                                        {!! Form::submit(trans('campaign.remove'), ['class' => 'btn btn-sm btn-success confirm']) !!}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {{ $contributions->links() }}
                </div>
            </div>
        </div>
    </div>
@stop
