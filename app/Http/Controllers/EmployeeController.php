<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $search = trim((string) $request->query('search', ''));

        $employees = User::query()
            ->where('is_admin', false)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->withCount('dailyReports')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('employees.index', [
            'employees' => $employees,
            'search' => $search,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => ($validated['password'] ?? '') ?: $validated['email'],
            'is_admin' => false,
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee account created. Default password is their email unless you entered a password.');
    }

    public function destroy(Request $request, User $employee)
    {
        $this->authorizeAdmin($request);

        abort_if($employee->isAdmin(), 403);
        abort_if($employee->is($request->user()), 403);

        $employee->delete();

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee account deleted.');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->isAdmin(), 403);
    }
}
