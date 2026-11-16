<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffAdminController extends Controller
{
    public function index()
    {
        $admins = DB::table('staff_admin')
            ->orderBy('staff_admin_id', 'asc')
            ->get();

        return view('staff_admins', compact('admins'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => 'required|email|max:150|unique:staff_admin,email',
            'role'       => 'required|string|max:50',
            'department' => 'required|string|max:100',
            'is_active'  => 'required|in:0,1',
            'password'   => 'required|string|min:6',
        ]);

        DB::table('staff_admin')->insert([
            'name'          => $request->name,
            'email'         => $request->email,
            'password_hash' => Hash::make($request->password),
            'role'          => $request->role,
            'department'    => $request->department,
            'is_active'     => (int) $request->is_active,
            'created_at'    => now(),
        ]);

        return back()->with('success', 'New staff admin account created successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => 'required|email|max:150|unique:staff_admin,email,' . $id . ',staff_admin_id',
            'role'       => 'required|string|max:50',
            'department' => 'required|string|max:100',
            'is_active'  => 'required|in:0,1',
            'password'   => 'nullable|string|min:6',
        ]);

        $currentAdminId = session('staff_admin_id');

        if ((int) $id === (int) $currentAdminId && (int) $request->is_active === 0) {
            return back()->withErrors(['error' => 'You cannot deactivate your own currently logged-in account.']);
        }

        if ((int) $request->is_active === 0) {
            $otherActiveCount = DB::table('staff_admin')
                ->where('is_active', 1)
                ->where('staff_admin_id', '!=', $id)
                ->count();

            if ($otherActiveCount === 0) {
                return back()->withErrors(['error' => 'At least one active admin account must remain in the system.']);
            }
        }

        $updateData = [
            'name'       => $request->name,
            'email'      => $request->email,
            'role'       => $request->role,
            'department' => $request->department,
            'is_active'  => (int) $request->is_active,
        ];

        if (!empty($request->password)) {
            $updateData['password_hash'] = Hash::make($request->password);
        }

        DB::table('staff_admin')
            ->where('staff_admin_id', $id)
            ->update($updateData);

        if ((int) $id === (int) $currentAdminId) {
            session(['staff_name' => $request->name]);
        }

        return back()->with('success', 'Staff admin record updated successfully.');
    }

    public function destroy($id)
    {
        if ((int) $id === (int) session('staff_admin_id')) {
            return back()->withErrors(['error' => 'You cannot delete your own currently logged-in account.']);
        }

        $remainingAdmins = DB::table('staff_admin')
            ->where('staff_admin_id', '!=', $id)
            ->count();

        if ($remainingAdmins === 0) {
            return back()->withErrors(['error' => 'Cannot delete the last remaining admin account.']);
        }

        DB::table('staff_admin')->where('staff_admin_id', $id)->delete();

        return back()->with('success', 'Staff admin account deleted.');
    }
}