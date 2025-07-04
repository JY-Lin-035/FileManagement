@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Meow')
<img src="{{ asset('J.png') }}" class="logo" alt="Meowgo">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
