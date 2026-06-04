/**
 * WordPress media library picker for Rigpa Mega Menu image fields.
 *
 * Each image field is a `.rigpa-mega-menu-image-field` wrapper containing a text
 * input (`.rigpa-mm-image-input`), Select/Remove buttons, and a preview image.
 * The text input still accepts a pasted URL; the picker is an additive convenience.
 */
(function ($) {
  "use strict";

  $(document).on("click", ".rigpa-mm-image-select", function (event) {
    event.preventDefault();

    var $button = $(this);
    var $field = $button.closest(".rigpa-mega-menu-image-field");
    var $input = $field.find(".rigpa-mm-image-input");
    var $preview = $field.find(".rigpa-mm-image-preview");
    var $remove = $field.find(".rigpa-mm-image-remove");

    var frame = wp.media({
      title: $button.data("title") || "Select or upload image",
      button: { text: $button.data("button") || "Use this image" },
      library: { type: "image" },
      multiple: false,
    });

    frame.on("select", function () {
      var attachment = frame.state().get("selection").first().toJSON();
      var url = attachment.url || "";

      $input.val(url).trigger("change");
      $preview.attr("src", url).css("display", url ? "block" : "none");
      $remove.css("display", url ? "inline-block" : "none");
    });

    frame.open();
  });

  $(document).on("click", ".rigpa-mm-image-remove", function (event) {
    event.preventDefault();

    var $field = $(this).closest(".rigpa-mega-menu-image-field");
    $field.find(".rigpa-mm-image-input").val("").trigger("change");
    $field.find(".rigpa-mm-image-preview").attr("src", "").css("display", "none");
    $(this).css("display", "none");
  });
})(jQuery);
