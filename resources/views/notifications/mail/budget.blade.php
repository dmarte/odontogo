@component('mail::message')
    {{-- Greeting --}}
    @if (! empty($greeting))
        # {{ $greeting }}
    @else
        @if ($level === 'error')
            # @lang('Whoops!')
        @else
            # @lang('Hello!')
        @endif
    @endif

    {{-- Intro Lines --}}
    @foreach ($introLines as $line)
        {{ $line }}

    @endforeach

    {{-- Action Button --}}
    @isset($actionText)
        <?php
        switch ($level) {
            case 'success':
            case 'error':
                $color = $level;
                break;
            default:
                $color = 'primary';
        }
        ?>
        @component('mail::button', ['url' => $actionUrl, 'color' => $color])
            {{ $actionText }}
        @endcomponent
    @endisset

    {{-- Outro Lines --}}
    @foreach ($outroLines as $line)
        {{ $line }}

    @endforeach

    {{-- Salutation --}}
    @if (! empty($salutation))
        {{ $salutation }}
    @else
        @lang('Regards'),
        <br>
        <img
            src="{{ Storage::disk($team->avatar_disk)->url($team->avatar_path) }}"
            alt="{{ $team->name }}"
            height="80"
        >
        {{ $team->name }}
        <hr>
        <h5>{{ $author->name }}</h5>
        <small>{{ $author->member->role->name }}</small>

        @if($team->primary_phone)
            <strong>@lang('Phone number')</strong>: {{ $team->primary_phone }}
        @endif
        @if($team->address_line_1 || $team->address_line_2)
            <strong>@lang('Address')</strong>: <br>
            {{ join(', ',[$team->address_line_1, $team->address_line_2]) }}
        @endif
        @if($team->email)
            {{ $team->email }}
        @endif
    @endif

    {{-- Subcopy --}}
    @isset($actionText)
        @slot('subcopy')
            @lang(
                "If you’re having trouble clicking the \":actionText\" button, copy and paste the URL below\n".
                'into your web browser:',
                [
                    'actionText' => $actionText,
                ]
            ) <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
        @endslot
    @endisset
@endcomponent
