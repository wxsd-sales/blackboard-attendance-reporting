<?php

namespace App\Models\Cisco;

use App\Models\Blackboard\BlackboardCourse;
use App\Models\OAuth;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;


/**
 * App\Models\Cisco\WebexUser
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
 * @method static Builder|WebexUser newModelQuery()
 * @method static Builder|WebexUser newQuery()
 * @method static Builder|WebexUser query()
 * @method static Builder|WebexUser whereCreatedAt($value)
 * @method static Builder|WebexUser whereEmail($value)
 * @method static Builder|WebexUser whereId($value)
 * @method static Builder|WebexUser whereName($value)
 * @method static Builder|WebexUser whereSyncedAt($value)
 * @method static Builder|WebexUser whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Cisco\WebexMeeting[] $meetings
 * @property-read int|null $meetings_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Cisco\WebexScheduledMeeting[] $scheduledMeetings
 * @property-read int|null $scheduled_meetings_count
 */
class WebexUser extends Model
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
     * The meetings that are related to this user.
     */
    public function meetings()
    {
        return $this->hasMany(WebexMeeting::class, 'host_user_id', 'id');
    }

    /**
     * The scheduled meetings that are related to this user.
     */
    public function scheduledMeetings()
    {
        return $this->hasMany(WebexScheduledMeeting::class, 'host_user_id', 'id');
    }
}
