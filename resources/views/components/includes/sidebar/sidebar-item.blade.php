@props(['name' , 'route'])

<ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-item">
        <a class="nav-link collapsed" href="{{$route}}">
            <i class="bi bi-circle"></i>
            <span>{{$name}}</span>
        </a>
    </li>

</ul>
