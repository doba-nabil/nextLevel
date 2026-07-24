<!-- Image Cropper Modal -->
<div class="modal fade" id="imageCropperModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('admin.crop_image') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="cropShape" id="cropSquare" value="square" checked>
                            <label class="btn btn-outline-primary" for="cropSquare">{{ __('admin.square') }}</label>

                            <input type="radio" class="btn-check" name="cropShape" id="cropCircle" value="circle">
                            <label class="btn btn-outline-primary" for="cropCircle">{{ __('admin.circle') }}</label>

                            <input type="radio" class="btn-check" name="cropShape" id="cropFree" value="free">
                            <label class="btn btn-outline-primary" for="cropFree">{{ __('admin.free') }}</label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div style="max-width: 100%; max-height: 500px; overflow: hidden;">
                            <img id="cropperImage" src="" alt="Crop Image" style="max-width: 100%; display: block;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('admin.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="cropImageBtn">{{ __('admin.crop') }}</button>
            </div>
        </div>
    </div>
</div>













