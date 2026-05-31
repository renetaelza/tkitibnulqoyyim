<nav class="navbar">
    <a class="nav-logo" href="{{ route('landing.index') }}">
        <div class="logo-icon">🌱</div>
        <div>
            <div class="logo-text">TK Ibnul Qoyyim</div>
            <div class="logo-sub">Sulawesi • Islam Terpadu</div>
        </div>
    </a>
    
    <ul class="nav-links">
        <li><a href="#profil">Profil</a></li>
        <li><a href="#program">Program</a></li>
        <li><a href="#sarpras">Sarana</a></li>
        <li><a href="#guru">Guru</a></li>
        <li><a href="#galeri">Galeri</a></li>
        @guest
            <li class="nav-auth-buttons">
                <a href="{{ route('login') }}" class="nav-login">Login</a>
                <a href="{{ route('register') }}" class="nav-signup">Buat Akun</a>
            </li>
        @else
            <li class="nav-auth-buttons">
                <span class="nav-user-greeting">👤 {{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="nav-logout">Logout</button>
                </form>
            </li>
        @endauth
    </ul>
    
    <div class="hamburger" onclick="toggleMenu()">
        <span></span>
        <span></span>
        <span></span>
    </div>
</nav>
