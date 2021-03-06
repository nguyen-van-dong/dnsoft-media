@if ($type == 'image')
@php($gallery = object_get($item, $name))
<?php
if ($gallery && is_string($gallery)) {
    $gallery = explode(',', $gallery);
}
?>

@if ($gallery && count($gallery) > 0)
@foreach($gallery as $image)
<div class="holder" style="margin-top:15px;max-height:100px; margin-bottom: 10px">
    <span class="close" data-image="{{ env('APP_URL'). '/'. $image }}">&times;</span>
    <img src="{{ env('APP_URL'). '/'. $image }}" style="height: 5rem;" class="mr-2">
</div>
@endforeach
@else
<div class="holder" style="margin-top:15px;max-height:100px; margin-bottom: 10px"></div>
@endif
@endif
<div class="input-group">
    @if (isset($label))
    <label for="{{ $name }}" class="col-12 font-weight-600" style="margin-left: -12px">{{ $label }}</label>
    @endif
    <span class="input-group-btn">
        <a data-input="thumbnail" data-preview="holder" class="btn btn-primary lfm">
            <i class="fa fa-picture-o"></i> {{ __('media::media.choose')}}
        </a>
    </span>
    <input id="thumbnail" placeholder="{{ $placeholder ?? $label }}" class="form-control" type="text" value="{{ is_string($gallery) ? env('APP_URL'). '/'.$gallery : ($gallery ? env('APP_URL'). '/'. implode(',', $gallery) : '') }}" name="{{ $name }}">
</div>

@push('scripts')
<script src="{{ asset('vendor/dnsoft/admin/js/scripts/stand-alone-button.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.lfm').filemanager('{{ $type }}');

        $('body').on('click', '.holder .close', function(e) {
            let images = $('#thumbnail').val();
            let arrImage = images.split(',');
            let srcImage = $(this).data('image');
            const index = arrImage.indexOf(srcImage.toString());
            if (index > -1) {
                arrImage.splice(index, 1);
                $('#thumbnail').val(arrImage.join(','));
            }
            let imgWrap = this.parentElement;
            if (imgWrap.parentElement) {
                imgWrap.parentElement.removeChild(imgWrap);
            }
        });
    });
</script>
@endpush

@push('styles')
<style>
    .holder {
        position: relative;
        display: inline-block;
        font-size: 0;
    }

    .holder .close {
        position: absolute;
        top: -10px;
        right: 2px;
        z-index: 100;
        background-color: #FFF;
        padding: 5px 2px 2px;
        color: #000;
        font-weight: bold;
        cursor: pointer;
        opacity: .2;
        text-align: center;
        font-size: 22px;
        line-height: 10px;
        border-radius: 50%;
    }

    .holder .close {
        opacity: 1;
    }
</style>
@endpush