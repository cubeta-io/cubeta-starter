@props(['action', 'method' => 'POST', 'validationErrorsFromHere' => false])
@if ($validationErrorsFromHere)
    @if ($errors->any())
        <div class="card p-1">
            <div class="card-body">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li style="color: red">
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
@endif
<!--end of validation errors-->
<form id="form" action="{{ $action }}" method="POST" enctype="multipart/form-data">
    @csrf

    @if ($method == 'PUT' || $method == 'put')
        @method('PUT')
    @endif
    {{ $slot }}
    <div class="text-center my-2">
        <button id="submit-btn" type="submit" class="btn btn-primary btn-lg btn-block">
            Submit
        </button>
    </div>
</form>
