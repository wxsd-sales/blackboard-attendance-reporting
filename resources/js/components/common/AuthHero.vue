<template>
    <div class="hero is-link">
        <div class="hero-body">
            <h1 class="title is-size-1">
                {{ $route.path.toLowerCase() === '/setup' ? 'Setup ' : '' }}Blackboard Attendance Reporting
            </h1>
            <p class="subtitle">
                Synchronize Webex Meeting attendance to your courses on Blackboard.
            </p>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AuthHero'
}
</script>

<style scoped>

</style>
