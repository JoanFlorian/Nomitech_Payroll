@props(['title' => '', 'value' => '', 'subtitle' => '', 'icon' => 'bi-card-text', 'trend' => null, 'trendUp' => true])

<div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col gap-4">
    <div class="flex justify-between items-start">
        <div class="flex flex-col">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $title }}</span>
            <span class="text-2xl font-bold text-gray-800">{{ $value }}</span>
        </div>
        <div class="bg-gray-50 p-3 rounded-xl text-gray-400">
            <i class="bi {{ $icon }} text-xl"></i>
        </div>
    </div>

    @if($trend !== null || $subtitle)
        <div class="flex items-center gap-2">
            @if($trend !== null)
                <div class="flex items-center {{ $trendUp ? 'text-green-500' : 'text-red-500' }} text-sm font-bold">
                    <i class="bi {{ $trendUp ? 'bi-arrow-up' : 'bi-arrow-down' }} mr-1"></i>
                    {{ $trend }}
                </div>
            @endif
            <span class="text-xs text-gray-400">{{ $subtitle }}</span>
        </div>
    @endif

    {{ $slot }}
</div>