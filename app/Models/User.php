<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Auth;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'avatar',
        'password',
        'is_active',
        'token_verification'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * @var array
     */
    public $rules = [
        'name' => 'required|max:255',
        'email' => 'required|email|max:255|unique:users',
        'password' => 'required|min:6|confirmed',
    ];

    public $loginRules = [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ];

    public function updateRules($id)
    {
        return [
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'avatar' => ['mimes:jpg,jpeg,JPEG,png,gif', 'max:2024'],
            'password' => 'required|min:6|max:30|confirmed',
            'password_confirmation' => 'required'
        ];
    }


    /**
     * Set the user's password.
     *
     * @param  string  $password
     * @return void
     */
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    public function getAvatarAttribute($value)
    {
        if (empty($value)) {
            return config('path.to_avatar_default');
        }

        $pattern = "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i";
        preg_match($pattern, $value, $matches);

        if (!empty($matches)) {
            return $value;
        }

        return config('path.to_avatar') . $value;
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->is_active = config('constants.NOT_ACTIVE');
            $user->token_verification = str_random(20);
        });
    }

    public function isCurrent()
    {
        return $this->id == auth()->id();
    }
}
