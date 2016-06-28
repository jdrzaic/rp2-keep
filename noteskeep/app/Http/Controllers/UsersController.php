<?php

namespace App\Http\Controllers;

use App\UsersService;
use Illuminate\Http\Request;

use App\Http\Requests;

class UsersController extends Controller
{
    public function search(Request $request) {
        if($request->has('q') == false) {
            return array();
        }
        $usersService = new UsersService();
        $users = $usersService->getUsersByQuery($request->input('q'));
        $usersInfo = array();
        foreach ($users as $user) {
            $usersInfo[] = array(
                "email" => $user->email,
                "name" => $user->name
            );
        }
        return json_encode(array('users' => array_values($usersInfo)));
    }
}
