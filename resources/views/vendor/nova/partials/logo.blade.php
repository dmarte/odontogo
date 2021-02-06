@php
use Illuminate\Support\Facades\Storage
@endphp

@auth()
    @php($team = auth()->user()->member->team)

    @if($team->avatar_path)
        <img src="{{ Storage::disk($team->avatar_disk)->url($team->avatar_path)}}" alt="">
    @endif
@endauth
