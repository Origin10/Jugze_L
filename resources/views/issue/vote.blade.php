@extends('layouts.master')

@section('title')
	@if(isset($title))
		{{ $title.'延伸投票' }}
	@else
		{{ config('app.name') }}
	@endif
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ URL::to('src/master/css/app.css') }}">
@endsection

@section('content')
<?php
	$now = new DateTime('now');
?>
<div class="full">
	<center>
		<h2> {{ $title }} </h2>
		<h3> - 延伸議題投票 </h3>
		<br>
		<p>投票截止:{{ \App\Constant::getTimeFromInt($subIssue->deadline) }}</p>
		<p>{{ \App\Constant::getLeftTimeFromInt($subIssue->deadline) }}</p>
		@if(count($errors) > 0)
			<div class="alert alert-danger">
				@foreach($errors->all() as $error)
					<p>{{ $error }}</p>
				@endforeach
			</div>
		@endif
	</center>

	<ul class="list-group">
		@foreach ($options as $option)
			<li class="list-group-item d-flex justify-content-between align-items-center">
				<input name="option" id="{{ $option->id }}" type="checkbox" {{ (isset($alreadyVote['id'.$option->id])) ? 'checked' : '' }}>
				{{ $option->title }}
				<span id="count_{{ $option->id }}" class="badge badge-primary badge-pill">{{ count($option->voters) }}</span>
			</li>
		@endforeach
	</ul>
	<br>
	<br>
	{{--  沒有提出過選項的人可以提出選項  --}}
	@if (!$alreadyPost)
		<form action="{{ route('issue.createSubIssueVote', ['id' => $subIssue->id]) }}" method="post">
			<div class="input-group mb-3">
				<input name="title" type="text" class="form-control" placeholder="輸入欲新增的投票選項">
				<div class="input-group-append">
					<button class="btn btn-outline-secondary" type="submit">新增</button>
				</div>
			</div>
			{{ csrf_field() }}
		</form>
	@endif


</div>

@endsection
</div>
{{--  載入 master 版型專用的 js  --}}

@section('scripts')
	<script src="{{ URL::to('src/master/js/app.js') }}"></script>
	<script src="{{ URL::to('src/master/js/subIssueVote.js') }}"></script>
@endsection