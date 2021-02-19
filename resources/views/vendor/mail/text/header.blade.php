@isset($url)
    [{{ $slot }}]({{ $url }})
@else
    {{ $slot }}
@endisset
