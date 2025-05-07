// JQuery
import $ from 'jquery';

window.$ = $;
window.jQuery = $;

//data tables
import 'datatables.net-bs5';
import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-buttons-bs5';
import 'datatables.net-buttons/js/buttons.colVis.mjs';
import 'datatables.net-fixedcolumns-bs5';
import 'datatables.net-fixedheader-bs5';

//Bootstrap
import 'bootstrap';
import {Modal} from 'bootstrap';

window.Modal = Modal;

// select2
import select2 from 'select2/dist/js/select2.full';

window.select2 = select2();

// baguette box
import baguetteBox from 'baguettebox.js';

window.baguetteBox = baguetteBox;

// sweet alert
import swal from 'sweetalert2';

window.Swal = swal;

// importing tinymce
import tinymce from "tinymce";
import 'tinymce/themes/silver/theme.js';
import 'tinymce/models/dom/model.js';
import 'tinymce/icons/default/icons.js';

window.tinymce = tinymce;

(function initDataTable() {
    $.extend($?.fn?.dataTable?.defaults, {
        pagingType: "simple_numbers",
        processing: true,
        serverSide: true,
        scrollX: true,
        autoWidth: false,
        dom: "Blfrtip",
        buttons: [],
        lengthMenu: [
            [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]
        ]
    });

    $(document).on("init.dt", function (e, settings) {
        initPluginsByClass();

        const api = new $.fn.dataTable.Api(settings);

        api.table().on("click", ".remove-item-from-table-btn", function () {
            let $this = $(this);
            let url = $this.data("deleteurl");
            handelDeletionOfItemFromDataTable($this, _CSRF_TOKEN, {
                deleteMessage: "Do You Want To Delete This Item",
                successMessage: "Deleted Successfully",
                confirmMessage: "Yes",
                denyMessage: "No"
            });
        });
    });
})();

