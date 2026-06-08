/**
 * WordPress media library picker for Rigpa Map location image fields.
 *
 * Each field is a `.rigpa-de-map-image-field` wrapper with a hidden state input,
 * URL text input, Select/Remove buttons, and a preview image.
 */
(function ($) {
  "use strict";

  function setFieldState($field, state) {
    $field.find(".rigpa-de-map-image-state").val(state);
  }

  function setPreview($field, url) {
    var $preview = $field.find(".rigpa-de-map-image-preview");
    if (url) {
      $preview.attr("src", url).css("display", "block");
    } else {
      $preview.attr("src", "").css("display", "none");
    }
  }

  function updateRemoveButton($field, state) {
    var $remove = $field.find(".rigpa-de-map-image-remove");
    $remove.css("display", state === "custom" || state === "default" ? "inline-block" : "none");
  }

  $(document).on("click", ".rigpa-de-map-image-select", function (event) {
    event.preventDefault();

    var $button = $(this);
    var $field = $button.closest(".rigpa-de-map-image-field");
    var $input = $field.find(".rigpa-de-map-image-input");

    var frame = wp.media({
      title: $button.data("title") || "Select or upload image",
      button: { text: $button.data("button") || "Use this image" },
      library: { type: "image" },
      multiple: false,
    });

    frame.on("select", function () {
      var attachment = frame.state().get("selection").first().toJSON();
      var url = attachment.url || "";

      $input.val(url);
      setFieldState($field, "custom");
      setPreview($field, url);
      updateRemoveButton($field, "custom");
    });

    frame.open();
  });

  $(document).on("click", ".rigpa-de-map-image-remove", function (event) {
    event.preventDefault();

    var $field = $(this).closest(".rigpa-de-map-image-field");
    $field.find(".rigpa-de-map-image-input").val("");
    setFieldState($field, "none");
    setPreview($field, "");
    updateRemoveButton($field, "none");
  });

  $(document).on("change input", ".rigpa-de-map-image-input", function () {
    var $field = $(this).closest(".rigpa-de-map-image-field");
    var url = $(this).val().trim();

    if (url) {
      setFieldState($field, "custom");
      setPreview($field, url);
      updateRemoveButton($field, "custom");
    }
  });
})(jQuery);
