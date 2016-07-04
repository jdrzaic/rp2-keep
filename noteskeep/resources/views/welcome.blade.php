@extends('layouts.app')

@section('content')
<script type="text/javascript">
    var currentUser = "{{ Auth::user()->email }}";
</script>
<div class="user-float panel">
    <div id="user-email-span">{{ Auth::user()->email }}</div>
    <a class="btn logout-btn glyphicon glyphicon-log-out" href="{{ url('/logout') }}"></a>
</div>

<div class="download-float panel">
    <div class="btn" id="download-btn">Download</div><br>
    <div class="btn" id="upload-btn">Upload</div>
    <input type="file" id="upload-file" style="display: none">
</div>

<div class="container">
    <div class="row extra-bot-margin">
        <div class="col-md-10 col-md-offset-1">
            <div class="input-group">
                <input type="text" class="panel-body search-box" placeholder="Search...">
                <span class="input-group-btn">
                    <div class="btn" id="new-note-btn">+</div>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="share-panel-blackout">
    <div class="panel share-panel">
        Share this note with:<br>
        <input type="text" id="share-input" placeholder="Email..."><br>
        <div class="btn" id="share-text-btn">Share</div>
        <div class="btn" id="cancel-share-btn">Cancel</div>
    </div>
</div>

<div class="container" id="notes-container">
</div>
@endsection

@section('scripts')
<script type="text/javascript" src="/js/script.js"></script>
<script type="text/javascript" src="/js/notifications.js"></script>
<script type="text/javascript" src="js/noty/packaged/jquery.noty.packaged.min.js"></script>
@endsection
