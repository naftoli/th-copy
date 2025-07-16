<?php /* Images Modal Include for Screens */ ?>
<div class="modal fade" id="imagesModal" tabindex="-1" aria-labelledby="imagesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="imagesModalLabel"><i class="fas fa-images me-2"></i>Manage Images</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="uploadForm" enctype="multipart/form-data" class="mb-4">
            <input type="hidden" id="imagesScreenId" name="screen_id" value="">
            <div class="mb-3">
                <label for="imageFiles" class="form-label fw-bold">Upload Images</label>
                <input class="form-control" type="file" id="imageFiles" name="images[]" accept="image/*" multiple>
            </div>
            <button type="submit" class="btn btn-success"><i class="fas fa-upload me-1"></i>Upload</button>
        </form>
        <hr>
        <div id="imagesGrid" class="row g-3">
            <!-- Images will be loaded here -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="saveAllBtn"><i class="fas fa-save me-1"></i>Save All Changes</button>
      </div>
    </div>
  </div>
</div>
<script>
let currentScreenId = null;

function openImagesModal(screenId) {
    currentScreenId = screenId;
    document.getElementById('imagesScreenId').value = screenId;
    $('#imagesModal').modal('show');
    loadImages();
}

const sizeOptions = [100, 150, 200, 250, 300, 'custom'];

function renderImageCard(img) {
    const sizeVal = img.size || 150;
    const isCustom = !sizeOptions.includes(Number(sizeVal));
    return `
    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="image-thumb" data-id="${img.id}">
            <img src="${img.url}" style="width:${sizeVal}px;height:${sizeVal}px;object-fit:cover;" alt="Image">
            <div class="form-check form-switch">
                <input class="form-check-input visible-toggle" type="checkbox" id="visible_${img.id}" ${img.visible ? 'checked' : ''}>
                <label class="form-check-label visible-label" for="visible_${img.id}">Show on Screen</label>
            </div>
            <div class="image-actions">
                <label class="size-label">Size:</label>
                <select class="form-select size-select d-inline-block" data-img-id="${img.id}">
                    ${sizeOptions.map(opt => `
                        <option value="${opt}" ${opt==sizeVal|| (opt==='custom'&&isCustom)?'selected':''}>${opt==='custom'?'Custom':opt+'x'+opt}</option>
                    `).join('')}
                </select>
                <div class="custom-size-group" style="${isCustom?'':'display:none;'}">
                    <input type="number" min="50" max="1000" class="form-control custom-size-input" value="${isCustom?sizeVal:''}" placeholder="px">
                    <span>px</span>
                </div>
                <span class="delete-btn ms-2" title="Delete"><i class="fas fa-trash"></i></span>
            </div>
        </div>
    </div>
    `;
}

function loadImages() {
    if (!currentScreenId) return;
    $('#imagesGrid').html('<div class="text-center py-5 w-100"><div class="spinner-border text-primary"></div></div>');
    $.get('images_list.php', {screen_id: currentScreenId}, function(data) {
        let images = [];
        try { images = JSON.parse(data); } catch(e) {}
        if (!images.length) {
            $('#imagesGrid').html('<div class="text-center text-muted py-5 w-100">No images found.</div>');
            return;
        }
        $('#imagesGrid').html(images.map(renderImageCard).join(''));
    });
}

$('#uploadForm').on('submit', function(e) {
    e.preventDefault();
    if (!currentScreenId) return;
    const formData = new FormData(this);
    formData.append('screen_id', currentScreenId);
    $.ajax({
        url: 'images_upload.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function() {
            loadImages();
            $('#imageFiles').val('');
        }
    });
});

$('#imagesModal').on('show.bs.modal', loadImages);

$('#imagesGrid').on('change', '.visible-toggle', function() {
    const id = $(this).closest('.image-thumb').data('id');
    const visible = $(this).is(':checked') ? 1 : 0;
    $.post('images_update.php', {id, visible, screen_id: currentScreenId});
});

$('#imagesGrid').on('change', '.size-select', function() {
    const id = $(this).data('img-id');
    const val = $(this).val();
    const thumb = $(this).closest('.image-thumb');
    if (val === 'custom') {
        thumb.find('.custom-size-group').show();
    } else {
        thumb.find('.custom-size-group').hide();
        $.post('images_update.php', {id, size: val, screen_id: currentScreenId}, loadImages);
    }
});

$('#imagesGrid').on('input', '.custom-size-input', function() {
    const id = $(this).closest('.image-thumb').data('id');
    const val = $(this).val();
    if (val && val >= 50 && val <= 1000) {
        $.post('images_update.php', {id, size: val, screen_id: currentScreenId}, loadImages);
    }
});

$('#imagesGrid').on('click', '.delete-btn', function() {
    if (!confirm('Delete this image?')) return;
    const id = $(this).closest('.image-thumb').data('id');
    $.post('images_delete.php', {id, screen_id: currentScreenId}, loadImages);
});

$('#saveAllBtn').on('click', function() {
    loadImages();
    alert('All changes saved!');
});
</script> 