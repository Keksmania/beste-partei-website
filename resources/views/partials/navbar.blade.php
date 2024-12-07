<nav class="navbar navbar-expand-lg navbar-light bg-primary">
    <a class="navbar-brand" href="/">Studentische Politik-Revolution</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavDropdown">
        <ul class="navbar-nav">
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
                    Orga
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                    <li><a class="dropdown-item" href="{{ url('/Termine?zukunft=true') }}">zukünftige Termine</a></li>
                    <li><a class="dropdown-item" href="{{ url('/Termine?zukunft=false') }}">vergangene Termine</a></li>
              
                </ul> </li>
                <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Über uns
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                    <li><a class="dropdown-item" href="{{ url('/Geschichte') }}">Geschichte</a></li>
                    <li><a class="dropdown-item" href="{{ url('/Kontakt') }}">Kontakt</a></li>
                    <li><a class="dropdown-item" href="{{ url('/Impressum') }}">Impressum</a></li>
                </ul> </li>
    	   <a href="/Login" class="nav-link"> Login </a> 
            <!-- Add more items here -->
        </ul>
    </div>
</nav>
