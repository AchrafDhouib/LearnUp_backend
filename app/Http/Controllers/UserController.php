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

            $query = User::with(['groups', 'userAnswers.passedExam', 'passedExams', 'roles']);

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
                    'role' => $user->getRoleNames()->first(), // Single role name
                    'groups' => $user->groups,
                    'passed_exams' => $user->passedExams,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
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
        try {
            $user = User::with([
                'groups', 
                'passedExams.exam', 
                'passedExams.certification',
                'roles'
            ])->findOrFail($id);

            // For teachers, get groups they created and courses they created
            if ($user->hasRole('teacher')) {
                $user->load(['createdGroups.course', 'courses']);
            }

            // For students, get groups they joined and exams they passed
            if ($user->hasRole('student')) {
                $user->load(['groups', 'passedExams.exam', 'passedExams.certification']);
            }

            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'avatar' => $user->avatar,
                'email' => $user->email,
                'is_active' => $user->is_active,
                'role' => $user->getRoleNames()->first(),
                'groups' => $user->groups,
                'created_groups' => $user->createdGroups ?? [],
                'passed_exams' => $user->passedExams,
                'courses' => $user->courses ?? [],
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = User::findOrFail($id);
            
            $validatedData = $request->validate([
                'first_name' => ['sometimes', 'string', 'max:255'],
                'last_name' => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
                'name' => ['sometimes', 'string', 'max:255', 'unique:users,name,' . $id],
            ]);

            $user->update($validatedData);

            return response()->json([
                'message' => 'User updated successfully!',
                'user' => $user,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request, string $id)
    {
        try {
            $user = User::findOrFail($id);
            
            $validatedData = $request->validate([
                'current_password' => ['required', 'string'],
                'new_password' => ['required', 'string', 'min:8', 'confirmed'],
                'new_password_confirmation' => ['required', 'string'],
            ]);

            // Check if current password is correct
            if (!Hash::check($validatedData['current_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect',
                ], 422);
            }

            // Update password
            $user->update([
                'password' => Hash::make($validatedData['new_password']),
            ]);

            return response()->json([
                'message' => 'Password changed successfully!',
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
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
