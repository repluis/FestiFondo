@props([
    'headers' => [],
])

<div style="overflow-x: auto; border-radius: var(--radius-lg); border: 1px solid var(--color-border);">
    <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">

        <thead>
            <tr style="background: var(--color-bg-elevated);">
                @foreach ($headers as $header)
                    <th style="
                        padding: 0.75rem 1rem;
                        text-align: left;
                        font-weight: 600;
                        color: var(--color-text-muted);
                        font-size: 0.75rem;
                        text-transform: uppercase;
                        letter-spacing: 0.05em;
                        border-bottom: 1px solid var(--color-border);
                        white-space: nowrap;
                    ">
                        {{ $header }}
                    </th>
                @endforeach
            </tr>
        </thead>

        <tbody style="background: var(--color-bg-surface);">
            {{ $slot }}
        </tbody>

    </table>
</div>

{{--
    Usage:
    <x-ui.table :headers="['Name', 'Status', 'Amount', 'Date']">
        <tr style="border-bottom: 1px solid var(--color-border);">
            <td style="padding: 0.875rem 1rem; color: var(--color-text-primary);">John Doe</td>
            ...
        </tr>
    </x-ui.table>
--}}
