@php
use Illuminate\Support\Facades\Storage
@endphp

@auth()

    @if(auth()->user()->team->avatar_path)
        <img src="{{ Storage::disk(auth()->user()->team->avatar_disk)->url(auth()->user()->team->avatar_path)}}" />
    @endif
@endauth
