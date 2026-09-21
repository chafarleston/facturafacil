<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemLockController extends Controller
{
    public function toggle(Request $request)
    {
        $owner = config('app.lock_owner_email', 'rcharles84@gmail.com');

        if (Auth::user()?->email !== $owner) {
            abort(403);
        }

        Setting::setSystemLocked(! Setting::isSystemLocked());

        $locked = Setting::isSystemLocked();

        return redirect()->back()->with('success', $locked
            ? 'Sistema bloqueado correctamente'
            : 'Sistema desbloqueado correctamente');
    }
}