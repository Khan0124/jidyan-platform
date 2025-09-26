<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    public function store(): RedirectResponse
    {
        $validated = request()->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($validated['password'], request()->user()->password)) {
            throw ValidationException::withMessages([
                'password' => [trans('auth.password')],
            ]);
        }

        request()->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended();
    }
}
