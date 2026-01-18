<div>
    <style>
        @media print {
            .mks-print {
                visibility: visible;
            }
        }
    </style>
    <div class="mks-print  md:w-4/5 xl:w-3/5 mx-auto md:mx-auto my-16 bg-white px-10 py-2 rounded-3xl">
        <form class="my-10">
            {{ $this->form }}
        </form>

        <x-filament-actions::modals />
    </div>
</div>

