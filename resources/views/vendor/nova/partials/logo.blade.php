@auth()
    <h3>{{ request()->user()->team->name  }}</h3>
@endauth
