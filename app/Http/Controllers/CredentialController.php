<?php

namespace App\Http\Controllers;

use App\Models\SavedCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CredentialController extends Controller
{
    /**
     * Show all the user's saved passwords/credentials.
     */
    public function index()
    {
        $credentials = Auth::user()->savedCredentials()->latest()->get();
        return view('credentials.index', compact('credentials'));
    }

    /**
     * Show the form to create a new saved credential.
     */
    public function create()
    {
        return view('credentials.create');
    }

    /**
     * Save the new credential to the database.
     * Everything I save here gets encrypted automatically by the model.
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
     * Show a single credential (only if the user owns it).
     */
    public function show(SavedCredential $credential)
    {
        // Make sure this credential belongs to the logged-in user
        if ($credential->user_id !== Auth::id()) {
            abort(403);
        }

        return view('credentials.show', compact('credential'));
    }

    /**
     * Show the form to edit a credential.
     */
    public function edit(SavedCredential $credential)
    {
        // Make sure this credential belongs to the logged-in user
        if ($credential->user_id !== Auth::id()) {
            abort(403);
        }

        return view('credentials.edit', compact('credential'));
    }

    /**
     * Save changes to an existing credential.
     */
    public function update(Request $request, SavedCredential $credential)
    {
        // Make sure this credential belongs to the logged-in user
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
     * Delete a credential from the database.
     */
    public function destroy(SavedCredential $credential)
    {
        // Make sure this credential belongs to the logged-in user
        if ($credential->user_id !== Auth::id()) {
            abort(403);
        }

        $credential->delete();

        return redirect()->route('credentials.index')
            ->with('success', 'Credential deleted successfully.');
    }

    /**
     * Send back the decrypted password via AJAX (for copy-to-clipboard).
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
