<template>
    <section>
        <auth-hero />
        <div class="columns is-centered is-multiline is-mobile my-1 py-6">
            <div class="column is-four-fifths">
                <div class="columns is-vcentered">
                    <div class="column is-three-fifths">
                        <h2 class="subtitle is-size-2">
                            Your Past Webex Meetings
                        </h2>
                    </div>
                    <div class="column is-two-fifths">
                        <b-field
                            label="Select a date range"
                            label-position="on-border"
                        >
                            <b-datepicker
                                v-model="dates"
                                placeholder="Click to select..."
                                size="is-medium"
                                icon="calendar-range"
                                :disabled="isDisabled || isLoading"
                                :loading="isLoading"
                                range
                                rounded
                                @input="isLoading = true; listMeetings(true)"
                            />
                        </b-field>
                    </div>
                </div>
            </div>
            <div class="column is-four-fifths">
                <b-switch
                    v-model="isTableFiltered"
                    type="is-link"
                >
                    Hide meetings without an active Course ID prefix.
                </b-switch>
            </div>
            <div class="column is-four-fifths is-hidden-mobile">
                <b-field
                    group-multiline
                    grouped
                >
                    <div
                        v-for="(column, index) in columnsVisible"
                        :key="index"
                        class="control"
                    >
                        <b-checkbox
                            v-model="column.isVisible"
                            :disabled="isLoading"
                            type="is-link"
                        >
                            {{ column.label }}
                        </b-checkbox>
                    </div>
                </b-field>
            </div>
            <div class="column is-four-fifths">
                <b-table
                    :data="filteredMeetings"
                    :default-sort="[columnsVisible.start.field]"
                    :loading="isLoading"
                    :height="600"
                    hoverable
                    sticky-header
                >
                    <b-table-column
                        v-slot="props"
                        :field="columnsVisible.id.field"
                        :label="columnsVisible.id.label"
                        :visible="columnsVisible.id.isVisible"
                        searchable
                        sortable
                        width="160"
                    >
                        <div :title="props.row.id">
                            {{ props.row.id.substr(props.row.id.lastIndexOf("_") + 1) }}
                        </div>
                    </b-table-column>
                    <b-table-column
                        v-slot="props"
                        :field="columnsVisible.meetingSeriesId.field"
                        :label="columnsVisible.meetingSeriesId.label"
                        :visible="columnsVisible.meetingSeriesId.isVisible"
                        searchable
                        sortable
                    >
                        {{ props.row.meetingSeriesId }}
                    </b-table-column>
                    <b-table-column
                        v-slot="props"
                        :field="columnsVisible.title.field"
                        :label="columnsVisible.title.label"
                        :visible="columnsVisible.title.isVisible"
                        searchable
                        sortable
                    >
                        {{ props.row.title }}
                    </b-table-column>
                    <b-table-column
                        v-slot="props"
                        :field="columnsVisible.start.field"
                        :label="columnsVisible.start.label"
                        :visible="columnsVisible.start.isVisible"
                        sortable
                        centered
                    >
                        {{ new Date(props.row.start).toLocaleString() }}
                    </b-table-column>
                    <b-table-column
                        v-slot="props"
                        :field="columnsVisible.end.field"
                        :label="columnsVisible.end.label"
                        :visible="columnsVisible.end.isVisible"
                        sortable
                        centered
                    >
                        {{ new Date(props.row.end).toLocaleString() }}
                    </b-table-column>
                    <b-table-column
                        v-slot="props"
                        :field="columnsVisible.hostUserId.field"
                        :label="columnsVisible.hostUserId.label"
                        :visible="columnsVisible.hostUserId.isVisible"
                        searchable
                        sortable
                        width="100"
                    >
                        <div :title="props.row.hostUserId">
                            {{ props.row.hostUserId.slice(-7) }}
                        </div>
                    </b-table-column>
                    <b-table-column
                        v-slot="props"
                        :field="columnsVisible.hostEmail.field"
                        :label="columnsVisible.hostEmail.label"
                        :visible="columnsVisible.hostEmail.isVisible"
                        searchable
                        sortable
                    >
                        {{ props.row.hostEmail }}
                    </b-table-column>
                    <b-table-column
                        v-slot="props"
                        :field="columnsVisible.webLink.field"
                        :label="columnsVisible.webLink.label"
                        :visible="columnsVisible.webLink.isVisible"
                        searchable
                        sortable
                    >
                        <b-button
                            icon-right="open-in-new"
                            size="is-small"
                            tag="a"
                            target="_blank"
                            type="is-link is-light"
                            class="is-rounded"
                            :href="props.row.webLink"
                            expanded
                        />
                    </b-table-column>
                    <b-table-column
                        v-slot="props"
                        :field="columnsVisible.courseId.field"
                        :label="columnsVisible.courseId.label"
                        :visible="columnsVisible.courseId.isVisible"
                        searchable
                        sortable
                    >
                        <b-field>
                            <b-select
                                v-model="props.row.courseId"
                                placeholder="Course ID"
                                icon="book"
                                :loading="props.row.isLoading"
                                :disabled="props.row.courseId"
                                rounded
                                expanded
                                @input="confirmMappingModal(props.row)"
                            >
                                <option
                                    v-for="courseId in courseIds"
                                    :key="courseId"
                                    :title="courseId"
                                    :value="courseId"
                                >
                                    {{ courseId }}
                                </option>
                            </b-select>
                        </b-field>
                    </b-table-column>
                </b-table>
            </div>
        </div>
    </section>
