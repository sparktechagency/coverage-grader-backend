<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Cashier\Billable;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [
        'id'
    ];
    protected $appends = ['full_name'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp',
        'verification_token',
        'fcm_token',
        'otp_expires_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class);
    }

    // Activity Log Configuration, it's also customizable
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email']) //customize the fields you want to log
            ->logOnlyDirty() //log only the changed fields
            ->setDescriptionForEvent(fn(string $eventName) => "User has been {$eventName}")
            ->useLogName('user_activity');
    }
///full  name
    public function getFullNameAttribute(): string
    {
        if($this->last_name === 'null' || $this->last_name === null){
            return $this->first_name;
        }
        $fullName = $this->first_name . ' ' . $this->last_name;

        return $fullName;
    }

    //get avatar
    public function getAvatarAttribute($value)
    {
        $fullName = $this->first_name;

        if (!empty($this->last_name)) {
            $fullName .= '+' . $this->last_name;
        }

        $encodedName = urlencode($fullName);

        return $value
            ?  Storage::disk('public')->url($value)
            : "https://ui-avatars.com/api/?background=random&name={$encodedName}&bold=true";
    }

}
