@extends('layouts.master')

@section('title')
	個人檔案
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ URL::to('src/master/css/app.css') }}">
@endsection

@section('content')
<div class="full">
	<div class="row">
		<div class="col-12">
			<h3>
				@if ($myself)
					{{ $title }}
				@else
					<img
						src="{{ URL::to('src/nicknames/'.$title->img.'.png') }}"
						alt="{{ $title->name }}"
						title="{{ $title->color }}的{{ $title->name }}"
						style="width: 25px; height: 25px; background: {{ $title->color }};">
					<strong> {{ $title->seq }}號辯論士</strong>
				@endif
				的議題表態歷史
				@if (!$myself && $myselfInIssue)
					<a href="{{ route('message.getMessageIndex', ['targetId' => \base64_encode($userIssueId)]) }}" target="_blank" class="btn btn-primary">
						開始聊天
					</a>
				@endif
			</h3>
		</div>
	</div>
    <ul class="nav nav-tabs" id="IssueTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="joinedIssues-tab" data-toggle="tab" href="#joinedIssues" role="tab" aria-controls="joinedIssues" aria-selected="true">參與議題</a>
		</li>
		@if ($myself)
			<li class="nav-item">
				<a class="nav-link" id="create-tab" data-toggle="tab" href="#create" role="tab" aria-controls="create" aria-selected="false">編輯議題</a>
			</li>
		@endif
    </ul>
    <div class="tab-content" id="IssueTabContent">
        <div class="tab-pane fade show active" id="joinedIssues" role="tabpanel" aria-labelledby="joinedIssues-tab">
			<div class="list-group" id="list-tab" role="tablist">
				<?php $seq = 0; ?>
				@foreach ($issues as $issue)
					<div class="list-group-item" id="list-joinedIssues-list-{{$seq}}" role="tab" aria-controls="joinedIssues">
						<div class="row">
							<div class="col-12 col-md-4">
								<h3>{{ $issue->title }}</h3>
							</div>
							<div class="col-6 col-md-2 {{ ($myself) ? 'dropdown' : '' }}">
								@if ($myself)
									<button id="currentStatus-{{$seq}}" class="btn {{($issue->status == \App\Constant::USER_ISSUE_STATUS_POS) ? 'btn-primary' : 'btn-danger'}} dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										{{ ($issue->status == \App\Constant::USER_ISSUE_STATUS_POS) ? '正方' : '反方' }}
									</button>
									<div class="dropdown-menu" aria-labelledby="currentStatus-{{$seq}}">
										<button id="status-{{$seq}}" class="dropdown-item"
											onclick="changeStatus({{ !isset($issue->issue_id) ? 1:0 }}, {{ $issue->id }},
												{{ !($issue->status == \App\Constant::USER_ISSUE_STATUS_POS) ? \App\Constant::USER_ISSUE_STATUS_POS : \App\Constant::USER_ISSUE_STATUS_NEG }},
												{{ $seq }})" >
											{{ !($issue->status == \App\Constant::USER_ISSUE_STATUS_POS) ? '正方' : '反方' }}
										</button>
									</div>
								@elseif($issue->status == \App\Constant::USER_ISSUE_STATUS_POS)
									<button class="btn btn-primary">正方</button>
								@else
									<button class="btn btn-danger">反方</button>
								@endif
							</div>
							<div class="col-6 col-md-4">
								<div class="progress">
									<div id="progressBarPos-{{$seq}}" class="progress-bar progress-bar-striped" role="progressbar" style="width:{{ $issue->posPercent }}%"
										aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
										{{ $issue->posPercent }}%
									</div>
									<div id="progressBarNeg-{{$seq}}" class="progress-bar progress-bar-striped bg-danger" role="progressbar" style="width:{{ $issue->negPercent }}%"
										aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
										{{ $issue->negPercent }}%
									</div>
								</div>
							</div>
							@if ($myself)
								<div class="col-12 col-md-2 dropdown">
									<button id="isCommentPublicDropDown-{{$seq}}" class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										{{ ($issue->isCommentPublic) ? '公開留言' : '隱藏留言' }}
									</button>
									<div class="dropdown-menu" aria-labelledby="isCommentPublicDropDown-{{$seq}}">
										<button id="isCommentPublic-{{$seq}}" class="dropdown-item"
											onclick="changePublic({{ !isset($issue->issue_id) ? 1:0 }}, {{ $issue->id }}, {{ $seq }})">
											{{ (!$issue->isCommentPublic) ? '公開留言' : '隱藏留言' }}
										</button>
									</div>
								</div>
							@endif
							<br>
							@if (($myself || $issue->isCommentPublic) && isset($issue->messages))
								<p class="col-12">
									<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseComment-{{$seq}}" aria-expanded="false" aria-controls="collapseComment">顯示留言...</button>
								</p>
								<ul class="list-group col-12">
									<div class="collapse" id="collapseComment-{{$seq}}">
										<div id="issueCollapseComment-{{$seq}}" class="row">
											@foreach ($issue->messages as $created_at => $message)
												<div class="col-12">
													@if ($message->status == \App\Constant::USER_ISSUE_STATUS_POS)
														<li class="list-group-item"
															style="margin: 5px; border-left:5px #0000FF solid;
																word-break: break-all; word-wrap: break-word;
																text-align:center">
															<div>
																@if (!isset($message->delete))
																	<?php echo nl2br($message->comment); ?>
																@else
																	<strong>Jugze： 留言遭到刪除</strong>
																@endif
															</div>
														</li>
													@else
														<li class="list-group-item"
															style="margin: 5px; border-right:5px #FF0000 solid;
																word-break: break-all; word-wrap: break-word;
																text-align:center">
															<div>
																@if (!isset($message->delete))
																	<?php echo nl2br($message->comment); ?>
																@else
																	<strong>Jugze： 留言遭到刪除</strong>
																@endif
															</div>
														</li>
													@endif
												</div>
											@endforeach
										</div>
									</div>
								</ul>
							@endif
						</div>
					</div>
					<?php $seq++; ?>
				@endforeach
	        </div>
		</div>
		@if ($myself)
			<div class="tab-pane fade" id="create" role="tabpanel" aria-labelledby="create-tab">
				<div class="list-group" id="list-tab-create" role="tablist">
					@foreach ($issues as $issue)
						@if ($issue->user_id == Auth::user()->id)
							<div class="list-group-item" role="tab" aria-controls="create">
								<div class="row">
									<div class="col-md-8 col-12">
										<h3>{{ $issue->title }}</h3>
									</div>
									<div class="col-md-4 col-12">
										<a class="btn btn-primary"
											href="{{ route('issue.createIssue', ['issueType' => ($issue->isExtend ? 'extend' : 'issue'), 'id' => $issue->id]) }}">
											編輯
										</a>
									</div>
								</div>
							</div>
						@endif
					@endforeach
				</div>
			</div>
		@endif
	</div>
</div>
@endsection

{{--  載入 master 版型專用的 js  --}}
@section('scripts')
	<script src="{{ URL::to('src/master/js/app.js') }}"></script>
	<script src="{{ URL::to('src/user/js/profile.js') }}"></script>
@endsection