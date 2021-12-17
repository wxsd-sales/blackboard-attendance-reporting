<?php

namespace App\Models\Blackboard;

use App\Models\OAuth;
use App\Models\User;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;


/**
 * App\Models\Blackboard\BlackboardUser
 *
 * @property string $id
 * @property string|null $name
 * @property string $email
 * @property Carbon $synced_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read OAuth|null $oauth
 * @property-read User|null $user
 * @method static Builder|BlackboardUser newModelQuery()
 * @method static Builder|BlackboardUser newQuery()
 * @method static Builder|BlackboardUser query()
 * @method static Builder|BlackboardUser whereCreatedAt($value)
 * @method static Builder|BlackboardUser whereEmail($value)
 * @method static Builder|BlackboardUser whereId($value)
 * @method static Builder|BlackboardUser whereName($value)
 * @method static Builder|BlackboardUser whereSyncedAt($value)
 * @method static Builder|BlackboardUser whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read Collection|BlackboardCourse[] $courses
 * @property-read int|null $courses_count
 */
class BlackboardUser extends Model
{
    use HasFactory, Notifiable;

    /**
     * {@inheritdoc}
     */
    public $incrementing = false;

    /**
     * {@inheritdoc}
     */
    protected $keyType = 'string';

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'synced_at'
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = Str::limit($value);
    }

    public function oauth()
    {
        return $this->morphOne(OAuth::class, 'provider');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The courses that are related to this user.
     */
    public function courses()
    {
        return $this->belongsToMany(BlackboardCourse::class)->withPivot('role_id');
    }
}
