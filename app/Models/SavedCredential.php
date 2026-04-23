<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SavedCredential extends Model
{
    use HasFactory;

    /**
     * Fields I can assign when creating/updating a saved credential.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'site_name',
        'site_url',
        'username',
        'password',
        'notes',
    ];

    /**
     * Don't expose the password when converting to JSON.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Link to the user who saved this credential.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * When saving the site name, I encrypt it for security.
     */
    public function setSiteNameAttribute($value)
    {
        $this->attributes['site_name'] = Crypt::encryptString($value);
    }

    /**
     * When getting the site name, I decrypt it so it's readable.
     */
    public function getSiteNameAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * When saving the site URL, I encrypt it (but allow it to be empty).
     */
    public function setSiteUrlAttribute($value)
    {
        $this->attributes['site_url'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * When getting the site URL, I decrypt it if it exists.
     */
    public function getSiteUrlAttribute($value)
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * When saving the username, I encrypt it so it's secure.
     */
    public function setUsernameAttribute($value)
    {
        $this->attributes['username'] = Crypt::encryptString($value);
    }

    /**
     * When getting the username, I decrypt it.
     */
    public function getUsernameAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * When saving the password, I encrypt it so it stays secure in the database.
     */
    public function setPasswordAttribute($value)
    {
        // Make sure the password is encrypted before saving
        $this->attributes['password'] = Crypt::encryptString($value);
    }

    /**
     * When getting the password, I decrypt it.
     */
    public function getPasswordAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * When saving notes, I encrypt them (but allow them to be empty).
     */
    public function setNotesAttribute($value)
    {
        $this->attributes['notes'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * When getting notes, I decrypt them if they exist.
     */
    public function getNotesAttribute($value)
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }
}
