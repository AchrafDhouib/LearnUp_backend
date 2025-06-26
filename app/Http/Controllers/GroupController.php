<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $groups = Group::with('course', 'students', 'creator')->get();

            return response()->json($groups);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $group = new Group([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'cour_id' => $request->input('cour_id'),
                'creator_id' => $request->input('creator_id'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'image' => $request->input('image'),
                'price'  => $request->input('price'),
                'max_students' => $request->input('max_students'),
            ]);
            $group->save();

            return response()->json($group);
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $group = Group::with('course', 'students', 'creator')->findOrFail($id);

            return response()->json($group);
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $group = Group::findOrFail($id);
            $group->update($request->all());

            return response()->json($group);
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

        try {
            $group = Group::findOrFail($id);
            $group->delete();

            return response()->json('Group deleted');
        } catch (\Exception $e) {
            return response()->json("'error' {$e->getMessage()}, {$e->getCode()}");
        }

    }

    public function addUser(Request $request, $groupId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $group = Group::findOrFail($groupId);
            $group->groups()->syncWithoutDetaching([$request->user_id]);

            return response()->json(['message' => 'User added to group successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function leaveGroup(Request $request, $userId)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

        try {
            $user = User::findOrFail($userId);
            $user->groups()->detach($request->group_id);

            return response()->json(['message' => 'User removed from group successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
