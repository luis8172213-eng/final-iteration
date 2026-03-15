<?php

namespace App\Http\Controllers;

use App\Models\SavedCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CredentialController extends Controller
{
    /**
     * Display a listing of the user's credentials.
     */
    public function index()
    {
        $credentials = Auth::user()->savedCredentials()->latest()->get();
        return view('credentials.index', compact('credentials'));
    }

    /**
     * Show the form for creating a new credential.
     */
    public function create()
    {
        return view('credentials.create');
    }

    /**
     * Store a newly created credential in storage.
     * All fields are automatically AES encrypted by the model.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_url' => 'nullable|string|max:500',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:500',
            'notes' => 'nullable|string|max:2000',
        ]);

        $validated['user_id'] = Auth::id();

        SavedCredential::create($validated);

        return redirect()->route('credentials.index')
            ->with('success', 'Credential saved successfully with AES encryption.');
    }

    /**
     * Display the specified credential.
     */
    public function show(SavedCredential $credential)
    {
        // Ensure user owns this credential
        if ($credential->user_id !== Auth::id()) {
            abort(403);
        }

        return view('credentials.show', compact('credential'));
    }

    /**
     * Show the form for editing the specified credential.
     */
    public function edit(SavedCredential $credential)
    {
        // Ensure user owns this credential
        if ($credential->user_id !== Auth::id()) {
            abort(403);
        }

        return view('credentials.edit', compact('credential'));
    }

    /**
     * Update the specified credential in storage.
     */
    public function update(Request $request, SavedCredential $credential)
    {
        // Ensure user owns this credential
        if ($credential->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_url' => 'nullable|string|max:500',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:500',
            'notes' => 'nullable|string|max:2000',
        ]);

        $credential->update($validated);

        return redirect()->route('credentials.index')
            ->with('success', 'Credential updated successfully.');
    }

    /**
     * Remove the specified credential from storage.
     */
    public function destroy(SavedCredential $credential)
    {
        // Ensure user owns this credential
        if ($credential->user_id !== Auth::id()) {
            abort(403);
        }

        $credential->delete();

        return redirect()->route('credentials.index')
            ->with('success', 'Credential deleted successfully.');
    }

    /**
     * Get the decrypted password for a credential.
     * Used for AJAX copy-to-clipboard functionality.
     */
    public function getPassword(SavedCredential $credential)
    {
        // Ensure user owns this credential
        if ($credential->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'password' => $credential->password
        ]);
    }
}
