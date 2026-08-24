@if(isset($homeBubbles) && $homeBubbles->isNotEmpty())
<style>
    .hp-bubble-layer { position: absolute; inset: 0; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 30; }
    .hp-bubble {
        position: absolute;
        transform: translate(-50%, -50%);
        pointer-events: auto;
        border: none;
        border-radius: 9999px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff !important;
        box-shadow: 0 8px 24px rgba(0,0,0,.35), 0 0 0 4px rgba(255,255,255,.12);
        animation: hp-bubble-float 4.5s ease-in-out infinite;
        transition: transform .25s ease, box-shadow .25s ease;
    }
    .hp-bubble:hover, .hp-bubble:focus-visible {
        transform: translate(-50%, -50%) scale(1.12);
        box-shadow: 0 12px 32px rgba(0,0,0,.4), 0 0 0 5px rgba(255,255,255,.2);
    }
    .hp-bubble::after {
        content: '';
        position: absolute; inset: -6px;
        border-radius: 9999px;
        border: 2px solid rgba(255,255,255,.5);
        animation: hp-bubble-pulse 2.6s ease-out infinite;
    }
    .hp-bubble-img {
        position: absolute; inset: 0;
        width: 100%; height: 100%;
        object-fit: cover;
        border-radius: 9999px;
    }
    .hp-bubble-sm { width: 44px; height: 44px; font-size: 16px; }
    .hp-bubble-md { width: 60px; height: 60px; font-size: 20px; }
    .hp-bubble-lg { width: 78px; height: 78px; font-size: 26px; }
    @media (max-width: 640px) {
        .hp-bubble-sm { width: 36px; height: 36px; font-size: 13px; }
        .hp-bubble-md { width: 48px; height: 48px; font-size: 16px; }
        .hp-bubble-lg { width: 60px; height: 60px; font-size: 20px; }
    }
    .hp-bubble-count {
        position: absolute; top: -4px; right: -4px;
        min-width: 18px; height: 18px; padding: 0 4px;
        border-radius: 9999px; background: #111827; color: #fff !important;
        font-size: 10px; font-weight: 700; display: flex; align-items: center; justify-content: center;
        box-shadow: 0 0 0 2px rgba(255,255,255,.6);
    }
    @keyframes hp-bubble-float {
        0%, 100% { margin-top: 0; }
        50% { margin-top: -10px; }
    }
    @keyframes hp-bubble-pulse {
        0% { opacity: .55; transform: scale(1); }
        100% { opacity: 0; transform: scale(1.35); }
    }

    /* Lightbox */
    #hp-bubble-modal { position: fixed; inset: 0; z-index: 90; display: none; align-items: center; justify-content: center; padding: 1rem; }
    #hp-bubble-modal.is-open { display: flex; }
    #hp-bubble-modal .hp-modal-backdrop { position: absolute; inset: 0; background: rgba(6, 12, 8, .82); backdrop-filter: blur(4px); }
    #hp-bubble-modal .hp-modal-panel {
        position: relative; width: 100%; max-width: 640px; max-height: 90vh;
        background: #100d0a; border-radius: 20px; overflow: hidden;
        border: 1px solid rgba(255,255,255,.08);
        display: flex; flex-direction: column;
        animation: hp-modal-in .25s ease-out;
    }
    @keyframes hp-modal-in { from { opacity: 0; transform: scale(.96) translateY(8px); } to { opacity: 1; transform: scale(1) translateY(0); } }
    #hp-bubble-modal .hp-modal-close {
        position: absolute; top: 12px; right: 12px; z-index: 5;
        width: 36px; height: 36px; border-radius: 9999px;
        background: rgba(0,0,0,.55); color: #fff !important; border: 1px solid rgba(255,255,255,.15);
        display: flex; align-items: center; justify-content: center; cursor: pointer;
    }
    #hp-bubble-modal .hp-modal-image-wrap { position: relative; width: 100%; aspect-ratio: 4/3; background: #000; }
    #hp-bubble-modal .hp-modal-image-wrap img { width: 100%; height: 100%; object-fit: cover; display: block; }
    #hp-bubble-modal .hp-modal-nav {
        position: absolute; top: 50%; transform: translateY(-50%);
        width: 38px; height: 38px; border-radius: 9999px;
        background: rgba(0,0,0,.5); color: #fff !important; border: 1px solid rgba(255,255,255,.15);
        display: flex; align-items: center; justify-content: center; cursor: pointer;
    }
    #hp-bubble-modal .hp-modal-nav.prev { left: 10px; }
    #hp-bubble-modal .hp-modal-nav.next { right: 10px; }
    #hp-bubble-modal .hp-modal-dots { position: absolute; bottom: 12px; left: 0; right: 0; display: flex; justify-content: center; gap: 6px; }
    #hp-bubble-modal .hp-modal-dot { width: 7px; height: 7px; border-radius: 9999px; background: rgba(255,255,255,.4); cursor: pointer; }
    #hp-bubble-modal .hp-modal-dot.is-active { background: #fff; }
    #hp-bubble-modal .hp-modal-body { padding: 1.25rem 1.5rem 1.5rem; color: #fff !important; }
    #hp-bubble-modal .hp-modal-title { font-family: 'Playfair Display', Georgia, serif; font-size: 1.4rem; font-weight: 700; color: #fff !important; margin-bottom: .5rem; }
    #hp-bubble-modal .hp-modal-desc { color: rgba(255,255,255,.75) !important; font-size: .92rem; line-height: 1.6; }
    #hp-bubble-modal .hp-modal-link {
        display: inline-flex; align-items: center; gap: .5rem; margin-top: 1rem;
        padding: .55rem 1.1rem; border-radius: 9999px; background: #f2790f; color: #000 !important;
        font-size: .85rem; font-weight: 600; text-decoration: none;
    }
