@php $isEdit = $isEdit ?? false; $prefix = $isEdit ? 'edit_' : ''; @endphp

<div>
    <label for="{{ $prefix }}title" class="block text-xs text-slate-400 mb-1">Titre</label>
    <input type="text" name="title" id="{{ $prefix }}title" maxlength="150"
           class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition"
           placeholder="Ex : Marché d'Adjamé">
</div>

<div>
    <label for="{{ $prefix }}description" class="block text-xs text-slate-400 mb-1">Description (affichée dans la fenêtre au clic)</label>
    <textarea name="description" id="{{ $prefix }}description" rows="3"
              class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none resize-y"></textarea>
</div>

<div>
    <label class="block text-xs text-slate-400 mb-2">
        Image {{ $isEdit ? '(ajouter une image supplémentaire)' : '' }}
    </label>
    <label class="flex flex-col items-center justify-center border-2 border-dashed border-slate-700 hover:border-orange-500/50 rounded-xl p-3 cursor-pointer transition group">
        <i class="fas fa-cloud-arrow-up text-lg text-slate-600 group-hover:text-orange-400/70 mb-1 transition"></i>
        <span class="text-slate-500 text-[11px] group-hover:text-slate-300 transition">JPG, PNG, WebP — 5 Mo max</span>
        <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" class="hidden"
               onchange="const r=new FileReader();r.onload=e=>{document.getElementById('{{ $prefix }}image_preview').src=e.target.result;document.getElementById('{{ $prefix }}image_preview').classList.remove('hidden');};r.readAsDataURL(this.files[0])">
    </label>
    <img id="{{ $prefix }}image_preview" src="" class="mt-2 w-full h-24 object-cover rounded-lg border border-slate-700 hidden">
    @if($isEdit)
        <p class="text-[11px] text-slate-600 mt-1">Pour voir, réorganiser ou supprimer les images existantes, utilisez le bouton <i class="fas fa-images"></i> « Gérer les images » depuis la liste.</p>
    @endif
</div>

