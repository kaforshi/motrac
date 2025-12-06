@extends('layouts.app')

@section('title', 'Calendar View')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Calendar View</h1>
            <div class="flex gap-2">
                <a href="{{ route('reports.calendar', ['year' => $year, 'month' => $month - 1]) }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded">Previous</a>
                <a href="{{ route('reports.calendar', ['year' => $year, 'month' => $month + 1]) }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 rounded">Next</a>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">{{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</h2>
            <div class="grid grid-cols-7 gap-2">
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                    <div class="text-center font-semibold text-gray-700 dark:text-gray-300 py-2">{{ $day }}</div>
                @endforeach
                @php
                    $firstDay = date('w', mktime(0, 0, 0, $month, 1, $year));
                    $daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
                @endphp
                @for($i = 0; $i < $firstDay; $i++)
                    <div class="p-2"></div>
                @endfor
                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                        $dayTransactions = $transactions->get($dateStr, collect());
                    @endphp
                    <div class="p-2 border border-gray-200 dark:border-gray-700 rounded min-h-24">
                        <div class="font-semibold text-gray-900 dark:text-white mb-1">{{ $day }}</div>
                        @if($dayTransactions->count() > 0)
                            <div class="space-y-1">
                                @foreach($dayTransactions->take(3) as $transaction)
                                    <div class="text-xs {{ $transaction->type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $transaction->description }}
                                    </div>
                                @endforeach
                                @if($dayTransactions->count() > 3)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">+{{ $dayTransactions->count() - 3 }} more</div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>
@endsection








