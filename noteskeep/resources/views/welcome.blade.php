@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row extra-bot-margin">
        <div class="col-md-10 col-md-offset-1">
            <div class="input-group">
                <input type="textfield" class="panel-body search-box" placeholder="Search...">
                <span class="input-group-btn">
                    <div class="btn" id="new-note-btn">+</div>
                </span>
            </div>
        </div>
    </div>
</div>

<div class="container" id="notes-container">
</div>
@endsection
