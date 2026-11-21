<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Actions\Fortify\CreateNewUser;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * 管理者の新規登録
 */
class RegisteredAdminController extends Controller
{
    public function create(): View
    {
        return view('admin.auth.register');
    }

    public function store(Request $request, CreateNewUser $creator): RedirectResponse
    {
        event(new Registered($user = $creator->create($request->all(), UserRole::Admin)));

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }
}
