@extends('layouts.without_header')

@section('title')
	{{ config('app.name') }} Message
@endsection

@section('styles')
	<link rel="stylesheet" href="{{ URL::to('src/message/css/message.css') }}">
@endsection

@section('content')
<div class="container">
	<div id="window" class="row">
		<div id="messageList" class="col-5 list-group list-group-flush">
			目前議題：
			<h3 id="currentIssue">
				{{ isset($myUserIssue) ? $myUserIssue->issue->title : '' }}
			</h3>
			身分：
			<h4 id="currentUser">
				@if (isset($myUserIssue))
					<img
						src="{{ URL::to('src/nicknames/'.$myUserIssue->nickname->img.'.png') }}"
						alt="{{ $myUserIssue->nickname->name }}"
						title="{{ $myUserIssue->color }}的{{ $myUserIssue->nickname->name }}"
						style="width: 25px; height: 25px; background: {{ $myUserIssue->color }};">
					{{ $myUserIssue->seq.'號辯論士' }}
				@endif
			</h4>
			@if (isset($targetUserIssue))
				<button id="user_{{ $targetUserIssue->user_id }}_{{ $targetUserIssue->issue_id }}"
					type="button" class="list-group-item list-group-item-action wait2focus
					{{ $targetUserIssue->has_new_message ? 'list-group-item-warning' : '' }}"
					onclick="changeTarget({{ $targetUserIssue->user_id }}, {{ $targetUserIssue->issue_id }})">
					<div class="row">
						<font class="float-left" size="1">{{ $targetUserIssue->issueTitle }}</font>
					</div>
					<div class="row">
						<img
							src="{{ URL::to('src/nicknames/'.$targetUserIssue->nickname->img.'.png') }}"
							alt="{{ $targetUserIssue->nickname->name }}"
							title="{{ $targetUserIssue->color }}的{{ $targetUserIssue->nickname->name }}"
							style="width: 25px; height: 25px; background: {{ $targetUserIssue->color }};">
						{{ $targetUserIssue->seq.'號辯論士' }}
					</div>

				</button>
			@endif
			@if ($allMessage != null)
			{{--  {{ \json_encode($allMessage) }}  --}}
				@foreach ($allMessage as $issueId => $issue)
					@if ($issueId != 'has_new_message')
						@foreach($issue as $userId => $user)
							@if ($userId != 'has_new_message')
							{{--  {{ \json_encode($user) }}  --}}
								@if (isset($targetUserIssue) &&
									$targetUserIssue->user_id == $user->userIssue->user_id &&
									$targetUserIssue->issue_id == $user->userIssue->issue_id)
								@else
									<button id="user_{{ $user->userIssue->user_id }}_{{ $user->userIssue->issue_id }}"
										type="button" class="list-group-item list-group-item-action
										{{ (isset($user->has_new_message) && $user->has_new_message) ? 'list-group-item-warning' : '' }}"
										onclick="changeTarget({{ $user->userIssue->user_id }}, {{ $user->userIssue->issue_id }})">
										<div class="row">
											<font class="float-left" size="1">{{ $user->userIssue->issueTitle }}</font>
										</div>
										<div class="row">
											<img
												src="{{ URL::to('src/nicknames/'.$user->userIssue->nickname->img.'.png') }}"
												alt="{{ $user->userIssue->nickname->name }}"
												title="{{ $user->userIssue->color }}的{{ $user->userIssue->nickname->name }}"
												style="width: 25px; height: 25px; background: {{ $user->userIssue->color }};">
											{{ $user->userIssue->seq.'號辯論士' }}
										</div>
									</button>
								@endif
							@endif
						@endforeach
					@endif
				@endforeach
			@endif
		</div>
		<div class="col-7 list-group list-group-flush">
			<ul class="list-group">
				<div id="messageContent" class="row"></div>
			</ul>
			<div class="input-group">
				<textarea id="inputMessage" placeholder="輸入您的訊息" aria-label="輸入您的訊息" rows="1"></textarea>
				<span class="input-group-btn">
					<button class="btn btn-secondary" type="button" onclick="sendMessage()">傳送</button>
				</span>
			</div>
		</div>
	</div>
</div>
@endsection

@section('scripts')
	<script src="{{ URL::to('src/message/js/message.js') }}"></script>
@endsection