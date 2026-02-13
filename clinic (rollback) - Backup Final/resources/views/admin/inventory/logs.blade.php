@extends('layouts.admin')

@section('content')
<div class="p-4">
    <h2 class="text-xl font-semibold mb-4">Inventory Logs</h2>

    <table class="w-full text-sm border">
        <thead class="bg-gray-100">
            <tr>
                <th class="border p-2">Date</th>
                <th class="border p-2">Item</th>
                <th class="border p-2">Action</th>
                <th class="border p-2">Qty</th>
                <th class="border p-2">Unit</th>
                <th class="border p-2">By</th>
                <th class="border p-2">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td class="border p-2">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                <td class="border p-2">{{ $log->inventory->name ?? '—' }}</td>
                <td class="border p-2 capitalize">{{ $log->action }}</td>
                <td class="border p-2">{{ $log->quantity }}</td>
                <td class="border p-2">{{ $log->unit }}</td>
                <td class="border p-2">{{ $log->user->name ?? 'System' }}</td>
                <td class="border p-2">{{ $log->remarks }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
@endsection
