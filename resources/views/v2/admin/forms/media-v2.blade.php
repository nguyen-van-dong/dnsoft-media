<div
  id="{{ $id }}"
  data-show="{{ isset($show_button) && $show_button }}"
  data-thumbnail="{{ $item && method_exists($item, 'getImageData') ? json_encode($item->getImageData()) : null }}"
  data-isCollection="{{ isset($isCollection) && $isCollection }}"
>
</div>
<!-- $item ? $item->getImageData() : '' -->
<input type="hidden" id="session_id" value="{{ session()->get('session_id') }}"/>
