<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (in_array($user->role, ['owner', 'admin'])) {
            $users = User::with('branch')->orderBy('branch_id')->get();
            $branches = Branch::orderBy('id')->get();
        } else {
            $users = User::with('branch')->where('branch_id', $user->branch_id)->orderBy('branch_id')->get();
            $branches = Branch::where('id', $user->branch_id)->get();
        }
        $grouped = $users->groupBy(fn($u) => $u->branch?->branch_name ?? $u->getRawOriginal('branch') ?? 'Unassigned');
        $isOwner = in_array($user->role, ['owner', 'admin']);

        return view('users.index', compact('grouped', 'branches', 'isOwner'));
    }

    public function create()
    {
        $user = auth()->user();
        if (in_array($user->role, ['admin', 'owner'])) {
            $branches = Branch::all();
        } else {
            $branches = Branch::where('id', $user->branch_id)->get();
        }
        return view('users.create', compact('branches'));
    }

    public function store(Request $request)
    {
        if (User::count() >= 4) {
            return back()->withErrors(['error' => 'Registration is closed. Maximum user limit (4) reached.']);
        }

        $user = auth()->user();
        $allowedRoles = in_array($user->role, ['admin', 'owner']) ? 'owner,staff,admin,manager,cashier' : 'staff,manager,cashier';
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => "required|in:$allowedRoles",
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $branchId = in_array($user->role, ['admin', 'owner']) ? $request->branch_id : $user->branch_id;
        $branchName = null;
        if ($branchId) {
            $branchObj = Branch::find($branchId);
            $branchName = $branchObj ? $branchObj->branch_name : null;
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'branch_id' => $branchId,
            'branch' => $branchName,
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $authUser = auth()->user();
        
        if (!in_array($authUser->role, ['admin', 'owner']) && $authUser->branch_id !== $user->branch_id) {
            abort(403);
        }

        if (in_array($authUser->role, ['admin', 'owner'])) {
            $branches = Branch::all();
        } else {
            $branches = Branch::where('id', $authUser->branch_id)->get();
        }
        
        return view('users.edit', compact('user', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        $authUser = auth()->user();

        if (!in_array($authUser->role, ['admin', 'owner']) && $authUser->branch_id !== $user->branch_id) {
            abort(403);
        }

        $allowedRoles = in_array($authUser->role, ['admin', 'owner']) ? 'owner,staff,admin,manager,cashier' : 'staff,manager,cashier';

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => "required|in:$allowedRoles",
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $data = $request->only(['name', 'email', 'role']);
        
        $branchId = in_array($authUser->role, ['admin', 'owner']) ? $request->branch_id : $authUser->branch_id;
        $data['branch_id'] = $branchId;
        
        if ($branchId) {
            $branchObj = Branch::find($branchId);
            $data['branch'] = $branchObj ? $branchObj->branch_name : null;
        } else {
            $data['branch'] = null;
        }

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete yourself.']);
        }
        
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function revealPassword(Request $request, User $user)
    {
        $authUser = auth()->user();
        if ($authUser->role !== 'owner') {
            abort(403);
        }

        return response()->json([
            'password' => '(Passwords are stored encrypted and cannot be revealed for security reasons.)',
        ]);
    }

    public function resetPassword(Request $request, User $user)
    {
        $authUser = auth()->user();
        if (!in_array($authUser->role, ['owner', 'admin'])) {
            abort(403);
        }

        $request->validate([
            'new_password' => 'required|string|min:8',
        ]);

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json(['success' => true, 'message' => 'Password updated successfully.']);
    }

    public function toggleBranch(Request $request, Branch $branch)
    {
        $authUser = auth()->user();
        if (!in_array($authUser->role, ['owner'])) {
            abort(403);
        }

        $branch->update(['is_active' => !$branch->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $branch->is_active,
            'message' => $branch->is_active ? 'Branch enabled.' : 'Branch disabled. Active sessions will be logged out.',
        ]);
    }

    public function updateUsername(Request $request, User $user)
    {
        $authUser = auth()->user();
        if (!in_array($authUser->role, ['owner', 'admin'])) {
            abort(403);
        }

        $request->validate([
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update(['email' => $request->email]);

        return response()->json([
            'success' => true,
            'message' => 'Username updated successfully.',
            'new_email' => $user->email,
        ]);
    }
}
