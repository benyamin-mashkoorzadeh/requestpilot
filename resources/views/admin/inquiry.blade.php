<x-layouts.admin
    :title="'Inquiry #'.$inquiry->id"
    subtitle="Review the customer request, model analysis, and Laravel routing outcome."
>
    <livewire:admin.inquiry-detail :inquiry="$inquiry" />
</x-layouts.admin>
