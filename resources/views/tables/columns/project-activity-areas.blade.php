<div>
    @if(! empty($getState()))
        @foreach($getState() as $variant)
            {{$variant->activityArea->name[app()->getLocale()] ?? $variant->activityArea->name['ckb']}}
            @if(! $loop->last)
                <b style="color:#2563eb">| </b>
            @endif
        @endforeach
    @endif
</div>
