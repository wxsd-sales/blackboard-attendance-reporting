<template>
    <form
        action=""
        class="mx-2"
    >
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">
                    Add Mapping
                </p>
            </header>
            <section class="modal-card-body">
                <p>
                    This will create a new attendance grade book column under Blackboard Course
                    <strong>{{ courseId }}</strong> for Webex Meeting <strong>{{ meetingTitle }}</strong>.
                </p>
                <b-message
                    v-if="syncStatus.message"
                    :type="syncStatus.type"
                    size="is-small"
                    class="mt-2"
                >
                    {{ syncStatus.message }}
                </b-message>
                <b-message
                    v-else
                    type="is-link"
                    size="is-small"
                    class="mt-2"
                >
                    Please note that it can take some time to process big classes (with many students).
                </b-message>
            </section>
            <footer class="modal-card-foot">
                <b-button
                    icon-left="close"
                    type="is-danger"
                    label="Cancel"
                    size="is-medium"
                    :disabled="isButtonLoading"
                    rounded
                    expanded
                    @click="$emit('close', false)"
                /><b-button
                    icon-left="check"
                    type="is-link"
                    label="Confirm"
                    size="is-medium"
                    :loading="isButtonLoading"
                    rounded
                    @click="performAttendanceSync"
                />
            </footer>
        </div>
    </form>
</template>

<script>
export default {
    name: 'ConfirmMappingModal',
    props: {
        meetingTitle: {
            type: String,
            required: true
        },
        meetingId: {
            type: String,
            required: true
        },
        courseId: {
            type: String,
            required: true
        }
    },
    data () {
        return {
            syncStatus: {
                type: 'is-info',
                message: null
            },
            isButtonLoading: false
        }
    },
    methods: {
        async retrieveUsers () {
            const [retrieveBlackboardCourseUsers, retrieveWebexMeetingParticipants] = await Promise.all([
                window.axios.get('/retrieveBlackboardCourseUsers', {
                    params: {
                        courseId: this.courseId
                    }
                }),
                window.axios.get('/retrieveWebexMeetingParticipants', {
                    params: {
                        meetingId: this.meetingId
                    }
                })
            ])

            return retrieveBlackboardCourseUsers.status === 200 && retrieveWebexMeetingParticipants.status === 200
        },
        async performAttendanceSync () {
            this.isButtonLoading = true
            this.syncStatus.type = 'is-link'
            this.syncStatus.message = 'Retrieving user records...'

            if (!(await this.retrieveUsers())) {
                this.syncStatus = 'Failed to retrieve user records.'
                this.isButtonLoading = false
                return
            }

            window.axios.get('/performAttendanceSync', {
                params: {
                    meetingId: this.meetingId,
                    courseId: this.courseId
                }
            })
                .then(() => {
                    this.type = 'is-link'
                    this.syncStatus.message = `Last sync completed on ${new Date()}.`
                    this.$emit('close', true)
                })
                .catch(() => {
                    this.type = 'is-danger'
                    this.syncStatus.message = 'Failed to update Attendance records.'
                })
                .finally(() => {
                    this.isButtonLoading = false
                })
        }
    }
}
</script>

<style scoped>

</style>
