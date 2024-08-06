<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;



    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'phone_number',
        'birth_date',
        'is_admin',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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

    public function wallet(){
        
        return $this->hasOne(Wallet::class);
    }

    public function lists()
    {
        return $this->hasMany(favoriteList::class);
    }

    public function reviews(){

        return $this->hasMany(Review::class);
    }

    public function getImageAttribute(){

        $parent = Folder::where('name', 'Users')->first();

        $image = Folder::where('name', 'user ' . $this->id)->where('folder_id', $parent->id)->first();
        if($image)
        $image = $image->images;

        return $image;

    }
    
   
}
