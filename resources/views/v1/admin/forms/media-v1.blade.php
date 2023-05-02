@php($value = $item && method_exists($item, 'getImageData') ? $item->getImageData() : null)
<div
  id="{{ $id }}"
  data-show="{{ isset($show_button) && $show_button }}"
  data-thumbnail="{{ $value ? json_encode($value) : null }}"
>
</div>

<input type="hidden" name="{{ $name }}" id="image-{{ $id }}" value="{{ $value ? json_encode([$value['id']]) : null }}"/>

@push('styles')
  <link rel="stylesheet" type="text/css" href="{{ asset('vendor/build/assets/app.css') }}">
@endpush

@push('scripts')
  <script src="{{ asset('vendor/build/assets/app.js') }}"></script>
@endpush
