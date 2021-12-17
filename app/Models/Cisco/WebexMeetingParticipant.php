<?php

namespace App\Models\Cisco;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * App\Models\Cisco\WebexMeetingParticipant
 *
 * @property-write mixed $display_name
 * @method static Builder|WebexMeetingParticipant newModelQuery()
 * @method static Builder|WebexMeetingParticipant newQuery()
 * @method static Builder|WebexMeetingParticipant query()
 * @mixin \Eloquent
 * @property string $id
 * @property bool $host
 * @property bool $co_host
 * @property bool $space_moderator
 * @property string $email
 * @property bool $invitee
 * @property string $state
 * @property \Illuminate\Support\Carbon $joined_time
 * @property \Illuminate\Support\Carbon $left_time
 * @property string $meeting_id
 * @property string $host_email
 * @property array $devices
 * @property \Illuminate\Support\Carbon $synced_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|WebexMeetingParticipant whereCoHost($value)
 * @method static Builder|WebexMeetingParticipant whereCreatedAt($value)
 * @method static Builder|WebexMeetingParticipant whereDevices($value)
 * @method static Builder|WebexMeetingParticipant whereDisplayName($value)
 * @method static Builder|WebexMeetingParticipant whereEmail($value)
 * @method static Builder|WebexMeetingParticipant whereHost($value)
 * @method static Builder|WebexMeetingParticipant whereHostEmail($value)
 * @method static Builder|WebexMeetingParticipant whereId($value)
 * @method static Builder|WebexMeetingParticipant whereInvitee($value)
 * @method static Builder|WebexMeetingParticipant whereJoinedTime($value)
 * @method static Builder|WebexMeetingParticipant whereLeftTime($value)
 * @method static Builder|WebexMeetingParticipant whereMeetingId($value)
 * @method static Builder|WebexMeetingParticipant whereSpaceModerator($value)
 * @method static Builder|WebexMeetingParticipant whereState($value)
 * @method static Builder|WebexMeetingParticipant whereSyncedAt($value)
 * @method static Builder|WebexMeetingParticipant whereUpdatedAt($value)
 * @property-read \App\Models\Cisco\WebexMeeting $meeting
 */
class WebexMeetingParticipant extends Model
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
        'host',
        'co_host',
        'space_moderator',
        'email',
        'display_name',
        'invitee',
        'state',
        'joined_time',
        'left_time',
        'meeting_id',
        'host_email',
        'devices',
        'synced_at',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'host' => 'boolean',
        'co_host' => 'boolean',
        'space_moderator' => 'boolean',
        'invitee' => 'boolean',
        'joined_time' => 'datetime',
        'left_time' => 'datetime',
        'devices' => 'array',
        'synced_at' => 'datetime',
    ];

    public function setDisplayNameAttribute($value)
    {
        $this->attributes['display_name'] = Str::limit($value);
    }

    public function meeting()
    {
        return $this->belongsTo(WebexMeeting::class, 'meeting_id', 'id');
    }
}
