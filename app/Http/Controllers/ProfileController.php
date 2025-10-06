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
    /**
     * Display the user's profile form.
     */
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
        // Cek apakah user ingin reset foto
        if ($request->has('reset_foto') && $request->reset_foto == '1') {
            // Hapus foto lama jika ada
            if ($user->foto_profile && Storage::exists($user->foto_profile)) {
                Storage::delete($user->foto_profile);
            }

            $user->foto_profile = null; // atau set ke foto default di database
        }
        if ($request->hasFile('foto_profile')) {
            // Hapus foto lama jika ada
            if ($user->foto && Storage::exists($user->foto)) {
                Storage::delete($user->foto);
            }
        // Simpan foto baru
        $path = $request->file('foto_profile')->store('profile', 'public');
        $user->foto_profile = $path;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Update the user's profile information.
     */
    // public function update(ProfileUpdateRequest $request): RedirectResponse
    // {
    //     $request->user()->fill($request->validated());

    //     if ($request->user()->isDirty('email')) {
    //         $request->user()->email_verified_at = null;
    //     }

    //     $request->user()->save();

    //     return Redirect::route('profile.edit')->with('status', 'profile-updated');
    // }

    /**
     * Delete the user's account.
     */
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
