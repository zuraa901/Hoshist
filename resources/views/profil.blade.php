
@extends('layouts.app')

@section('title', 'Profil')

@section('content')

   

    <div class="banner"
        @if($user->banner_path)
            style="background-image: url('{{ Storage::url($user->banner_path) }}'); background-size: cover; background-position: center;"
        @endif
    ></div>
    <div class="profile-head">

        <div class="profile-top">
            @if ($user->avatar_path)
            <img
                src="{{ Storage::url($user->avatar_path) }}"
                class="av av-lg"
                alt="Foto profil {{ $user->name }}"
                style="object-fit: cover;"
            >
        @else
            <div class="av av-lg">
                {{ strtoupper(mb_substr($user->name, 0, 1)) }}
            </div>
        @endif

            <a href="{{ route('profile.edit') }}" class="ob">
                Edit profil
            </a>
        </div>

        <h1 class="profile-name">{{ $user->name }}</h1>

        <div class="m">&#64;{{ $user->username }}</div>

        @if ($user->bio)
            <p class="profile-bio">{{ $user->bio }}</p>
        @else
            <p class="profile-bio m">Belum ada bio.</p>
        @endif

        @if ($genres->isNotEmpty())
            <div class="tags">
                @foreach ($genres as $genre)
                    <span class="tg">{{ $genre }}</span>
                @endforeach
            </div>
        @endif

        <div class="stats">
            <div>
                <b>{{ $stats['ratings'] }}</b>
                <span class="m">Rating</span>
            </div>

            <div>
                <b>{{ $stats['following'] }}</b>
                <span class="m">Mengikuti</span>
            </div>

            <div>
                <b>{{ $stats['followers'] }}</b>
                <span class="m">Pengikut</span>
            </div>
        </div>

    </div>


    
    <div class="tabs profile-tabs">

        <button type="button" class="tab on" data-tab="postingan">
            <span class="tl">Postingan</span>
        </button>

        <button type="button" class="tab" data-tab="rating">
            <span class="tl">Rating</span>
        </button>

        <button type="button" class="tab" data-tab="daftar">
            <span class="tl">Daftar</span>
        </button>

        <button type="button" class="tab" data-tab="favorit">
            <span class="tl">Favorit</span>
        </button>

    </div>


   

    <section class="profile-panel" id="panel-postingan">


        <div class="composer">

            <div class="composer-top">

                <div class="av">
                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                </div>

                <div class="composer-input">
                    <textarea
                        id="postContent"
                        placeholder="Apa yang sedang kamu pikirkan tentang hiburan hari ini?"
                        maxlength="1000"
                    ></textarea>
                </div>

            </div>


           
            <div id="imagePreviewContainer" class="image-preview-container" hidden>

                <img id="imagePreview" src="" alt="Preview gambar">

                <button type="button" id="removeImage" class="remove-image">
                    ✕
                </button>

            </div>


            {{-- COMPOSER BOTTOM --}}

            <div class="composer-bottom">

                <div class="composer-tools">

                    <label for="postImage" class="composer-tool">
                        <svg width="19" height="19" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor"
                             stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="3"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <path d="M21 15l-5-5L5 21"/>
                        </svg>
                        Foto
                    </label>

                    <input
                        type="file"
                        id="postImage"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                        hidden
                    >

                    <span class="composer-hint">
                        Maks. 2 MB
                    </span>

                </div>

                <button type="button" id="publishPost" class="publish-btn">
                    Posting
                </button>

            </div>

            <p id="postError" class="post-error"></p>

        </div>



        <div id="postFeed">

            <div class="empty" id="emptyPosts">
                Belum ada postingan. Yuk, bagikan sesuatu!
            </div>

        </div>

    </section>


   
    <section class="profile-panel" id="panel-rating" hidden>

        @forelse ($ratings as $r)

            <article class="post">

                <div class="av">
                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                </div>

                <div class="post-body">

                    <div class="post-head">
                        <b>{{ $user->name }}</b>

                        <span class="m">
                            &#64;{{ $user->username }}
                            &middot;
                            {{ \Carbon\Carbon::parse($r->created_at)->locale('id')->diffForHumans() }}
                        </span>
                    </div>

                    <div class="post-about m">
                        menilai <b>{{ $r->title }}</b>

                        <span class="r">
                            <svg width="14" height="14"
                                 viewBox="0 0 24 24"
                                 fill="#F5C451"
                                 aria-hidden="true">
                                <path d="M12 2l3 6.9 7.5.7-5.7 5 1.7 7.4L12 18l-6.5 4 1.7-7.4-5.7-5 7.5-.7z"/>
                            </svg>

                            {{ $r->score }}
                        </span>
                    </div>

                    @if ($r->review)
                        <p class="post-text">{{ $r->review }}</p>
                    @endif

                </div>

            </article>

        @empty

            <div class="empty">
                Belum ada rating. Mulai beri rating pertamamu!
            </div>

        @endforelse

    </section>


    

    <section class="profile-panel" id="panel-daftar" hidden>

        <div class="empty">
            Belum ada daftar tontonan.
        </div>

    </section>


    
    <section class="profile-panel" id="panel-favorit" hidden>

        <div class="empty">
            Belum ada judul favorit.
        </div>

    </section>


    

    <script>

        document.addEventListener('DOMContentLoaded', function () {

            
            const username = @json($user->username);

            const storageKey = 'hoshist_posts_' + username;


         
            const textarea = document.getElementById('postContent');
            const imageInput = document.getElementById('postImage');
            const imagePreview = document.getElementById('imagePreview');
            const imageContainer = document.getElementById('imagePreviewContainer');
            const removeImage = document.getElementById('removeImage');
            const publishButton = document.getElementById('publishPost');
            const postFeed = document.getElementById('postFeed');
            const emptyPosts = document.getElementById('emptyPosts');
            const postError = document.getElementById('postError');

            let selectedImage = null;


            
            function getPosts() {

                try {
                    return JSON.parse(localStorage.getItem(storageKey)) || [];
                } catch (error) {
                    return [];
                }

            }


            
            function savePosts(posts) {

                try {
                    localStorage.setItem(storageKey, JSON.stringify(posts));
                    return true;
                } catch (error) {
                    postError.textContent =
                        'Penyimpanan penuh. Coba gunakan gambar yang lebih kecil.';
                    return false;
                }

            }


            
            imageInput.addEventListener('change', function () {

                const file = this.files[0];

                postError.textContent = '';

                if (!file) return;

                if (!file.type.startsWith('image/')) {
                    postError.textContent = 'File harus berupa gambar.';
                    this.value = '';
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    postError.textContent = 'Ukuran gambar maksimal 2 MB.';
                    this.value = '';
                    return;
                }

                const reader = new FileReader();

                reader.onload = function (event) {

                    selectedImage = event.target.result;

                    imagePreview.src = selectedImage;

                    imageContainer.hidden = false;

                };

                reader.readAsDataURL(file);

            });


            
            removeImage.addEventListener('click', function () {

                selectedImage = null;

                imageInput.value = '';

                imagePreview.src = '';

                imageContainer.hidden = true;

            });


        
            publishButton.addEventListener('click', function () {

                const content = textarea.value.trim();

                postError.textContent = '';

                if (!content && !selectedImage) {
                    postError.textContent =
                        'Tulis sesuatu atau tambahkan gambar terlebih dahulu!';
                    return;
                }

                const posts = getPosts();

                const newPost = {

                    id: Date.now(),

                    username: username,

                    content: content,

                    image: selectedImage,

                    likes: 0,

                    liked: false,

                    createdAt: new Date().toISOString()

                };

                posts.unshift(newPost);

                if (!savePosts(posts)) return;

                textarea.value = '';

                selectedImage = null;

                imageInput.value = '';

                imagePreview.src = '';

                imageContainer.hidden = true;

                renderPosts();

            });


            
            function formatTime(date) {

                const seconds = Math.floor(
                    (Date.now() - new Date(date).getTime()) / 1000
                );

                if (seconds < 60) return 'Baru saja';

                if (seconds < 3600)
                    return Math.floor(seconds / 60) + ' menit yang lalu';

                if (seconds < 86400)
                    return Math.floor(seconds / 3600) + ' jam yang lalu';

                return Math.floor(seconds / 86400) + ' hari yang lalu';

            }


            
            function renderPosts() {

                const posts = getPosts();

                postFeed.innerHTML = '';

                if (posts.length === 0) {

                    emptyPosts.hidden = false;

                    postFeed.appendChild(emptyPosts);

                    return;

                }

                emptyPosts.hidden = true;

                posts.forEach(function (post) {

                    const article = document.createElement('article');

                    article.className = 'post user-post';

                    article.dataset.id = post.id;


                    
                    const avatar = document.createElement('div');

                    avatar.className = 'av';

                    avatar.textContent = username.charAt(0).toUpperCase();


                    
                    const body = document.createElement('div');

                    body.className = 'post-body';


                    
                    const header = document.createElement('div');

                    header.className = 'post-head';

                    const name = document.createElement('b');

                    name.textContent = @json($user->name);

                    const meta = document.createElement('span');

                    meta.className = 'm';

                    meta.textContent =
                        '@' + username + ' · ' + formatTime(post.createdAt);

                    header.append(name, meta);


                    
                    body.appendChild(header);

                    if (post.content) {

                        const text = document.createElement('p');

                        text.className = 'post-text';

                        text.textContent = post.content;

                        body.appendChild(text);

                    }


                    
                    if (post.image) {

                        const image = document.createElement('img');

                        image.src = post.image;

                        image.alt = 'Gambar postingan';

                        image.className = 'user-post-image';

                        body.appendChild(image);

                    }


                    
                    const actions = document.createElement('div');

                    actions.className = 'user-post-actions';


                    
                    const likeButton = document.createElement('button');

                    likeButton.type = 'button';

                    likeButton.className = post.liked ? 'post-liked' : '';

                    likeButton.textContent =
                        (post.liked ? '♥ ' : '♡ ') + post.likes;


                    likeButton.addEventListener('click', function () {

                        const currentPosts = getPosts();

                        const target = currentPosts.find(
                            item => item.id === post.id
                        );

                        if (!target) return;

                        target.liked = !target.liked;

                        target.likes += target.liked ? 1 : -1;

                        if (savePosts(currentPosts)) renderPosts();

                    });


             
                    const deleteButton = document.createElement('button');

                    deleteButton.type = 'button';

                    deleteButton.className = 'delete-post';

                    deleteButton.textContent = 'Hapus';

                    deleteButton.addEventListener('click', function () {

                        if (!confirm('Yakin ingin menghapus postingan ini?')) {
                            return;
                        }

                        const remainingPosts = getPosts().filter(
                            item => item.id !== post.id
                        );

                        if (savePosts(remainingPosts)) renderPosts();

                    });


                    const time = document.createElement('span');

                    time.className = 'm';

                    time.textContent = formatTime(post.createdAt);


                    actions.append(likeButton, time, deleteButton);

                    body.appendChild(actions);

                    article.append(avatar, body);

                    postFeed.appendChild(article);

                });

            }


  
            const tabs = document.querySelectorAll('.profile-tabs .tab');

            const panels = document.querySelectorAll('.profile-panel');

            tabs.forEach(function (tab) {

                tab.addEventListener('click', function () {

                    const target = this.dataset.tab;

                    tabs.forEach(item => item.classList.remove('on'));

                    this.classList.add('on');

                    panels.forEach(panel => {

                        panel.hidden =
                            panel.id !== 'panel-' + target;

                    });

                });

            });


            
            renderPosts();

        });

    </script>

@endsection