</style>

<div class="hp-bubble-layer" aria-hidden="false">
    @foreach($homeBubbles as $bubble)
        @php
            $images = $bubble->images;
            $imagesJson = $images->map(fn ($img) => $img->image_url)->values()->toJson();
            $bgStyle = $bubble->color_hex
                ? "background:{$bubble->color_hex};"
                : 'background: linear-gradient(135deg, #f2790f, #d4630a);';
        @endphp
        <button type="button"
            class="hp-bubble hp-bubble-{{ $bubble->size }}"
            style="top:{{ $bubble->position_top }}%; left:{{ $bubble->position_left }}%; {{ $bgStyle }}"
            data-title="{{ $bubble->title }}"
            data-description="{{ $bubble->description }}"
            data-link-url="{{ $bubble->link_url }}"
            data-link-label="{{ $bubble->link_label }}"
            data-images='{{ $imagesJson }}'
            aria-label="{{ $bubble->title ?: 'Découvrir' }}">
            @if($images->count() > 1)
                <span class="hp-bubble-count">{{ $images->count() }}</span>
            @endif
            @if($images->isNotEmpty())
                <img src="{{ $images->first()->image_url }}" alt="" class="hp-bubble-img">
            @else
                <i class="{{ $bubble->icon ?: 'fas fa-image' }}"></i>
            @endif
        </button>
    @endforeach
</div>

