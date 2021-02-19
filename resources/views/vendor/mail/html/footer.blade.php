<tr>
    <td>
        <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td class="content-cell" align="center">
                    @isset($team)
                        {{
                            join(',', array_filter([
                                $team->primary_phone,
                                $team->secondary_phone,
                                $team->email,
                            ]))
                        }} <br>
                        {{ join(', ',[$team->address_line_1, $team->address_line_2]) }}
                    @endisset
                    {{ Illuminate\Mail\Markdown::parse($slot) }}
                </td>
            </tr>
        </table>
    </td>
</tr>
