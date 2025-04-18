<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4 p-4 bg-gray-50 max-h-[500px] overflow-y-auto">
    @foreach ($barcodes as $barcode)
        <div class="bg-white rounded shadow p-2 flex flex-col items-center text-center text-xs">
            <div class="mb-1 leading-none scale-75">{!! $barcode['content'] !!}</div>
            <span class="text-gray-700 font-medium truncate w-full">{{ $barcode['label'] }}</span>
        </div>
    @endforeach
</div>
@script
<script>
    $wire.on('post-created', () => {
        window.open('facebook.com', '_blank');
    });
</script>
@endscript


