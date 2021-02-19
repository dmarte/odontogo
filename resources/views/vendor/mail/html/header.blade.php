<tr>
    <td class="header">
        @isset($url)
            <a href="{{ $url }}">
                @endisset

                @isset($team)
                    @if($team->avatar_path)
                        <img
                            src="{{ Storage::disk($team->avatar_disk)->url($team->avatar_path) }}"
                            alt="{{ $team->name }}"
                            height="80"
                            class="mx-auto"
                        >
                    @else
                        {{ $team->name }}
                    @endif
                @else
                    {{ $slot }}
                @endisset

                @isset($url)
            </a>
        @endisset
    </td>
</tr>
