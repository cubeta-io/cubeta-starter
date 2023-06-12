/**
 * this function add a red * char to the label of the input field if it has required attribute
 */
function markRequiredFields() {
    let inputs = document.querySelectorAll('input, textarea, select');
    for (let i = 0; i < inputs.length; i++) {
        let input = inputs[i];
        if (input.hasAttribute('required')) {
            let label = document.querySelector('label[for="' + input.id + '"]');
            label.innerHTML += "<span style='color: red;'>*</span>";
        }
    }
}

/**
 * this function is to disable the submit button until all required fields is filled
 */
function disableSubmitUntilFillRequiredFields() {
    const form = document.getElementById('form');

    if (!form) {
        return;
    }

    const submitBtn = document.getElementById('submit-btn');
    const requiredFields = form.querySelectorAll('[required]');
    let allFilled = true;
    for (let i = 0; i < requiredFields.length; i++) {
        if (requiredFields[i].value === '') {
            allFilled = false;
            break;
        }
    }
    submitBtn.disabled = !allFilled

    form.addEventListener('input', disableSubmitUntilFillRequiredFields);
}

/**
 * this function to handle the delete request for the an image
 * you should a data-deleteurl attribute to your button tag
 * @param buttonClass
 */
function handleImageDeleteButton(buttonClass) {
    $(buttonClass).on('click', function (e) {
        e.preventDefault();
        let $this = $(this);
        let url = $this.data('deleteurl');
        if (url === "#") {
            return;
        }
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': _CSRF_TOKEN
            }
        });
        $.ajax({
            "method": "DELETE", "url": url, success: function () {
                $this.parent().remove();
            }
        });
    });
}

/**
 * this function to show the added photo to the gallery
 * @param id
 * @param imagePreviewId
 * @param imageThumbId
 * @param removeButtonId
 */
function handleAddImageButton(id, imagePreviewId, imageThumbId, removeButtonId) {
    let imageUpload = $(id);
    let imagePreview = $(imagePreviewId);
    let imageThumb = $(imageThumbId);
    let removeButtonForTemp = $(removeButtonId);

    imageUpload.on('change', function (e) {
            let file = e.target.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.attr('src', e.target.result);
                    imagePreview.show();
                    imageThumb.attr('href', e.target.result);
                    imageThumb.parent().removeAttr('hidden');
                    removeButtonForTemp.removeAttr('hidden');
                    removeButtonForTemp.on('click', function () {
                        e.preventDefault();
                        let $this = $(this);
                        $this.parent().attr('hidden', true);
                        imageUpload.val('');
                    });
                }
                reader.readAsDataURL(file);
            }
        }
    );
}

function handleImageGalleryExistence() {
    let gallery = $('.gallery');

    if (gallery !== 'undefined') {
        handleImageDeleteButton(".remove-image-btn");

        let addButton = gallery.children().last().children().last();

        if (addButton !== 'undefined' && addButton.attr('id') === 'imageUpload') {
            handleAddImageButton(
                "#imageUpload",
                '#imagePreview',
                '#imageThumb',
                '#remove-button-for-temp',
            );
        }
    }
}
