<script>
    document.addEventListener('alpine:init', () => {
        @include('dash.scripts.common')
        @include('dash.scripts.dashboard')
        @include('dash.scripts.clubs')
        @include('dash.scripts.users')
        @include('dash.scripts.teams')
        @include('dash.scripts.squads')
        @include('dash.scripts.subscriptions')
        @include('dash.scripts.audit')
        @include('dash.scripts.locations')
        @include('dash.scripts.trainings')
        @include('dash.scripts.system')
        @include('dash.scripts.profile')
    });
</script>
