<style>
img {
    -webkit-user-drag: none;
    -webkit-touch-callout: none;
}
</style>
<script>
(function () {
    // Lazy-load all images that have no explicit loading attribute
    document.querySelectorAll('img:not([loading])').forEach(function (img) {
        img.setAttribute('loading', 'lazy');
    });

    // Disable right-click save on images
    document.addEventListener('contextmenu', function (e) {
        if (e.target.tagName === 'IMG') {
            e.preventDefault();
        }
    }, true);

    // Disable drag & drop to desktop / other tabs
    document.addEventListener('dragstart', function (e) {
        if (e.target.tagName === 'IMG') {
            e.preventDefault();
        }
    }, true);
})();
</script>
