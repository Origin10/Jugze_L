@extends('layouts.master')

@section('title')
	@if(isset($subIssue))
		{{ $subIssue->title }}
	@else
		{{ config('app.name') }}
	@endif
@endsection

@section('styles')
	<link rel="stylesheet" href="{{ URL::to('src/master/css/app.css') }}">
	<meta property="og:title" content="{{ $subIssue->title }}"/>
	<meta property="og:type" content="website" />
	{{--  <meta property="og:image" content="{{ URL::to('pie/issue/'.$issue->id.'.png') }}"/>
	<meta property="og:image:secure_url" content="{{ URL::to('pie/issue/'.$issue->id.'.png') }}" />
	<meta property="og:image:type" content="image/jpeg" />
	<meta property="og:image:width" content="300" />
	<meta property="og:image:height" content="300" />
	<meta property="og:image:alt" content="Current issue pie chart" />  --}}
	<meta property="og:site_name" content="{{ config('app.name') }}"/>
	<meta property="og:description" content="{{ $subIssue->description }}" >
@endsection

@section('content')
<div class="full">
	@if(isset($subIssue))
		<?php
			/**
			* 議題詳細內容初始化設定
			*/
			$mPosPercent = round(($masterIssueTotalNum['pos'] /
				($masterIssueTotalNum['pos'] + $masterIssueTotalNum['neg'])) * 100);
			$mNegPercent = round(($masterIssueTotalNum['neg'] /
				($masterIssueTotalNum['pos'] + $masterIssueTotalNum['neg'])) * 100);

			$totalNum = (($posNum + $negNum) > 0) ? ($posNum + $negNum) : 1;
			$posPercent = round(($posNum / $totalNum) * 100);
			$negPercent = round(($negNum / $totalNum) * 100);
		?>
		<div class="card text-center" class="col-sm-7">
			<div class="card-header" >
				<div class="card-title">
					<div class="row">
						<div class="col-12 col-md-6">
							<h3>{{ $masterIssue->title }}</h3>
						</div>
						<div class="col-12 col-md-2">
							<h5>正方:{{ $masterIssueTotalNum['pos'] }}人</h5>
						</div>
						<div class="col-12 col-md-2">
							<h5>反方:{{ $masterIssueTotalNum['neg'] }}人</h5>
						</div>
						<div class="col-12 col-md-2">
							<a href="{{ route('issue.detail', ['id' => $masterIssue->id]) }}" class="btn btn-success">
								返回母議題
							</a>
						</div>
						<div class="col-12 col-md-12">
							<br>
							<div class="progress">
								<div class="progress-bar progress-bar-striped" role="progressbar" style="width:{{ $mPosPercent }}%"
									aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">{{ $mPosPercent }}%</div>
								<div class="progress-bar progress-bar-striped bg-danger" role="progressbar" style="width:{{ $mNegPercent }}%"
									aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">{{ $mNegPercent }}%</div>
							</div>
						</div>
					</div>
					<br>
					<div class="text-left">
						<h3>
							延伸： <issueTitle id="issueTitle">{{ $subIssue->title }}</issueTitle>
							@if ($userInSubIssue && $hasJudges)
								<button class="btn btn-warning" type="button" data-toggle="modal" data-target="#judgeModal" style="float:right;">
									審核系統
								</button>
							@endif
						</h3>
					</div>
				</div>
			</div>
			<div class="card-body">
				<div class="card-text float-left">
					<?php echo htmlspecialchars_decode($subIssue->description); ?>
				</div>
			</div>


			<div class="card-footer">
				<div class="col-12 col-md-6">
					議題發表於
					{{ \App\Constant::getTimeFromInt($subIssue->deadline) }}
				</div>
			</div>
		</div>
		<div>
			<br>
			<center>
				@if (!$userInIssue)
					<a href="{{ route('issue.detail', ['id' => $subIssue->issue_id]) }}" class="btn btn-warning">
						請先參與母議題
					</a>
				@else
					<a href="{{ route('issue.joinExtend', ['id' => $subIssue->id, 'status' => \App\Constant::USER_ISSUE_STATUS_POS]) }}" class="btn btn-primary">
						@if ($userInSubIssue)
							@if($user->status == \App\Constant::USER_ISSUE_STATUS_POS)
								已選擇：
							@else
								變更為
							@endif
						@else
							我要成為
						@endif
						正方
						<span class="badge">
							{{ $posNum }}
						</span>
					</a>
					<a href="{{ route('issue.joinExtend', ['id' => $subIssue->id, 'status' => \App\Constant::USER_ISSUE_STATUS_NEG]) }}" class="btn btn-danger">
						@if ($userInSubIssue)
							@if($user->status == \App\Constant::USER_ISSUE_STATUS_NEG)
								已選擇：
							@else
								變更為
							@endif
						@else
							我要成為
						@endif
						反方
						<span class="badge">
							{{ $negNum }}
						</span>
					</a>
				@endif
				<br><br>
				<div class="progress" style="width:30%;float:center;" >
					<div class="progress-bar progress-bar-striped" role="progressbar" style="width: {{ $posPercent }}%"
					aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">{{ $posPercent }}%</div>
					<div class="progress-bar progress-bar-striped bg-danger" role="progressbar" style="width: {{ $negPercent }}%"
					aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">{{ $negPercent }}%</div>
				</div>
				<br>
			</center>
			<center>
				@if ($userInSubIssue)
					<div class="col-lg-8">
						<div class="input-group mb-3">
								<div class="input-group-prepend">
									<span class="input-group-text">
										<img src="{{ URL::to('src/nicknames/'.$user->nickname->img.'.png') }}"
											alt="{{ $user->nickname->name }}"
											title="{{ $user->color }}的{{ $user->nickname->name }}"
											style="width: 25px; height: 25px; background: {{ $user->color }};">
									</span>
									<span class="input-group-text">{{ $user->seq }}號辯論士</span>
								</div>
							<textarea id="inputComment"class="form-control" placeholder="輸入您的回應" aria-label="輸入您的回應" rows="1"></textarea>
							<span class="input-group-btn">
								<button class="btn btn-secondary" type="button" onclick="sendComment()">留言</button>
							</span>
						</div>
					</div>
					<br>
				@endif
				<div id="subIssueCommentDiv">
					@if (count($messages) > 0)
						<?php
							/**
							* 訊息過多時需要先隱藏
							*/
							$hideMessageGap = 5;//先設定 1 條以上就隱藏，方便測試
							$messageNum = 0;
							$isHide = false;
							$currentCommentStatus = -1;
							$judgesList = '';
						?>
						{{--
							這邊基本上完全不一樣了，由於程式碼有點分散所以我大致說一下：
							foreach裡面顯示所有的留言，前兩個if判斷是用來判斷留言的立場，來分別把空白放在前面或後面；
							第三、四、五個if用來判斷是否超過五句留言，超過時要增加顯示"顯示更多"的區域；
							最後面的else用來顯示沒有留言時要講的話
						--}}
						@if ($bestCommentPos['num'] > 0 || $bestCommentNeg['num'] > 0)
							<center><font size="5">最佳論述</font></center>
							<ul class="list-group">
								<div id="bestComment" class="row">
									<div class="col-6">
										@if ($bestCommentPos['num'] > 0)
											<li class="list-group-item"
												style="margin: 5px; border-left:5px #0000FF solid;
													word-break: break-all; word-wrap: break-word;
													text-align:center">
												<img class="float-md-left commentImg"
													src="{{ URL::to('src/nicknames/'.$bestCommentPos['message']->user->nickname->img.'.png') }}"
													alt="{{ $bestCommentPos['message']->user->nickname->name }}"
													title="{{ $bestCommentPos['message']->user->color }}的{{ $bestCommentPos['message']->user->nickname->name }}"
													style="width: 25px; height: 25px; background: {{ $bestCommentPos['message']->user->color }};"
													onclick="go2Profile({{ $bestCommentPos['message']->user->user_id }})">
												<strong class="float-md-left"> {{ $bestCommentPos['message']->user->seq }}號辯論士</strong>
												<br>
												<div>
													<?php echo nl2br($bestCommentPos['message']->comment); ?>
												</div>
											</li>
										@endif
									</div>
									<div class="col-6">
										@if ($bestCommentNeg['num'] > 0)
											<li class="list-group-item"
												style="margin: 5px; border-right:5px #FF0000 solid;
													word-break: break-all; word-wrap: break-word;
													text-align:center">
												<img class="float-md-right commentImg"
													src="{{ URL::to('src/nicknames/'.$bestCommentNeg['message']->user->nickname->img.'.png') }}"
													alt="{{ $bestCommentNeg['message']->user->nickname->name }}"
													title="{{ $bestCommentNeg['message']->user->color }}的{{ $bestCommentNeg['message']->user->nickname->name }}"
													style="width: 25px; height: 25px; background: {{ $bestCommentNeg['message']->user->color }};"
													onclick="go2Profile({{ $bestCommentNeg['message']->user->user_id }})">
												<strong class="float-md-right"> {{ $bestCommentNeg['message']->user->seq }}號辯論士</strong>
												<br>
												<div>
													<?php echo nl2br($bestCommentNeg['message']->comment); ?>
												</div>
											</li>
										@endif
									</div>
								</div>
							</ul>
							<hr>
						@endif
						<ul class="list-group">
							<div id="subIssueComment" class="row">
								@foreach ($messages as $created_at => $message)
									<?php
										$messageNum++;
										$messageId = $created_at;
										$created_at = explode('_', $created_at)[0];
										$thumbUpsPos = 0;
										$thumbUpsNeg = 0;
										$thumbUpAlready = false;
										if(isset($message->thumb_up_pos)){
											$ups = json_decode($message->thumb_up_pos);
											$thumbUpsPos = count((array)$ups);
											if($userInSubIssue){
												$uId = Auth::user()->id;
												if(isset($ups->$uId)) $thumbUpAlready = true;
											}
										}
										if(isset($message->thumb_up_neg)){
											$ups = json_decode($message->thumb_up_neg);
											$thumbUpsNeg = count((array)$ups);
											if($userInSubIssue){
												$uId = Auth::user()->id;
												if(isset($ups->$uId)) $thumbUpAlready = true;
											}
										}

										//檢查留言是否被檢舉
										if($userInSubIssue && $hasJudges && isset($wait4Judges->$messageId)){
											$judgesList .= '<li id="judgeModalListItem-'.$messageId.'" class="judgeModalListItem list-group-item">'.
												nl2br($message->comment).
												'<button type="button" class="btn btn-primary btn-sm" style="float: right;" '.
												'onclick="judgeCommentChoose('.$subIssue->id.', \''.$messageId.'\', '.Auth::user()->id.', \'delete\')">刪除</button>'.
												'<button type="button" class="btn btn-secondary btn-sm" style="float: right;" '.
												'onclick="judgeCommentChoose('.$subIssue->id.', \''.$messageId.'\', '.Auth::user()->id.', \'keep\')">保留</button>'.
												'</li><br>';
										}
									?>
									@if(!$isHide && $messageNum > $hideMessageGap)
										<?php $isHide = true; ?>
										</div>
										<div class="collapse" id="collapseComment">
											<div id="subIssueCollapseComment" class="row">
									@endif
									@if ($currentCommentStatus != $message->status)
										<div class="w-100"><br></div>
										<div class="w-100"><br></div>
									@endif
									@if ($message->status == \App\Constant::USER_ISSUE_STATUS_POS)
										<div class="col-10" style="text-align:left">
											<span id="{{ $messageNum }}F"
												class="badge badge-pill badge-primary commentFloor"
												onclick="addTag({{ $messageNum }})"
												uId="{{ $message->user->user_id }}">
												{{ $messageNum }}F
											</span>
											@if(!isset($message->delete) && $userInSubIssue && $message->user->user_id == Auth::user()->id)
												<a id="commentEdit_{{$messageNum}}" class="badge badge-pill" data-toggle="modal" data-target="#modalCommentEdit" href="#"
													data-subIssue="{{ $subIssue->id }}" data-seq="{{ $messageNum }}"
													data-id="{{ $messageId }}" data-content="{{ $message->comment }}">
													<h8>編輯</h8>
												</a>
											@endif
											<li id="comment_{{ $message->status }}_{{ $messageNum }}"
												class="list-group-item"
												style="margin: 5px; border-left:5px #0000FF solid;
													word-break: break-all; word-wrap: break-word;
													text-align:center">
												<img class="float-md-left commentImg"
													src="{{ URL::to('src/nicknames/'.$message->user->nickname->img.'.png') }}"
													alt="{{ $message->user->nickname->name }}"
													title="{{ $message->user->color }}的{{ $message->user->nickname->name }}"
													style="width: 25px; height: 25px; background: {{ $message->user->color }};"
													onclick="go2Profile({{ $message->user->user_id }})">
												<strong class="float-md-left"> {{ $message->user->seq }}號辯論士</strong>
												<br>
												<div id="comment_{{$messageNum}}">
													@if (!isset($message->delete))
														<?php echo nl2br($message->comment); ?>
													@else
														<strong>Jugze： 留言遭到刪除</strong>
													@endif
												</div>
											</li>
											<font class="float-md-left" size="1">
												{{ \App\Constant::getPassedTimeFromInt($created_at) }}
												@if (!isset($message->delete) && $userInSubIssue)
													<a class="badge badge-pill" data-toggle="modal" data-target="#modalCommentJudge" href="#"
														data-subIssue="{{ $subIssue->id }}" data-id="{{ $messageId }}" data-content="{{ $message->comment }}">
														檢舉
													</a>
												@endif
											</font>
											@if (!isset($message->delete))
												<button id="commentThumb_{{$messageNum}}" type="button"
													class="btn btn-sm float-md-right {{ ($thumbUpAlready) ? 'btn-primary' : 'btn-light'}}"
													aria-hidden="true"
													@if ($userInSubIssue)
														onclick="thumbChange({{$messageNum}}, {{$subIssue->id}}, '{{$messageId}}',
															{{Auth::user()->id}}, '{{ ($thumbUpAlready) ? 'down' : 'up' }}')"
													@endif
													>
													<i class="fa fa-thumbs-up"></i>
													@if ($thumbUpsPos > 0)
														<span class="badge badge-primary">{{$thumbUpsPos}}</span>
													@endif
													@if ($thumbUpsNeg > 0)
														<span class="badge badge-danger">{{$thumbUpsNeg}}</span>
													@endif
												</button>
											@endif
										</div>
										<div class="col-2"></div>
									@else
										<div class="col-2"></div>
										<div class="col-10" style="text-align:right">
											@if(!isset($message->delete) && $userInSubIssue && $message->user->user_id == Auth::user()->id)
												<a id="commentEdit_{{$messageNum}}" class="badge badge-pill" data-toggle="modal" data-target="#modalCommentEdit" href="#"
													data-subIssue="{{ $subIssue->id }}" data-seq="{{ $messageNum }}"
													data-id="{{ $messageId }}" data-content="{{ $message->comment }}">
													<h8>編輯</h8>
												</a>
											@endif
											<span id="{{ $messageNum }}F"
												class="badge badge-pill badge-danger commentFloor"
												onclick="addTag({{ $messageNum }})"
												uId="{{ $message->user->user_id }}">
												{{ $messageNum }}F
											</span>
											<li id="comment_{{ $message->status }}_{{ $messageNum }}"
												class="list-group-item"
												style="margin: 5px; border-right:5px #FF0000 solid;
													word-break: break-all; word-wrap: break-word;
													text-align:center">

												<img class="float-md-right commentImg"
													src="{{ URL::to('src/nicknames/'.$message->user->nickname->img.'.png') }}"
													alt="{{ $message->user->nickname->name }}"
													title="{{ $message->user->color }}的{{ $message->user->nickname->name }}"
													style="width: 25px; height: 25px; background: {{ $message->user->color }};"
													onclick="go2Profile({{ $message->user->user_id }})">

												<strong class="float-md-right"> {{ $message->user->seq }}號辯論士</strong>
												<br>
												<div id="comment_{{$messageNum}}">
													@if (!isset($message->delete))
														<?php echo nl2br($message->comment); ?>
													@else
														<strong>Jugze： 留言遭到刪除</strong>
													@endif
												</div>
											</li>
											@if (!isset($message->delete))
												<button id="commentThumb_{{$messageNum}}" type="button"
													class="btn btn-sm float-md-left {{ ($thumbUpAlready) ? 'btn-primary' : 'btn-light'}}"
													aria-hidden="true"
													@if ($userInSubIssue)
														onclick="thumbChange({{$messageNum}}, {{$subIssue->id}}, '{{$messageId}}',
															{{Auth::user()->id}}, '{{ ($thumbUpAlready) ? 'down' : 'up' }}')"
													@endif
													>
													<i class="fa fa-thumbs-up"></i>
													@if ($thumbUpsPos > 0)
														<span class="badge badge-primary">{{$thumbUpsPos}}</span>
													@endif
													@if ($thumbUpsNeg > 0)
														<span class="badge badge-danger">{{$thumbUpsNeg}}</span>
													@endif
												</button>
											@endif
											<font class="float-md-right" size="1">
												{{ \App\Constant::getPassedTimeFromInt($created_at) }}
												@if (!isset($message->delete) && $userInSubIssue)
													<a class="badge badge-pill" data-toggle="modal" data-target="#modalCommentJudge" href="#"
														data-subIssue="{{ $subIssue->id }}" data-id="{{ $messageId }}" data-content="{{ $message->comment }}">
														檢舉
													</a>
												@endif
											</font>
										</div>
									@endif
									<div class="w-100"></div>
									<?php
										$currentCommentStatus = $message->status;
									?>
								@endforeach
							</div>
								@if ($isHide)
									</div>
								@endif
						</ul>
						@if ($isHide)
							<p>
								<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseComment" aria-expanded="false" aria-controls="collapseComment">顯示更多留言...</button>
							</p>
						@endif
					@else
						<div id="SubIssueCommentNoOne">成為第一個留言的人吧！</div>
					@endif
				</div>
			</center>
			<br>
		</div>
		<!-- Modal -->
		<div class="modal fade" id="modalCommentEdit" tabindex="-1" role="dialog" aria-labelledby="modalCommentEditTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="btn btn-sm btn-danger delete">刪除</button>
						<h5 class="modal-title" id="modalCommentEditTitle"> 編輯留言</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="card card-body text-center">
							<textarea class="editComment" rows="4"></textarea>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
						<button type="button" class="btn btn-primary confirm">確定</button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="modalCommentJudge" tabindex="-1" role="dialog" aria-labelledby="modalCommentJudgeTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="modalCommentJudgeTitle">確認要檢舉此篇留言？</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="card card-body text-center"></div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
						<button type="button" class="btn btn-primary confirm">確定</button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="judgeModal" tabindex="-1" role="dialog" aria-labelledby="judgeModalTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="judgeModalTitle">審核視窗</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<ul id="judgeModalList" class="list-group list-group-flush">
							@if($userInSubIssue && $hasJudges)
							<?php echo $judgesList; ?>
							@endif
						</ul>
					</div>
				</div>
			</div>
		</div>
	@endif
</div>
<form role="form" action="{{ route('user.profile') }}" method="post" class="login-form">
	<input type="text" id="go2ProfileTarget" name="targetId" value="" style="display:none;">
	<input type="text" id="go2ProfileIssue" name="id" value="{{$subIssue->issue_id}}" style="display:none;">
	<button type="submit" id="go2ProfileSubmit" style="display:none;"></button>
	{{ csrf_field() }}
</form>
@endsection

{{--  載入 master 版型專用的 js  --}}
@section('scripts')
	<link rel="stylesheet" href="{{ URL::to('src/master/css/comment.css') }}">
	<script src="{{ URL::to('src/master/js/app.js') }}"></script>
	<script src="{{ URL::to('src/extend/comment.js') }}"></script>
	<script src="{{ URL::to('src/extend/commentAction.js') }}"></script>
	<script src="{{ URL::to('src/extend/thumb.js') }}"></script>
@endsection