</template>

<script>

import ConfirmMappingModal from './common/ConfirmMappingModal'

export default {
    name: 'Dashboard',
    data () {
        return {
            dates: [
                new Date(Date.now() - 14 * 24 * 60 * 60 * 1000),
                new Date()
            ],
            syncStatus: null,
            courses: [],
            meetings: [],
            scheduledMeetings: [],
            isDisabled: false,
            isLoading: true,
            isTableFiltered: true,
            columnsVisible: {
                id: {
                    label: 'ID',
                    field: 'id',
                    isVisible: false
                },
                meetingSeriesId: {
                    label: 'Series ID',
                    field: 'meetingSeriesId',
                    isVisible: false
                },
                title: {
                    label: 'Title',
                    field: 'title',
                    isVisible: true
                },
                start: {
                    label: 'Start',
                    field: 'start',
                    isVisible: true
                },
                end: {
                    label: 'End',
                    field: 'end',
                    isVisible: true
                },
                hostUserId: {
                    label: 'Host ID',
                    field: 'hostUserId',
                    isVisible: false
                },
                hostEmail: {
                    label: 'Host Email',
                    field: 'hostEmail',
                    isVisible: false
                },
                webLink: {
                    label: 'Webex Link',
                    field: 'webLink',
                    isVisible: true
                },
                courseId: {
                    label: 'Course ID',
                    field: 'courseId',
                    isVisible: true
                }
            },
            error: {
                courses: null,
                meetings: null,
                scheduledMeetings: null
            }
        }
    },
    computed: {
        termIds () {
            return Array.from(new Set(this.courses.map(course => course.termId))).sort()
        },
        meetingsDateRange () {
            if (this.dates.length !== 2) {
                return null
            }
            const from = new Date(this.dates[0].setHours(0, 0, 0, 0))
                .toISOString()
                .replace('.000Z', 'Z')
            const to = new Date(this.dates[1].setHours(23, 59, 59, 0))
                .toISOString()
                .replace('.000Z', 'Z')

            return [from, to]
        },
        courseIds () {
            return this.courses.map(course => {
                return course.courseId
            })
        },
        filteredMeetings () {
            if (this.isTableFiltered) {
                return this.meetings.filter(meeting => {
                    return this.courseIds.some(courseId => meeting.title.startsWith(courseId))
                })
            }

            return this.meetings
        }
    },
    created () {
        this.listCourses(true)
        this.listMeetings(true)
        // this.listScheduledMeetings(true)
    },
    methods: {
        retrieveCourses () {
            window.axios.get('/retrieveBlackboardUserCourses')
                .catch(error => {
                    console.error(error)
                    this.error.courses = 'Could not retrieve courses.'
                })
        },
        loadCourses () {
            window.axios.get('/blackboardUserCourses')
                .then(response => {
                    function getCourses (course) {
                        return {
                            id: course.id,
                            courseId: course.course_id,
                            name: course.name,
                            termId: course.term_id,
                            availability: course.availability,
                            syncedAt: course.synced_at,
                            createdAt: course.created_at,
                            updatedAt: course.updated_at
                        }
                    }

                    this.courses = response.data.map(o => getCourses(o))
                })
                .catch(error => {
                    console.error(error)
                    this.error.courses = 'Could not load courses.'
                })
        },
        async listCourses (refresh = false) {
            this.isDisabled = true
            this.isLoading = true
            this.error.courses = []
            if (refresh) {
                await this.retrieveCourses()
            }
            await this.loadCourses()
            this.isDisabled = false
            this.isLoading = false
        },
        filterCourses (courseId) {
            return this.courses.filter(course => course.courseId === courseId)
        },
        retrieveMeetings () {
            window.axios.get('/retrieveWebexMeetings', {
                params: {
                    from: this.meetingsDateRange[0],
                    to: this.meetingsDateRange[1]
                }
            })
                .catch(error => {
                    console.error(error)
                    this.error.meetings = 'Could not retrieve meetings.'
                })
        },
        loadMeetings () {
            window.axios.get('/webexMeetings', {
                params: {
                    from: this.meetingsDateRange[0],
                    to: this.meetingsDateRange[1]
                }
            })
                .then(response => {
                    function getMeetings (meeting) {
                        return {
                            id: meeting.id,
                            meetingSeriesId: meeting.meeting_series_id,
                            scheduledMeetingId: meeting.scheduled_meeting_id,
                            title: meeting.title,
                            state: meeting.state,
                            start: meeting.start,
                            end: meeting.end,
                            hostUserId: meeting.host_user_id,
                            hostEmail: meeting.host_email,
                            webLink: meeting.web_link,
                            courseId: meeting.course_id,
                            syncedAt: meeting.synced_at,
                            createdAt: meeting.created_at,
                            updatedAt: meeting.updated_at,
                            isLoading: false
                        }
                    }

                    this.meetings = response.data.map(o => getMeetings(o))
                })
                .catch(error => {
                    console.error(error)
                    this.error.meetings = 'Could not load meetings.'
                })
        },
        async listMeetings (refresh = false) {
            this.isDisabled = true
            this.isLoading = true
            this.error.meetings = []
            if (refresh) {
                await this.retrieveMeetings()
            }
            await this.loadMeetings()
            this.isDisabled = false
            this.isLoading = false
        },
        retrieveScheduledMeetings () {
            window.axios.get('/retrieveWebexScheduledMeetings', {
                params: {
                    from: this.meetingsDateRange[0],
                    to: this.meetingsDateRange[1]
                }
            })
                .catch(error => {
                    console.error(error)
                    this.error.meetings = 'Could not retrieve scheduled meetings.'
                })
        },
        loadScheduledMeetings () {
            window.axios.get('/webexScheduledMeetings', {
                params: {
                    from: this.meetingsDateRange[0],
                    to: this.meetingsDateRange[1]
                }
            })
                .then(response => {
                    function getScheduledMeetings (scheduledMeeting) {
                        return {
                            id: scheduledMeeting.id,
                            meetingSeriesId: scheduledMeeting.meeting_series_id,
                            title: scheduledMeeting.title,
                            state: scheduledMeeting.state,
                            isModified: scheduledMeeting.is_modified,
                            start: scheduledMeeting.start,
                            end: scheduledMeeting.end,
                            hostUserId: scheduledMeeting.host_user_id,
                            hostEmail: scheduledMeeting.host_email,
                            webLink: scheduledMeeting.web_link,
                            courseId: scheduledMeeting.course_id,
                            syncedAt: scheduledMeeting.synced_at,
                            createdAt: scheduledMeeting.created_at,
                            updatedAt: scheduledMeeting.updated_at,
                            isLoading: false
                        }
                    }

                    this.scheduledMeetings = response.data.map(o => getScheduledMeetings(o))
                })
                .catch(error => {
                    console.error(error)
                    this.error.meetings = 'Could not load scheduled meetings.'
                })
        },
        async listScheduledMeetings (refresh = false) {
            this.isDisabled = true
            this.isLoading = true
            this.error.scheduledMeetings = []
            if (refresh) {
                await this.retrieveScheduledMeetings()
            }
            await this.loadScheduledMeetings()
            this.isDisabled = false
            this.isLoading = false
        },
        confirmMappingModal (meetingRow) {
            this.$buefy.modal.open({
                parent: this,
                props: {
                    meetingTitle: meetingRow.title,
                    meetingId: meetingRow.id,
                    courseId: meetingRow.courseId
                },
                component: ConfirmMappingModal,
                hasModalCard: true,
                trapFocus: true,
                canCancel: false,
                scroll: 'keep',
                events: {
                    close: (status) => {
                        if (status) {
                            this.$buefy.snackbar.open({
                                message: `Processed attendance records for ${meetingRow.title}`,
                                pauseOnHover: true,
                                type: 'is-success'
                            })
                        } else {
                            meetingRow.courseId = null
                        }
                    }
                }
            })
        }
    }
}
</script>

<style scoped>

</style>
