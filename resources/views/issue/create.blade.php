@extends('layouts.master')

@section('title')
    創建議題
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ URL::to('src/master/css/app.css') }}">
    <script src="{{ URL::to('ckeditor/ckeditor.js') }}"></script>
    <script src="{{ URL::to('ckfinder/ckfinder.js') }}"></script>
@endsection

@section('content')
    <div class="full">
        @if (isset($issue))
            <center><h1>編輯議題</h1></center>
        @else
            <center><h1>創建議題</h1></center>
        @endif
        @if(count($errors) > 0)
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif
        @if (isset($issue))
            <form action="{{ route('issue.editIssue') }}" method="post">
        @else
            <form action="{{ route('issue.createIssue') }}" method="post">
        @endif
            <div class="form-group">
                <label for="title" ><font size="5">標題</font></label>
                @if (isset($issue))
                    <input type="text" id="title" name="title" value="{{ $issue->title }}" class="form-control">
                @else
                    <input type="text" id="title" name="title" class="form-control">
                @endif
            </div>
            <div class="form-group">
                <label for="description"><font size="5">內容</font></label>
                <textarea name="description" id="description" rows="10" cols="80">
                    @if (isset($issue))
                        <?php echo $issue->description ?>
                    @else
                        客觀且完整的說明您的議題吧！
                    @endif
                </textarea>
            </div>
            @if (isset($issue))
                <input type="text" name="issueId" value="{{ $issue->id }}" class="form-control" style="display:none">
                @if (!$isExtend)
                    <div class="form-group">
                        <input type="text" name="issueType" value="issue" class="form-control" style="display:none">
                        <label for="checkCycle"><font size="5">議題循環週期（天）</font></label>
                        <input type="number" id="checkCycle" name="checkCycle" value="{{ (int)$issue->check_cycle }}" min="1" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="voteCycle"><font size="5">投票持續時間（天）</font></label>
                        <input type="number" id="voteCycle" name="voteCycle" value="{{ (int)$issue->vote_cycle }}" min="1" class="form-control">
                    </div>
                @else
                    <input type="text" name="issueType" value="extend" class="form-control" style="display:none">
                @endif
                <button type="submit" class="btn btn-primary btn-lg btn-block">確定修改</button>
            @else
                <div class="form-group">
                    <label for="checkCycle"><font size="5">議題循環週期（天）</font></label>
                    <input type="number" id="checkCycle" name="checkCycle" value="1" min="1" class="form-control">
                </div>
                <div class="form-group">
                    <label for="voteCycle"><font size="5">投票持續時間（天）</font></label>
                    <input type="number" id="voteCycle" name="voteCycle" value="1" min="1" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block">發佈</button>
            @endif
            {{ csrf_field() }}
        </form>
    </div>
@endsection

@section('scripts')
    <script src="{{ URL::to('src/master/js/app.js') }}"></script>
    <script>
        // Replace the <textarea id="description"> with a CKEditor
        // instance, using default configuration.
        let description = CKEDITOR.replace('description', {
            filebrowserImageBrowseUrl: "ckfinder/ckfinder.html?Type=Images&id={{ hash('sha256', Auth::user()->id) }}"
        });
        CKFinder.setupCKEditor( description, "{{ URL::asset('ckfinder/ckfinder.js') }}" );
        console.log(CKFinder);
    </script>
    {{--  <script src="{{ URL::asset('nicEdit/nicEdit.js') }}" type="text/javascript"></script>
    <script type="text/javascript">bkLib.onDomLoaded(nicEditors.allTextAreas);</script>  --}}
@endsection