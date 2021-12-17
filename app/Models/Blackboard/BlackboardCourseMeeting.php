<?php

namespace App\Models\Blackboard;

use App\Models\Cisco\WebexMeeting;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Blackboard\BlackboardCourseMeeting
 *
 * @property-read \App\Models\Blackboard\BlackboardCourse $course
 * @property-read WebexMeeting $webexMeeting
 * @method static \Illuminate\Database\Eloquent\Builder|BlackboardCourseMeeting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BlackboardCourseMeeting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BlackboardCourseMeeting query()
 * @mixin \Eloquent
 */
class BlackboardCourseMeeting extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'course_id',
        'webex_meeting_id',
        'synced_at',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(BlackboardCourse::class, 'course_id', 'id');
    }

    public function webexMeeting()
    {
        return $this->belongsTo(WebexMeeting::class);
    }
}
