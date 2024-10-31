(function ($) {
  "use strict";
  const globalData = window.riothere_admin_build_frontend_global;

  $("#riothere_build_frontend_button").on("click", function () {
    $("#riothere_build_frontend_button").prop("disabled", true);

    $.ajax({
      type: "POST",
      url: globalData.ajax_url,
      data: {
        action: "build_frontend",
        ajax_nonce: globalData.build_frontend_nonce,
      },
      success: function (data) {
        const response = JSON.parse(data);
        $("#riothere_build_frontend_button").prop("disabled", false);
      },
      error: function (error) {
        console.log("errorThrown", error); // error
        $("#riothere_build_frontend_button").prop("disabled", false);
      },
    });
  });
})(jQuery);
