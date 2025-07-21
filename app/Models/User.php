<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

/*
 the User class is like a child of the Authenticatable class, inheriting its authentication-related features.
 Additionally, it promises to implement the functionalities expected by the FilamentUser interface,
 likely for working with the Filament admin panel.
*/

/*
Change the class beginning with this (class User extends Authenticatable implements FilamentUser) so that you can use committed function (canAccessPanel) below.
*/

class User extends Authenticatable implements FilamentUser
// class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];

    protected $guarded = ['role'];

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
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->is_active === 0) {
            // Filament\Filament::flash('error', 'Your account has been disabled, please contact the administration');
            Notification::make()
                ->title(__('Your account has been disabled, please contact the administration'))
                ->icon('heroicon-o-x-circle')
                ->danger()
                ->color('danger')
                // ->duration(1500)
                ->send();
            return false; // User is inactive and cannot access the panel
        } elseif ($this->is_active === 2) { // pending to accept from head of department
            // Filament\Filament::flash('error', 'Your account has been disabled, please contact the administration');
            Notification::make()
                ->title(__('Your account is pending to approve'))
                ->icon('heroicon-o-pause-circle')
                ->info()
                ->color('info')
                // ->duration(1500)
                ->send();
            return false; // User is inactive and cannot access the panel
        }
        return true;
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function headquarter(): BelongsTo
    {
        return $this->belongsTo(Headquarter::class);
    }

    public function position(): BelongsTo
    {
        return $this->BelongsTo(Position::class);
    }

    public function acadimic_rank(): BelongsTo
    {
        return $this->BelongsTo(AcadimicRank::class);
    }

    public function agendas(): HasMany
    {
        return $this->hasMany(Agenda::class);
    }


    // public function roles(): BelongsToMany
    // {
    //     return $this->belongsToMany(Role::class , 'model_has_roles', 'model_id');
    // }
    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(Session::class, 'session_user');
    }

    public function faculty_sessions(): BelongsToMany
    {
        return $this->belongsToMany(FacultySession::class, 'faculty_session_users');
    }
    public function votes()
    {
        return $this->hasMany(UserDecisionVote::class);
    }
    public function faculty_votes()
    {
        return $this->hasMany(FacultyUserDecisionVote::class);
    }
}
