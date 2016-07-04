<?php

namespace App\Http\Controllers;

use App\UsersService;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    public function search(Request $request) {
        $usersService = new UsersService();
        if($request->has('q') == false) {
            $users = $usersService->getUsersByQuery("");
        } else {
            $users = $usersService->getUsersByQuery($request->input('q'));
        }
        $usersInfo = array();
        foreach ($users as $user) {
            $usersInfo[] = array(
                "email" => $user->email,
                "name" => $user->name
            );
        }
        return json_encode(array('users' => array_values($usersInfo)));
    }

    public function index() {
        $user = Auth::user();
        return json_encode(array("user" => $user));
    }
}
