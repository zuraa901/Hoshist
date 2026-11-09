@php
    $titleId       = $titleId ?? null;
    $selectedTitle = $selectedTitle ?? null;
    $myRating      = $myRating ?? null;
    $currentScore  = (int) old('score', $myRating->score ?? 0);
    $currentReview = old('review', $myRating->review ?? '');
@endphp

<form method="POST" action="{{ route('rating.store') }}" class="rate-form">
    @csrf

    @if ($errors->any())
        <div class="alert">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if ($titleId)
        
        <input type="hidden" name="title_id" value="{{ $titleId }}">
    @else
        
        <div class="ac-wrap" id="title-picker" data-url="{{ route('title.search') }}">
            <label class="field-label" for="title_text">Judul</label>
            <input type="text" id="title_text" name="title_text" class="form-input" autocomplete="off"
                   placeholder="Ketik judul, misalnya: langit"
                   value="{{ old('title_text', $selectedTitle->title ?? '') }}"
                   role="combobox" aria-expanded="false" aria-controls="title-list" aria-autocomplete="list" required>
            <input type="hidden" name="title_id" id="title_id" value="{{ old('title_id', $selectedTitle->id ?? '') }}">
            <ul id="title-list" class="ac-list" role="listbox" hidden></ul>
        </div>
    @endif

    <div class="field-label" id="score-label">Skor (1-10)</div>
    <div class="score-row" role="radiogroup" aria-labelledby="score-label">
        @for ($i = 1; $i <= 10; $i++)
            <input type="radio" name="score" id="score{{ $i }}" value="{{ $i }}" class="score-input" {{ $currentScore === $i ? 'checked' : '' }} required>
            <label for="score{{ $i }}" class="sb">{{ $i }}</label>
        @endfor
    </div>

    <label class="field-label" for="review">Review (opsional)</label>
    <textarea name="review" id="review" class="form-textarea" rows="4" maxlength="1000" placeholder="Tulis review singkat...">{{ $currentReview }}</textarea>

    <div>
        <button type="submit" class="btn-gold">{{ $myRating ? 'Perbarui rating' : 'Simpan rating' }}</button>
    </div>
</form>

@if (! $titleId)
<script>
(function () {
    var wrap = document.getElementById('title-picker');
    if (!wrap) { return; }

    var input = document.getElementById('title_text');
    var hidden = document.getElementById('title_id');
    var list = document.getElementById('title-list');
    var url = wrap.getAttribute('data-url');
    var timer = null;
    var items = [];
    var active = -1;

    function close() {
        list.hidden = true;
        list.innerHTML = '';
        input.setAttribute('aria-expanded', 'false');
        active = -1;
    }

    function choose(item) {
        input.value = item.title;
        hidden.value = item.id;
        close();
    }

    function render() {
        list.innerHTML = '';

        if (items.length === 0) {
            var empty = document.createElement('li');
            empty.className = 'ac-empty';
            empty.textContent = 'Judul tidak ditemukan';
            list.appendChild(empty);
        } else {
            items.forEach(function (item, i) {
                var li = document.createElement('li');
                li.className = 'ac-item' + (i === active ? ' on' : '');
                li.setAttribute('role', 'option');
                var extra = item.type.charAt(0).toUpperCase() + item.type.slice(1);
                if (item.release_year) { extra += ', ' + item.release_year; }
                li.textContent = item.title + ' (' + extra + ')';
                li.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    choose(item);
                });
                list.appendChild(li);
            });
        }

        list.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    }

    function search() {
        var q = input.value.trim();
        if (q === '') { close(); return; }

        fetch(url + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) { items = data; active = -1; render(); })
            .catch(function () { close(); });
    }

    input.addEventListener('input', function () {
        hidden.value = '';
        clearTimeout(timer);
        timer = setTimeout(search, 200);
    });

    input.addEventListener('keydown', function (e) {
        if (list.hidden || items.length === 0) { return; }

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            active = (active + 1) % items.length;
            render();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            active = (active - 1 + items.length) % items.length;
            render();
        } else if (e.key === 'Enter' && active >= 0) {
            e.preventDefault();
            choose(items[active]);
        } else if (e.key === 'Escape') {
            close();
        }
    });

    input.addEventListener('blur', function () { setTimeout(close, 100); });
})();
</script>
@endif