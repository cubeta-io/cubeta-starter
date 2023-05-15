@if($errors->any())
    <div class="card p-1">
        <div class="card-body">
            <ul>
                @foreach($errors->all() as $error)
                    <li style="color: red">
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
