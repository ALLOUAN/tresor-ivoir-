{{-- Comportements des cartes d'établissement (une seule fois par page) : favori instantané et partage. --}}
@once
<div class="ti-place-toast" role="status" aria-live="polite" hidden></div>
<script>
(function () {
    const token = {{ \Illuminate\Support\Js::from(csrf_token()) }};
    const favUrl = {{ \Illuminate\Support\Js::from(\Illuminate\Support\Facades\Route::has('visitor.favorites.toggle') ? route('visitor.favorites.toggle') : '') }};
    const copied = {{ \Illuminate\Support\Js::from(__('Lien copié dans le presse-papiers.')) }};
    const failed = {{ \Illuminate\Support\Js::from(__('Action impossible pour le moment, réessayez.')) }};
    let toastTimer;
    function toast(msg) {
        const t = document.querySelector('.ti-place-toast');
        if (!t) return;
        t.textContent = msg; t.hidden = false;
        clearTimeout(toastTimer); toastTimer = setTimeout(() => { t.hidden = true; }, 2600);
    }
    document.addEventListener('click', async (e) => {
        const fav = e.target.closest('[data-place-fav]');
        if (fav) {
            e.preventDefault();
            if (!favUrl || fav.disabled) return;
            fav.disabled = true;
            try {
                const r = await fetch(favUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ type: fav.dataset.favType || 'provider', id: Number(fav.dataset.placeFav) }),
                });
                if (!r.ok) throw new Error();
                const d = await r.json();
                // Un même établissement peut apparaître dans plusieurs sections : on met tous ses cœurs à jour.
                document.querySelectorAll('[data-place-fav="' + fav.dataset.placeFav + '"][data-fav-type="' + (fav.dataset.favType || 'provider') + '"], [data-place-fav="' + fav.dataset.placeFav + '"]:not([data-fav-type])').forEach((b) => {
                    if ((b.dataset.favType || 'provider') !== (fav.dataset.favType || 'provider')) return;
                    b.classList.toggle('is-on', d.favorited);
                    b.setAttribute('aria-pressed', d.favorited ? 'true' : 'false');
                });
                toast(d.message);
            } catch (err) { toast(failed); } finally { fav.disabled = false; }
            return;
        }
        const share = e.target.closest('[data-place-share]');
        if (share) {
            e.preventDefault();
            const url = new URL(share.dataset.placeShare, location.href).href;
            try {
                if (navigator.share) { await navigator.share({ title: share.dataset.placeTitle, url }); return; }
                await navigator.clipboard.writeText(url); toast(copied);
            } catch (err) { /* partage annulé */ }
        }
    });
})();
</script>
@endonce
