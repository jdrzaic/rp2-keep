@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    You are logged in!
                </div>

            </div>
        </div>
    </div>
</div>
<!-- test, to be removed -->
<form class="form-horizontal" role="form" method="POST" action="{{ url('/note/14/delete') }}">
        First name:<br>
        <input type="text" name="email" value="Mickey"><br>
        Last name:<br>
        <input type="text" name="tags" value="Mouse"><br><br>
    <button type="submit" class="btn btn-primary">
        <i class="fa fa-btn fa-sign-in"></i> editiraj
    </button>
</form>
@endsection
