<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MemberAccount;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        $account = MemberAccount::where('provider', 'google')
            ->where('account_identifier', $googleUser->email)
            ->where('status', 'active')
            ->first();

        if (! $account || ! $account->member || $account->member->status !== 'active') {
            abort(403, 'No active linked member account.');
        }

        $user = $account->user;

        if (! $user) {
            $user = User::create([
                'name' => $account->member->display_name,
                'email' => $googleUser->email,
                'password' => bcrypt(str()->random(32)),
            ]);

            $account->update([
                'user_id' => $user->id,
                'verified_at' => now(),
            ]);
        }

        Auth::login($user);

        return redirect('/members');
    }
}
