<?php

namespace App\Models\Blackboard;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * App\Models\Blackboard\BlackboardCourseUser
 *
 * @property string $id
 * @property string $blackboard_course_id
 * @property string $blackboard_user_id
 * @property string $role_id
 * @property Carbon $synced_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|BlackboardCourseUser newModelQuery()
 * @method static Builder|BlackboardCourseUser newQuery()
 * @method static Builder|BlackboardCourseUser query()
 * @method static Builder|BlackboardCourseUser whereBlackboardCourseId($value)
 * @method static Builder|BlackboardCourseUser whereBlackboardUserId($value)
 * @method static Builder|BlackboardCourseUser whereCreatedAt($value)
 * @method static Builder|BlackboardCourseUser whereId($value)
 * @method static Builder|BlackboardCourseUser whereRoleId($value)
 * @method static Builder|BlackboardCourseUser whereSyncedAt($value)
 * @method static Builder|BlackboardCourseUser whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BlackboardCourseUser extends Pivot
{
    use HasFactory;

    protected $table = 'blackboard_course_blackboard_user';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'blackboard_course_id',
        'blackboard_user_id',
        'role_id',
        'synced_at',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'synced_at' => 'datetime',
    ];
}