{{-- Modal partagé (lightbox) --}}
<div id="hp-bubble-modal" role="dialog" aria-modal="true">
    <div class="hp-modal-backdrop" data-hp-close></div>
    <div class="hp-modal-panel">
        <button type="button" class="hp-modal-close" data-hp-close aria-label="Fermer">
            <i class="fas fa-xmark"></i>
        </button>
        <div class="hp-modal-image-wrap">
            <img id="hp-modal-img" src="" alt="">
            <button type="button" class="hp-modal-nav prev" id="hp-modal-prev" aria-label="Précédent">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button type="button" class="hp-modal-nav next" id="hp-modal-next" aria-label="Suivant">
                <i class="fas fa-chevron-right"></i>
            </button>
            <div class="hp-modal-dots" id="hp-modal-dots"></div>
        </div>
        <div class="hp-modal-body">
            <h3 class="hp-modal-title" id="hp-modal-title"></h3>
            <p class="hp-modal-desc" id="hp-modal-desc"></p>
            <a href="#" id="hp-modal-link" class="hp-modal-link" target="_blank" rel="noopener">
                <span id="hp-modal-link-label">En savoir plus</span>
                <i class="fas fa-arrow-up-right-from-square text-xs"></i>
            </a>
        </div>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('hp-bubble-modal');
    if (!modal) return;

    var imgEl = document.getElementById('hp-modal-img');
    var titleEl = document.getElementById('hp-modal-title');
    var descEl = document.getElementById('hp-modal-desc');
    var linkEl = document.getElementById('hp-modal-link');
    var linkLabelEl = document.getElementById('hp-modal-link-label');
    var dotsEl = document.getElementById('hp-modal-dots');
    var prevBtn = document.getElementById('hp-modal-prev');
    var nextBtn = document.getElementById('hp-modal-next');

    var currentImages = [];
    var currentIndex = 0;

    function renderImage() {
        if (!currentImages.length) return;
        imgEl.src = currentImages[currentIndex];
        var dots = dotsEl.querySelectorAll('.hp-modal-dot');
        dots.forEach(function (dot, i) {
            dot.classList.toggle('is-active', i === currentIndex);
        });
    }

    function buildDots() {
        dotsEl.innerHTML = '';
        if (currentImages.length <= 1) return;
        currentImages.forEach(function (_, i) {
            var dot = document.createElement('span');
            dot.className = 'hp-modal-dot' + (i === 0 ? ' is-active' : '');
            dot.addEventListener('click', function () {
                currentIndex = i;
                renderImage();
            });
            dotsEl.appendChild(dot);
        });
    }

    function openModal(btn) {
        var title = btn.getAttribute('data-title') || '';
        var description = btn.getAttribute('data-description') || '';
        var linkUrl = btn.getAttribute('data-link-url') || '';
        var linkLabel = btn.getAttribute('data-link-label') || 'En savoir plus';
        try {
            currentImages = JSON.parse(btn.getAttribute('data-images') || '[]');
        } catch (e) {
            currentImages = [];
        }
        currentIndex = 0;

        titleEl.textContent = title;
        titleEl.style.display = title ? '' : 'none';
        descEl.textContent = description;
        descEl.style.display = description ? '' : 'none';

        if (linkUrl) {
            linkEl.href = linkUrl;
            linkLabelEl.textContent = linkLabel;
            linkEl.style.display = 'inline-flex';
        } else {
            linkEl.style.display = 'none';
        }

        var hasMultiple = currentImages.length > 1;
        prevBtn.style.display = hasMultiple ? 'flex' : 'none';
        nextBtn.style.display = hasMultiple ? 'flex' : 'none';
        buildDots();
        renderImage();

        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.hp-bubble').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openModal(btn);
        });
    });

    modal.querySelectorAll('[data-hp-close]').forEach(function (el) {
        el.addEventListener('click', closeModal);
    });

    prevBtn.addEventListener('click', function () {
        if (!currentImages.length) return;
        currentIndex = (currentIndex - 1 + currentImages.length) % currentImages.length;
        renderImage();
    });
    nextBtn.addEventListener('click', function () {
        if (!currentImages.length) return;
        currentIndex = (currentIndex + 1) % currentImages.length;
        renderImage();
    });

    document.addEventListener('keydown', function (e) {
        if (!modal.classList.contains('is-open')) return;
        if (e.key === 'Escape') closeModal();
        if (e.key === 'ArrowLeft') prevBtn.click();
        if (e.key === 'ArrowRight') nextBtn.click();
    });
})();
</script>
@endif