<div>
    <label class="block text-xs text-slate-400 mb-2">Position à l'écran</label>
    <div class="hp-position-picker relative w-full rounded-lg border border-slate-700 bg-slate-800 overflow-hidden cursor-crosshair select-none"
         style="aspect-ratio: 3 / 4;" id="{{ $prefix }}position_picker">
        <div class="absolute inset-0 opacity-40" style="background-image: linear-gradient(rgba(255,255,255,.08) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.08) 1px, transparent 1px); background-size: 10% 10%;"></div>
        <div class="absolute inset-0 flex items-center justify-center text-slate-600 text-[11px] pointer-events-none px-4 text-center leading-relaxed">
            Cliquez pour placer la bulle<br>(représente l'écran visible, du haut vers le bas)
        </div>
        <div id="{{ $prefix }}position_dot" class="absolute w-4 h-4 rounded-full bg-orange-500 border-2 border-white shadow-lg pointer-events-none" style="top:50%; left:50%; transform: translate(-50%,-50%);"></div>
    </div>
    <div class="grid grid-cols-2 gap-3 mt-2">
        <div>
            <label class="block text-[11px] text-slate-500 mb-1">Haut (%)</label>
            <input type="number" name="position_top" id="{{ $prefix }}position_top" min="0" max="100" step="0.1" value="50" required
                   class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
        </div>
        <div>
            <label class="block text-[11px] text-slate-500 mb-1">Gauche (%)</label>
            <input type="number" name="position_left" id="{{ $prefix }}position_left" min="0" max="100" step="0.1" value="50" required
                   class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-1.5 text-sm text-slate-100 outline-none">
        </div>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <label for="{{ $prefix }}size" class="block text-xs text-slate-400 mb-1">Taille</label>
        <select name="size" id="{{ $prefix }}size"
                class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
            <option value="sm">Petite</option>
            <option value="md" selected>Moyenne</option>
            <option value="lg">Grande</option>
        </select>
    </div>
    <div>
        <label for="{{ $prefix }}color_hex" class="block text-xs text-slate-400 mb-1">Couleur</label>
        <input type="color" name="color_hex" id="{{ $prefix }}color_hex" value="#f2790f"
               class="w-full h-[38px] bg-slate-800 border border-slate-700 rounded-lg px-1 py-1 outline-none cursor-pointer">
    </div>
    <div>
        <label for="{{ $prefix }}icon" class="block text-xs text-slate-400 mb-1">Icône</label>
        <input type="text" name="icon" id="{{ $prefix }}icon" maxlength="100" placeholder="fas fa-image"
               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition font-mono">
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="{{ $prefix }}link_url" class="block text-xs text-slate-400 mb-1">Lien / bouton d'action</label>
        <input type="text" name="link_url" id="{{ $prefix }}link_url" maxlength="500" placeholder="https://…"
               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
    </div>
    <div>
        <label for="{{ $prefix }}link_label" class="block text-xs text-slate-400 mb-1">Libellé du bouton</label>
        <input type="text" name="link_label" id="{{ $prefix }}link_label" maxlength="150" placeholder="En savoir plus"
               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none transition">
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="{{ $prefix }}display_order" class="block text-xs text-slate-400 mb-1">Ordre d'affichage</label>
        <input type="number" name="display_order" id="{{ $prefix }}display_order" min="0" max="9999" value="0"
               class="w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none">
    </div>
    <div class="flex items-end pb-2.5">
        <label class="flex items-center gap-2.5 cursor-pointer group">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" id="{{ $prefix }}is_active" value="1" checked
                   class="rounded border-slate-600 bg-slate-800 text-orange-500">
            <span class="text-sm text-slate-300 group-hover:text-white transition">Active</span>
        </label>
    </div>
</div>

<div>
    <label class="block text-xs text-slate-400 mb-2">Pages où afficher la bulle (en plus de l'accueil, toujours inclus)</label>
    <div id="{{ $prefix }}pages_list" class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1.5 bg-slate-800/60 border border-slate-700 rounded-lg p-3">
        @foreach(\App\Models\HomepageBubble::SELECTABLE_PAGES as $pageKey => $pageInfo)
            <label class="flex items-center gap-2 cursor-pointer group">
                <input type="checkbox" name="pages[]" value="{{ $pageKey }}"
                       id="{{ $prefix }}page_{{ \Illuminate\Support\Str::slug($pageKey, '_') }}"
                       data-page-key="{{ $pageKey }}"
                       class="rounded border-slate-600 bg-slate-800 text-orange-500 {{ $pageKey === 'all' ? 'hp-page-all' : 'hp-page-specific' }}">
                <span class="text-sm text-slate-300 group-hover:text-white transition {{ $pageKey === 'all' ? 'font-semibold' : '' }}">{{ $pageInfo['label'] }}</span>
            </label>
        @endforeach
    </div>
</div>

<script>
(function () {
    var picker = document.getElementById('{{ $prefix }}position_picker');
    var dot = document.getElementById('{{ $prefix }}position_dot');
    var topInput = document.getElementById('{{ $prefix }}position_top');
    var leftInput = document.getElementById('{{ $prefix }}position_left');
    if (!picker) return;

    function setDot(top, left) {
        dot.style.top = top + '%';
        dot.style.left = left + '%';
    }

    function updateFromInputs() {
        setDot(parseFloat(topInput.value) || 0, parseFloat(leftInput.value) || 0);
    }

    picker.addEventListener('click', function (e) {
        var rect = picker.getBoundingClientRect();
        var left = Math.min(100, Math.max(0, ((e.clientX - rect.left) / rect.width) * 100));
        var top = Math.min(100, Math.max(0, ((e.clientY - rect.top) / rect.height) * 100));
        topInput.value = top.toFixed(1);
        leftInput.value = left.toFixed(1);
        setDot(top, left);
    });

    topInput.addEventListener('input', updateFromInputs);
    leftInput.addEventListener('input', updateFromInputs);

    window['{{ $prefix }}syncBubblePicker'] = updateFromInputs;
    updateFromInputs();
})();

(function () {
    var list = document.getElementById('{{ $prefix }}pages_list');
    if (!list) return;
    var allBox = list.querySelector('.hp-page-all');
    var specificBoxes = list.querySelectorAll('.hp-page-specific');

    allBox.addEventListener('change', function () {
        if (allBox.checked) {
            specificBoxes.forEach(function (b) { b.checked = false; });
        }
    });
    specificBoxes.forEach(function (b) {
        b.addEventListener('change', function () {
            if (b.checked) allBox.checked = false;
        });
    });
})();
</script>
