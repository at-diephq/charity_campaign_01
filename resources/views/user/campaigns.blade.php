@extends('layouts.app')

@section('js')
    @parent
    {{ Html::script('js/active_campaign.js') }}
    <script type="text/javascript">
        $( document ).ready(function() {
            var active = new Active(
                '{{ trans('campaign.active') }}',
                '{{ trans('campaign.close') }}',
                '{{ action('CampaignController@activeOrCloseCampaign') }}',
                '{{ trans('campaign.message_confirm') }}'
            );
            active.init();
        });
    </script>
@stop

@section('content')
    <div id="page-content">
        <div class="row">
            @include('user.profile')
            <div class="col-md-9 center-panel">
                <div class="block">
                    <div class="block-title themed-background-dark">
                        <h2 class="block-title-light campaign-title"><strong>{{ trans('user.your_campaign') }}</strong></h2>
                    </div>

                    <div class="block-content-full">
                        <div class="timeline">
                            <table class="table table-striped table-bordered table-hover table-responsive">

                                <tr>
                                    <th>{{ trans('campaign.index') }}</th>
                                    <th>{{ trans('campaign.name') }}</th>
                                    <th>{{ trans('campaign.address') }}</th>
                                    <th>{{ trans('campaign.start_date') }}</th>
                                    <th>{{ trans('campaign.end_date') }}</th>
                                    <th>{{ trans('campaign.status') }}</th>
                                </tr>
                                <tbody>
                                @foreach ($campaigns as $key => $campaign)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td><a href="{{ action('UserController@manageCampaign', ['userId' => $user->id, 'campaignId' => $campaign->id]) }}">{{ $campaign->name }}</a></td>
                                        <td>{{ $campaign->address }}</td>
                                        <td>{{ $campaign->start_time }}</td>
                                        <td>{{ $campaign->end_time }}</td>
                                        <td>
                                            <div data-campaign-id="{{ $campaign->id }}">
                                                @if (!$campaign->status)
                                                    {!! Form::submit(trans('campaign.active'), ['class' => 'btn btn-sm btn-success active']) !!}
                                                @else
                                                    {!! Form::submit(trans('campaign.close'), ['class' => 'btn btn-sm btn-success active']) !!}
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>

                            </table>
                            {{ $campaigns->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
