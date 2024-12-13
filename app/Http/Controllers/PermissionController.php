<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function getUsersWithPermissions(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 100);
        $searchName = $request->get('name', '');
        $offset = ($page - 1) * $perPage;

        // Fetch users with their permissions
        $users = User::with('permissions:id,name')
            ->when($searchName, function ($query) use ($searchName) {
                $query->where('name', 'like', "%$searchName%");
            })
            ->offset($offset)
            ->limit($perPage)
            ->get();

        // Decrypt the email addresses
        $users->transform(function ($user) {
            $user->email = Crypt::decryptString($user->email);
            return $user;
        });

        $total = User::count();

        return response()->json(['users' => $users, 'total' => $total]);
    }

    public function getPermissions(Request $request)
    {
        $search = $request->get('search', '');

        $permissions = Permission::when($search, function ($query) use ($search) {
            $query->where('name', 'like', "%$search%");
        })->get(['id', 'name']);

        return response()->json($permissions);
    }

    public function assignPermission(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        DB::table('user_permissions')->updateOrInsert([
            'user_id' => $request->user_id,
            'permission_id' => $request->permission_id,
        ]);

        return response()->json(['message' => 'Permission assigned successfully.']);
    }

    public function revokePermission(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        DB::table('user_permissions')
            ->where('user_id', $request->user_id)
            ->where('permission_id', $request->permission_id)
            ->delete();

        return response()->json(['message' => 'Permission revoked successfully.']);
    }
}
