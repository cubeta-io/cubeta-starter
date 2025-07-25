/**
 * this function add a red * char to the label of the input field if it has required attribute
 */
function markRequiredFields() {
  let inputs = document.querySelectorAll("input, textarea, select");
  for (let i = 0; i < inputs.length; i++) {
    let input = inputs[i];
    if (input.hasAttribute("required")) {
      let label = document.querySelector('label[for="' + input.id + '"]');
      if (label) {
        label.innerHTML += "<span style='color: red;'>*</span>";
      }
    }
  }
}

/**
 * this function is to disable the submit button until all required fields is filled
 */
function disableSubmitUntilFillRequiredFields() {
  const form = document.getElementById("form");

  if (!form) {
    return;
  }

  const submitBtn = document.getElementById("submit-btn");
  const requiredFields = form.querySelectorAll("[required]");
  let allFilled = true;
  for (let i = 0; i < requiredFields.length; i++) {
    if (
      requiredFields[i].tagName === "INPUT" &&
      requiredFields[i].getAttribute("type") === "radio"
    ) {
      continue;
    }
    if (
      requiredFields[i].value === undefined ||
      requiredFields[i].value === "" ||
      requiredFields[i].value == null
    ) {
      allFilled = false;
      break;
    }
  }
  submitBtn.disabled = !allFilled;

  form.addEventListener("input", disableSubmitUntilFillRequiredFields);
}

/**
 * this function to handle the delete request for the image
 * you should a data-deleteurl attribute to your button tag
 * @param buttonClass
 */
function handleImageDeleteButton(buttonClass) {
  $(buttonClass).on("click", function (e) {
    e.preventDefault();
    let $this = $(this);
    let url = $this.data("deleteurl");
    if (url === "#") {
      return;
    }
    $.ajaxSetup({
      headers: {
        "X-CSRF-TOKEN": _CSRF_TOKEN,
      },
    });
    $.ajax({
      method: "DELETE",
      url: url,
      success: function () {
        $this.parent().remove();
      },
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
function handleAddImageButton(
  id,
  imagePreviewId,
  imageThumbId,
  removeButtonId,
) {
  let imageUpload = $(id);
  let imagePreview = $(imagePreviewId);
  let imageThumb = $(imageThumbId);
  let removeButtonForTemp = $(removeButtonId);

  imageUpload.on("change", function (e) {
    let file = e.target.files[0];
    if (file) {
      let reader = new FileReader();
      reader.onload = function (e) {
        imagePreview.attr("src", e.target.result);
        imagePreview.show();
        imageThumb.attr("href", e.target.result);
        imageThumb.parent().removeAttr("hidden");
        removeButtonForTemp.removeAttr("hidden");
        removeButtonForTemp.on("click", function () {
          e.preventDefault();
          let $this = $(this);
          $this.parent().attr("hidden", true);
          imageUpload.val("");
        });
      };
      reader.readAsDataURL(file);
    }
  });
}

function handleImageGalleryExistence() {
  let gallery = $(".gallery");

  if (gallery !== "undefined") {
    handleImageDeleteButton(".remove-image-btn");

    let addButton = gallery.children().last().children().last();

    if (addButton !== "undefined" && addButton.attr("id") === "imageUpload") {
      handleAddImageButton(
        "#imageUpload",
        "#imagePreview",
        "#imageThumb",
        "#remove-button-for-temp",
      );
    }
  }
}

function triggerSwalError(message) {
  Swal.fire({
    title: "Error!",
    text: message,
    icon: "error",
    confirmButtonText: "Ok",
    confirmButtonColor: "#0d6efd",
  });
}

function triggerSwalSuccess(message) {
  Swal.fire({
    title: "Success!",
    text: message,
    icon: "success",
    confirmButtonText: "Ok",
    confirmButtonColor: "#0d6efd",
  });
}

function triggerSwalMessage(message) {
  Swal.fire({
    title: "Info !",
    text: message,
    icon: "info",
    confirmButtonText: "Ok",
    confirmButtonColor: "#0d6efd",
  });
}

function changeLocale() {
  let children = $("#lang-changer").children();

  for (let i = 0; i < children.length; i++) {
    $(children[i]).on("click", function (e) {
      e.preventDefault();
      $.ajaxSetup({
        headers: {
          "X-CSRF-TOKEN": _CSRF_TOKEN,
        },
      });
      $.ajax({
        method: "POST",
        data: {
          lang: $(this).data("lang"),
        },
        url: $(this).data("route"),
        success: function () {
          location.reload();
        },
        error: function (error) {
          console.error("Error:", error);
        },
      });
    });
  }
}

function downloadResponseFile(response, xhr) {
  const blob = new Blob([response], {
    type: xhr.getResponseHeader("Content-Type"),
  });

  const blobUrl = URL.createObjectURL(blob);

  const a = document.createElement("a");
  a.href = blobUrl;

  const disposition = xhr.getResponseHeader("Content-Disposition");
  const fileNameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
  const matches = fileNameRegex.exec(disposition);
  a.download =
    matches != null && matches[1]
      ? matches[1].replace(/['"]/g, "")
      : "download";
  document.body.appendChild(a);
  a.click();

  document.body.removeChild(a);
  URL.revokeObjectURL(blobUrl);
}

/**
 * @param $item
 * @param token
 * @param messages
 */
function handelDeletionOfItemFromDataTable($item, token, messages) {
  let url = $item.data("deleteurl");
  Swal.fire({
    title: messages.deleteMessage,
    showDenyButton: true,
    confirmButtonText: messages.confirmMessage,
    confirmButtonColor: "#0d6efd",
    denyButtonText: messages.denyMessage,
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajaxSetup({
        headers: {
          "X-CSRF-TOKEN": token,
        },
      });
      $.ajax({
        method: "DELETE",
        url: url,
        success: function () {
          $item.parent().parent().parent().parent().remove();
        },
      });
      Swal.fire(messages.successMessage + " !", "", "success");
    }
  });
}

function translate(data, locale = undefined) {
  locale = locale ? locale : (localStorage.getItem("locale") ?? "en");

  return Object.values(data).pop()[locale];
}

/**
 * @returns {[{extend: string, className: string, init: *},{text: string, action: *, className: string, init: *}]}
 * @param {string} createPageRoute
 */
function dataTableButtons(createPageRoute) {
  return [
    {
      extend: "csvHtml5",
      className: "btn btn-primary mt-2 mb-2",
      init: function (api, node, config) {
        $(node).removeClass("btn-secondary");
      },
    },
    {
      text: '<i class="bi bi-file-earmark-plus"></i>',
      action: function (e, dt, node, config) {
        window.location.href = createPageRoute;
      },
      className: "btn-primary mt-2 mb-2",
      init: function (api, node, config) {
        $(node).removeClass("btn-secondary");
      },
    },
  ];
}

/**
 *
 * @param {string} url
 * @param {string} locale
 * @returns {{url, headers: {Accept: string, "Content-Type": string, "Accept-language": string}}}
 */
function dataTableAjax(url, locale) {
  return {
    url: url,
    headers: {
      Accept: "application/html",
      "Content-Type": "application/html",
      "Accept-language": locale,
    },
  };
}
