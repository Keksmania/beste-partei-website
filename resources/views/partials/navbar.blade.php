<nav class="navbar navbar-expand-lg navbar-light bg-primary">
    <a style="margin-left:1em" class="navbar-brand" href="/">Studentische Politik-Revolution</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavDropdown">
        <ul class="navbar-nav me-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Inhalte
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                    <li><a class="dropdown-item" href="/Partei-Programm">Parteiprogramm</a></li>
                </ul>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Über uns
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                    <li><a class="dropdown-item" href="{{ url('/Geschichte') }}">Geschichte</a></li>
                    <li><a class="dropdown-item" href="{{ url('/Kontakt') }}">Kontakt</a></li>
                    <li><a class="dropdown-item" href="{{ url('/Impressum') }}">Impressum</a></li>
                </ul>
            </li>

            @if(Auth::check() && Auth::user()->hasPermission('verwalter'))
                <!-- Verwaltung Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="verwaltungDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Verwaltung
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="verwaltungDropdown">
                        <li>
                            <a class="dropdown-item {{ Auth::user()->hasPermission('posten') ? '' : 'disabled' }}" href="{{ url('/createpost') }}">
                                Event erstellen
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ Auth::user()->hasPermission('posten') ? '' : 'disabled' }}" href="{{ url('/list-events') }}">
                                Events verwalten
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ Auth::user()->hasPermission('admin') ? '' : 'disabled' }}" href="{{ url('/manage-permissions') }}">
                                Benutzerrechte
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ Auth::user()->hasPermission('anwesenheit') ? '' : 'disabled' }}" href="{{ url('/anwesenheit') }}">
                                Anwesenheit verwalten
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ Auth::user()->hasPermission('admin') ? '' : 'disabled' }}" href="{{ url('/registration-list') }}">
                                Registrierungen genehmigen
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
        </ul>
        <ul class="navbar-nav ms-auto">
            @if(Auth::check())
                <li class="nav-item d-flex align-items-center">
                    <span class="nav-link">{{ Auth::user()->firstname }}</span>
                    <div style="margin-right:1em">
                      <button id="logoutButton" class="btn btn-danger btn-sm ms-2">Logout</button>
                    </div>
                </li>
            @else
                <li class="nav-item">
                    <a href="/login" class="nav-link">Login</a>
                </li>
                <li class="nav-item">
                    <a href="/register" class="nav-link">Registrieren</a>
                </li>
            @endif
        </ul>
    </div>
</nav>

<script>
    // Listen for the logout button click event
    document.getElementById('logoutButton')?.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent the default form submission

        // Perform AJAX request to logout
        fetch('/logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({}) // Empty body for the logout request
        })
        .then(response => response.json())
        .then(data => {
            if (data.message === 'Logout successful') {
                window.location.href = '/'; // Redirect to /start after successful logout
            } else {
                console.error('Logout failed:', data.message);
            }
        })
        .catch(error => {
            console.error('Error during logout:', error);
        });
    });
</script>
