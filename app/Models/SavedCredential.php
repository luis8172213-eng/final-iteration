<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SavedCredential extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
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
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the user that owns the credential.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * AES Encrypt the site name when storing
     */
    public function setSiteNameAttribute($value)
    {
        $this->attributes['site_name'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt the site name when retrieving
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
     * AES Encrypt the site URL when storing
     */
    public function setSiteUrlAttribute($value)
    {
        $this->attributes['site_url'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Decrypt the site URL when retrieving
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
     * AES Encrypt the username when storing
     */
    public function setUsernameAttribute($value)
    {
        $this->attributes['username'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt the username when retrieving
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
     * AES Encrypt the password when storing
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt the password when retrieving
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
     * AES Encrypt the notes when storing
     */
    public function setNotesAttribute($value)
    {
        $this->attributes['notes'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Decrypt the notes when retrieving
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
