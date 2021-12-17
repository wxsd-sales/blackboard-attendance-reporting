<?php

namespace App\Models\Blackboard;

use App\Models\Cisco\WebexMeeting;
use App\Models\Cisco\WebexScheduledMeeting;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * App\Models\Blackboard\BlackboardCourse
 *
 * @property-write mixed $name
 * @method static Builder|BlackboardCourse newModelQuery()
 * @method static Builder|BlackboardCourse newQuery()
 * @method static Builder|BlackboardCourse query()
 * @mixin Eloquent
 * @property string $id
 * @property string|null $external_id
 * @property string|null $course_id
 * @property string|null $term_id
 * @property array|null $availability
 * @property Carbon $synced_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|BlackboardUser[] $users
 * @property-read int|null $users_count
 * @method static Builder|BlackboardCourse whereAvailability($value)
 * @method static Builder|BlackboardCourse whereCourseId($value)
 * @method static Builder|BlackboardCourse whereCreatedAt($value)
 * @method static Builder|BlackboardCourse whereId($value)
 * @method static Builder|BlackboardCourse whereName($value)
 * @method static Builder|BlackboardCourse whereSyncedAt($value)
 * @method static Builder|BlackboardCourse whereTermId($value)
 * @method static Builder|BlackboardCourse whereUpdatedAt($value)
 * @property-read Collection|WebexMeeting[] $meetings
 * @property-read int|null $meetings_count
 * @property-read Collection|WebexScheduledMeeting[] $scheduledMeetings
 * @property-read int|null $scheduled_meetings_count
 */
class BlackboardCourse extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'course_id',
        'name',
        'term_id',
        'availability',
        'synced_at',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'availability' => 'array',
        'synced_at' => 'datetime',
    ];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = Str::limit($value);
    }

    /**
     * The users that are related to this course.
     */
    public function users()
    {
        return $this->belongsToMany(BlackboardUser::class)->withPivot('role_id');
    }

    /**
     * The meetings that are related to this course.
     */
    public function meetings()
    {
        return $this->hasMany(WebexMeeting::class, 'course_id', 'course_id');
    }

    /**
     * The scheduled meetings that are related to this course.
     */
    public function scheduledMeetings()
    {
        return $this->hasMany(WebexScheduledMeeting::class, 'course_id', 'course_id');
    }
}
