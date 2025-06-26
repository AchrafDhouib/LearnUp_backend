<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    // public function index(Request $request)
    // {
    //     try {
    //         $role = $request->query('role'); // get ?role=someRole from URL
    
    //         $query = User::with('roles', 'groups', 'userAnswers', 'passedExams');
    
    //         if ($role) {
    //             $query->whereHas('roles', function ($q) use ($role) {
    //                 $q->where('name', $role);
    //             });
    //         }
    
    //         $users = $query->get();
    
    //         return response()->json($users);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

    public function index(Request $request)
    {
        try {
            $role = $request->query('role');

            $query = User::with('groups', 'userAnswers.passedExam'); // ❌ No need to eager load 'roles'

            if ($role) {
                $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('name', $role);
                });
            }

            $users = $query->get();

            // Transform users
            $users = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'avatar' => $user->avatar,
                    'email' => $user->email,
                    'is_active' => $user->is_active,
                    'roles' => $user->getRoleNames()->first(), // ✅ only role names
                    'groups' => $user->groups,         // original groups
                    'grouped_user_answers' => $user->userAnswers->groupBy('passed_exam_id'), // grouped answers
                ];
            });

            return response()->json($users);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => ['required', 'string', 'max:255', 'unique:users,name'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'password' => Hash::make($validatedData['password']),
        ]);

        // Trigger the Registered event
        event(new Registered($user));

        // Assign the role to the user
        if ($request->has('role')) {
            $user->assignRole($request->role);
        }

        return response()->json([
            'message' => 'User Created successfully!',
            'user' => $user,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    public function activate(string $id)
    {
        $user = User::findOrFail($id);
        $user->activate();

        return response()->json([
            'message' => 'User activated successfully!',
            'user' => $user,
        ], 202);
    }

    public function deactivate(string $id)
    {
        $user = User::findOrFail($id);
        $user->deactivate();

        return response()->json([
            'message' => 'User deactivated successfully!',
            'user' => $user,
        ], 202);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function rolesIndex()
    {
        try {
            $roles = Role::all()->pluck('name');

            return response()->json($roles);
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
        }
    }

    public function joinToGroup(Request $request, $userId)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

        try {
            $user = User::findOrFail($userId);
            $user->groups()->syncWithoutDetaching([$request->group_id]);

            return response()->json(['message' => 'User joined the group successfully.'], 200);
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
