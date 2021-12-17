<?php

namespace App\Models\Cisco;

use App\Models\Blackboard\BlackboardCourse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * App\Models\Cisco\WebexScheduledMeeting
 *
 * @property-write mixed $title
 * @method static Builder|WebexScheduledMeeting newModelQuery()
 * @method static Builder|WebexScheduledMeeting newQuery()
 * @method static Builder|WebexScheduledMeeting query()
 * @mixin \Eloquent
 * @property string $id
 * @property string $meeting_series_id
 * @property string $state
 * @property bool $is_modified
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
 * @method static Builder|WebexScheduledMeeting whereCourseId($value)
 * @method static Builder|WebexScheduledMeeting whereCreatedAt($value)
 * @method static Builder|WebexScheduledMeeting whereEnd($value)
 * @method static Builder|WebexScheduledMeeting whereHostEmail($value)
 * @method static Builder|WebexScheduledMeeting whereHostUserId($value)
 * @method static Builder|WebexScheduledMeeting whereId($value)
 * @method static Builder|WebexScheduledMeeting whereIsModified($value)
 * @method static Builder|WebexScheduledMeeting whereMeetingSeriesId($value)
 * @method static Builder|WebexScheduledMeeting whereStart($value)
 * @method static Builder|WebexScheduledMeeting whereState($value)
 * @method static Builder|WebexScheduledMeeting whereSyncedAt($value)
 * @method static Builder|WebexScheduledMeeting whereTitle($value)
 * @method static Builder|WebexScheduledMeeting whereUpdatedAt($value)
 * @method static Builder|WebexScheduledMeeting whereWebLink($value)
 */
class WebexScheduledMeeting extends Model
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
        'title',
        'state',
        'is_modified',
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

    /**
     * Get the course that owns the scheduled meeting.
     */
    public function course()
    {
        return $this->belongsTo(BlackboardCourse::class, 'course_id', 'course_id');
    }
}
