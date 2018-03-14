{{--  **必要** 延伸 layouts.master  --}}
@extends('layouts.master')

@section('title')
	{{ config('app.name') }}
@endsection

{{--  載入 master 版型專用的 css  --}}
@section('styles')
    <link rel="stylesheet" href="{{ URL::to('src/master/css/app.css') }}">
@endsection

{{--  中間內容部分  --}}
@section('content')
<div class="full"></div>
	@if (count($issues) > 0)
		@foreach($issues as $issue)
			<div class="card text-center">
				<div class="card-header">
					<h5 class="card-title">{{ $issue->title }}</h5>
				</div>
				<div class="card-body">
					<div class="card-text issue-short-intro">
						<?php
							echo htmlspecialchars_decode($issue->description);
						?>
					</div>
					<a href="{{ route('issue.detail', ['id' => $issue->id]) }}" class="btn btn-primary">繼續閱讀...</a>
				</div>
				<div class="card-footer text-muted">
					{{ \App\Constant::getTimeFromInt($issue->check_point) }}
					結算
				</div>
			</div>
			<br/>
		@endforeach
	@endif
</div>
@endsection

{{--  載入 master 版型專用的 js  --}}
@section('scripts')
    <script src="{{ URL::to('src/master/js/app.js') }}"></script>
@endsection