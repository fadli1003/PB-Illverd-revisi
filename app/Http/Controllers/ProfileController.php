<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ProfileController extends Controller
{
    public function edit()
    {
         $user = Auth::user();  
         return view('profile.edit', compact('user'));
    }

    public function ubahProfile(Request $request)
    {
        $user = Auth::user();        
        $user->name = $request->nama;
        $user->email = $request->email;
        $user->no_Hp = $request->noHp;
        $user->alamat = $request->alamat;
        if ($request->has('reset_foto') && $request->reset_foto == '1') {
            if ($user->foto_profile && Storage::exists($user->foto_profile)) {
                Storage::delete($user->foto_profile);
            }
            $user->foto_profile = null;
        }
        if ($request->hasFile('foto_profile')) {
            if ($user->foto && Storage::exists($user->foto)) {
                Storage::delete($user->foto);
            }
        $path = $request->file('foto_profile')->store('profile', 'public');
        $user->foto_profile = $path;
        }
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
