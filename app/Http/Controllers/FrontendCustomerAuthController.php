<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class FrontendCustomerAuthController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'customerLogin')
                ->with('customer_auth_modal', 'login');
        }

        $login = trim((string) $request->string('login'));

        $customer = Customer::query()
            ->where(function ($query) use ($login) {
                $query->where('email', $login)
                    ->orWhere('phone', $login);
            })
            ->where('deleted', 0)
            ->first();

        if (! $customer || ! Hash::check((string) $request->string('password'), $customer->password)) {
            return back()
                ->withErrors(['login' => __('These credentials do not match our records.')], 'customerLogin')
                ->with('customer_auth_modal', 'login');
        }

        if ((int) $customer->status !== 1) {
            return back()
                ->withErrors(['login' => __('Your customer account is inactive.')], 'customerLogin')
                ->with('customer_auth_modal', 'login');
        }

        $request->session()->put('customer_id', $customer->id);
        $request->session()->regenerate();

        return back();
    }

    public function register(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'phone' => ['required', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'customerRegister')
                ->withInput()
                ->with('customer_auth_modal', 'register');
        }

        $customer = Customer::create([
            ...$validator->validated(),
            'status' => 1,
        ]);

        $request->session()->put('customer_id', $customer->id);
        $request->session()->regenerate();

        return back();
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('customer_id');

        return back();
    }
}
