<?php

namespace App\Models\Cisco;

use App\Models\Blackboard\BlackboardCourse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * App\Models\Cisco\WebexMeeting
 *
 * @method static Builder|WebexMeeting newModelQuery()
 * @method static Builder|WebexMeeting newQuery()
 * @method static Builder|WebexMeeting query()
 * @mixin \Eloquent
 * @property string $id
 * @property string $meeting_series_id
 * @property string $scheduled_meeting_id
 * @property string $state
 * @property \Illuminate\Support\Carbon $start
 * @property \Illuminate\Support\Carbon $end
 * @property string $host_user_id
 * @property string $host_email
 * @property string $web_link
 * @property string|null $course_id
 * @property \Illuminate\Support\Carbon $synced_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read BlackboardCourse|null $course
 * @method static Builder|WebexMeeting whereCourseId($value)
 * @method static Builder|WebexMeeting whereCreatedAt($value)
 * @method static Builder|WebexMeeting whereEnd($value)
 * @method static Builder|WebexMeeting whereHostEmail($value)
 * @method static Builder|WebexMeeting whereHostUserId($value)
 * @method static Builder|WebexMeeting whereId($value)
 * @method static Builder|WebexMeeting whereMeetingSeriesId($value)
 * @method static Builder|WebexMeeting whereScheduledMeetingId($value)
 * @method static Builder|WebexMeeting whereStart($value)
 * @method static Builder|WebexMeeting whereState($value)
 * @method static Builder|WebexMeeting whereSyncedAt($value)
 * @method static Builder|WebexMeeting whereTitle($value)
 * @method static Builder|WebexMeeting whereUpdatedAt($value)
 * @method static Builder|WebexMeeting whereWebLink($value)
 * @property string $title
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Cisco\WebexMeetingParticipant[] $participants
 * @property-read int|null $participants_count
 */
class WebexMeeting extends Model
{
    use HasFactory;

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
        'meeting_series_id',
        'scheduled_meeting_id',
        'title',
        'state',
        'start',
        'end',
        'host_user_id',
        'host_email',
        'web_link',
        'course_id',
        'synced_at',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'is_modified' => 'boolean',
        'start' => 'datetime',
        'end' => 'datetime',
        'synced_at' => 'datetime',
    ];

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = Str::limit($value);
    }

    public function getTitleAttribute()
    {
        return $this->attributes['title'];
    }

    /**
     * Get the course that owns the meeting.
     */
    public function course()
    {
        return $this->belongsTo(BlackboardCourse::class, 'course_id', 'course_id');
    }

    public function participants()
    {
        return $this->hasMany(WebexMeetingParticipant::class, 'meeting_id', 'id');
    }
}
