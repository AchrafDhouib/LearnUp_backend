<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class UserGroupController extends Controller
{
    public function addUserToGroup(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'group_id' => 'required|exists:groups,id',
        ]);

        try {
            DB::table('user_groups')->insert([  
                'user_id' => $request->user_id,
                'group_id' => $request->group_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['message' => 'User added to group.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function removeUserFromGroup(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'group_id' => 'required|exists:groups,id',
        ]);

        try {
            DB::table('user_groups')
                ->where('user_id', $request->user_id)
                ->where('group_id', $request->group_id)
                ->delete();

            return response()->json(['message' => 'User removed from group.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display all user-group associations.
     */
    public function index()
    {
        try {
            $userGroups = DB::table('user_groups')
                ->join('users', 'user_groups.user_id', '=', 'users.id')
                ->join('groups', 'user_groups.group_id', '=', 'groups.id')
                ->select('user_groups.*', 'users.name as user_name', 'groups.description as group_description')
                ->get();

            return response()->json($userGroups);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display all groups of a specific user.
     */
    public function show($userId)
    {
        try {
            $user = User::with('groups')->findOrFail($userId);

            return response()->json([
                'user' => $user->only(['id', 'name', 'email']),
                'groups' => $user->groups,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
