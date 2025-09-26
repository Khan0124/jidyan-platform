<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    public function update(): RedirectResponse
    {
        $validated = request()->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (! Hash::check($validated['current_password'], request()->user()->password)) {
            return back()->withErrors([
                'current_password' => [trans('auth.password')],
            ]);
        }

        request()->user()->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return back()->with('status', __('Password updated successfully.'));
    }
}
