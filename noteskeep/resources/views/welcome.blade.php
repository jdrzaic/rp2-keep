@extends('layouts.app')

@section('content')
<div class="panel user-float">
    <div id="user-email-span"></div>
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
