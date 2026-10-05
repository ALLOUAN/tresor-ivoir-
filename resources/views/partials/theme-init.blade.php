@include('partials.typography')
<script>
    (function () {
        document.getElementById('html-root')?.classList.remove('dark');
        try { localStorage.removeItem('tiTheme'); } catch (e) {}
    })();
</script>
