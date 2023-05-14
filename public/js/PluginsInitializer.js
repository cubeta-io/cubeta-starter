function initTrunbowyg(className) {
    $(className).trumbowyg({
        autogrow: true,
        btns: [
            ['viewHTML'],
            ['formatting'],
            ['strong', 'em', 'del'],
            ['superscript', 'subscript'],
            ['link'],
            ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
            ['unorderedList', 'orderedList'],
            ['horizontalRule'],
            ['removeformat'],
            ['fullscreen'],
            ['insertImage'],
        ]
    });
}

function initSelect2(className) {
    $(className).select2({
        theme: "bootstrap-5",
        width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
        placeholder: $(this).data('placeholder'),
    });
}

function initMultipleSelect2(className) {
    $(className).select2({
        theme: "bootstrap-5",
        width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
        placeholder: $(this).data('placeholder'),
        closeOnSelect: false,
        multiple: true,
    });
}

function initBaguetteBox(className) {
    baguetteBox.run(className);
}


function initPluginsByClass() {
    let elements = document.querySelectorAll('.select-2, .multiple-select-2, .Trunbowyg-text-editor, .gallery');

    let initSelect2State = true;
    let initMultipleSelect2State = true;
    let initTrunbowygState = true;
    let initBaguetteBoxState = true;

    for (let i = 0; i < elements.length; i++) {
        if (!initMultipleSelect2State && !initSelect2State && !initTrunbowygState && !initBaguetteBoxState) {
            break;
        }

        if (elements[i].classList.contains('select-2') && initSelect2State) {
            initSelect2('.select-2');
            initSelect2State = false;
        }

        if (elements[i].classList.contains('multiple-select-2') && initMultipleSelect2State) {
            initMultipleSelect2('.multiple-select-2');
            initMultipleSelect2State = false;
        }

        if (elements[i].classList.contains('Trunbowyg-text-editor') && initTrunbowygState) {
            initTrunbowyg('.Trunbowyg-text-editor');
            initTrunbowygState = false;
        }

        if (elements[i].classList.contains('gallery') && initBaguetteBoxState) {
            initBaguetteBox('.gallery');
            initBaguetteBoxState = false;
        }
    }
}
