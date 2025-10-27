<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */ 

    protected $fillable = [
        'reg_id',
        'name',
        'phone',
        'email',
        'password',
        'profile_image',
        'dob',
        'dob_hijri',
        'age',
        'blood_group',
        'gender',
        'senior_user',
        'junior_user',
        'user_type',
        'role_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
        'otp',
        'otp_expires_at',
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ]; 

    
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function enroles()
    {
        return $this->hasMany(Enrole::class);
    }

    public function studentRegister()
    {
        return $this->hasOne(StudentRegister::class, 'user_id');
    }  

    public function student()
    {
        return $this->hasOne(Student::class, 'user_id');
    } 


    public function userFamily()
    {
        return $this->hasOne(UserFamily::class, 'user_id');
    } 

    public function answerFiles()
    {
        return $this->hasMany(AnswerFile::class, 'user_id');
    }

    public function admissionProgress()
    {
        return $this->hasOne(AdmissionProgressStatus::class, 'user_id');
    }

    public function guardian()
    {
        return $this->hasOne(Guardian::class, 'user_id');
    }

    public function address()
    {
        return $this->hasMany(UserAddress::class, 'user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }  

    public function getPermissionsSlugs()
    {
        return $this->role->permissions->pluck('slug')->toArray();
    } 
 
 

}
