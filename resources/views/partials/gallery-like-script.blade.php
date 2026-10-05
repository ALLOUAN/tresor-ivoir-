<script>
// Bascule j'aime/plus-j'aime sur la galerie — délégation d'événement sur le
// document car des cartes sont ajoutées dynamiquement (défilement infini),
// un écouteur posé une seule fois au chargement ne les couvrirait pas.
document.addEventListener('click', async function (e) {
    const btn = e.target.closest('.gallery-like-btn');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    if (btn.dataset.loading === '1') return;
    btn.dataset.loading = '1';

    const uuid = btn.dataset.mediaUuid;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    try {
        const res = await fetch(`/galerie-tresors-ivoire/visuelle/${uuid}/jaime`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        });
        if (!res.ok) throw new Error('request failed');
        const data = await res.json();

        document.querySelectorAll(`.gallery-like-btn[data-media-uuid="${uuid}"]`).forEach((el) => {
            el.dataset.liked = data.liked ? '1' : '0';
            el.setAttribute('aria-pressed', data.liked ? 'true' : 'false');
            const icon = el.querySelector('.like-icon');
            if (icon) {
                icon.classList.toggle('fas', data.liked);
                icon.classList.toggle('far', !data.liked);
                icon.classList.toggle('text-red-500', data.liked);
            }
            const count = el.querySelector('.like-count');
            if (count) count.textContent = data.likes_count;
        });
    } catch (err) {
        // Silencieux : l'état visuel reste inchangé, l'utilisateur peut réessayer.
    } finally {
        btn.dataset.loading = '0';
    }
});

// Comptage des téléchargements — ne doit surtout pas empêcher le téléchargement
// natif du navigateur (pas de preventDefault ici) : l'appel de suivi part en
// parallèle, silencieusement, pendant que le fichier se télécharge normalement.
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.gallery-download-btn');
    if (!btn) return;
    e.stopPropagation();

    const uuid = btn.dataset.mediaUuid;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    fetch(`/galerie-tresors-ivoire/visuelle/${uuid}/telechargement`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
    })
        .then((res) => (res.ok ? res.json() : null))
        .then((data) => {
            if (!data) return;
            document.querySelectorAll(`.gallery-download-btn[data-media-uuid="${uuid}"] .download-count`).forEach((el) => {
                el.textContent = data.downloads_count;
            });
        })
        .catch(() => {
            // Silencieux : le fichier a de toute façon déjà commencé à se télécharger.
        });
});
</script>
