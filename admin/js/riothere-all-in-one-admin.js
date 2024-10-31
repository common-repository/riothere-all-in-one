(function ($) {
  "use strict";

  /**
   * All of the code for your admin-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */
  $(".profile-edit-woocommerce-info").insertBefore("#your-profile");

  function getEnhancedSelectFormatString() {
    return {
      language: {
        errorLoading: function () {
          // Workaround for https://github.com/select2/select2/issues/4355 instead of i18n_ajax_error.
          return wc_enhanced_select_params.i18n_searching;
        },
        inputTooLong: function (args) {
          var overChars = args.input.length - args.maximum;

          if (1 === overChars) {
            return wc_enhanced_select_params.i18n_input_too_long_1;
          }

          return wc_enhanced_select_params.i18n_input_too_long_n.replace(
            "%qty%",
            overChars
          );
        },
        inputTooShort: function (args) {
          var remainingChars = args.minimum - args.input.length;

          if (1 === remainingChars) {
            return wc_enhanced_select_params.i18n_input_too_short_1;
          }

          return wc_enhanced_select_params.i18n_input_too_short_n.replace(
            "%qty%",
            remainingChars
          );
        },
        loadingMore: function () {
          return wc_enhanced_select_params.i18n_load_more;
        },
        maximumSelected: function (args) {
          if (args.maximum === 1) {
            return wc_enhanced_select_params.i18n_selection_too_long_1;
          }

          return wc_enhanced_select_params.i18n_selection_too_long_n.replace(
            "%qty%",
            args.maximum
          );
        },
        noResults: function () {
          return wc_enhanced_select_params.i18n_no_matches;
        },
        searching: function () {
          return wc_enhanced_select_params.i18n_searching;
        },
      },
    };
  }

  // Ajax order search boxes for order id
  $(":input.wc-riothere-orders-search")
    .filter(":not(.enhanced)")
    .each(function () {
      var select2_args = {
        allowClear: $(this).data("allow_clear") ? true : false,
        placeholder: $(this).data("placeholder"),
        minimumInputLength: $(this).data("minimum_input_length")
          ? $(this).data("minimum_input_length")
          : "1",
        escapeMarkup: function (m) {
          return m;
        },
        ajax: {
          url: riothere_global.ajax_url,
          dataType: "json",
          delay: 1000,
          data: function (params) {
            return {
              term: params.term,
              action: "woocommerce_json_search_orders",
              security: riothere_global.search_orders_nonce,
              exclude: $(this).data("exclude"),
            };
          },
          processResults: function (data) {
            var terms = [];
            if (data) {
              $.each(data, function (id, text) {
                terms.push({
                  id: id,
                  text: text,
                });
              });
            }
            return {
              results: terms,
            };
          },
          cache: true,
        },
      };

      select2_args = $.extend(select2_args, getEnhancedSelectFormatString());

      $(this).selectWoo(select2_args).addClass("enhanced");

      if ($(this).data("sortable")) {
        var $select = $(this);
        var $list = $(this)
          .next(".select2-container")
          .find("ul.select2-selection__rendered");

        $list.sortable({
          placeholder: "ui-state-highlight select2-selection__choice",
          forcePlaceholderSize: true,
          items: "li:not(.select2-search__field)",
          tolerance: "pointer",
          stop: function () {
            $($list.find(".select2-selection__choice").get().reverse()).each(
              function () {
                var id = $(this).data("data").id;
                var option = $select.find('option[value="' + id + '"]')[0];
                $select.prepend(option);
              }
            );
          },
        });
      }
    });

  // Ajax order search boxes for sellers
  $(":input.wc-riothere-sellers-search")
    .filter(":not(.enhanced)")
    .each(function () {
      var select2_args = {
        allowClear: $(this).data("allow_clear") ? true : false,
        placeholder: $(this).data("placeholder"),
        minimumInputLength: $(this).data("minimum_input_length")
          ? $(this).data("minimum_input_length")
          : "1",
        escapeMarkup: function (m) {
          return m;
        },
        ajax: {
          url: riothere_global.ajax_url,
          dataType: "json",
          delay: 1000,
          data: function (params) {
            return {
              term: params.term,
              action: "woocommerce_json_search_sellers",
              security: riothere_global.search_sellers_nonce,
              exclude: $(this).data("exclude"),
            };
          },
          processResults: function (data) {
            var terms = [];
            if (data) {
              $.each(data, function (id, text) {
                terms.push({
                  id: id,
                  text: text,
                });
              });
            }
            return {
              results: terms,
            };
          },
          cache: true,
        },
      };

      select2_args = $.extend(select2_args, getEnhancedSelectFormatString());

      $(this).selectWoo(select2_args).addClass("enhanced");

      if ($(this).data("sortable")) {
        var $select = $(this);
        var $list = $(this)
          .next(".select2-container")
          .find("ul.select2-selection__rendered");

        $list.sortable({
          placeholder: "ui-state-highlight select2-selection__choice",
          forcePlaceholderSize: true,
          items: "li:not(.select2-search__field)",
          tolerance: "pointer",
          stop: function () {
            $($list.find(".select2-selection__choice").get().reverse()).each(
              function () {
                var id = $(this).data("data").id;
                var option = $select.find('option[value="' + id + '"]')[0];
                $select.prepend(option);
              }
            );
          },
        });
      }
    });

  // Ajax order search boxes for sellers
  $(":input.wc-riothere-brands-search")
    .filter(":not(.enhanced)")
    .each(function () {
      var select2_args = {
        allowClear: $(this).data("allow_clear") ? true : false,
        placeholder: $(this).data("placeholder"),
        minimumInputLength: $(this).data("minimum_input_length")
          ? $(this).data("minimum_input_length")
          : "1",
        escapeMarkup: function (m) {
          return m;
        },
        ajax: {
          url: riothere_global.ajax_url,
          dataType: "json",
          delay: 1000,
          data: function (params) {
            return {
              term: params.term,
              action: "woocommerce_json_search_brands",
              security: riothere_global.search_brands_nonce,
              exclude: $(this).data("exclude"),
            };
          },
          processResults: function (data) {
            var terms = [];
            if (data) {
              $.each(data, function (id, text) {
                terms.push({
                  id: id,
                  text: text,
                });
              });
            }
            return {
              results: terms,
            };
          },
          cache: true,
        },
      };

      select2_args = $.extend(select2_args, getEnhancedSelectFormatString());

      $(this).selectWoo(select2_args).addClass("enhanced");

      if ($(this).data("sortable")) {
        var $select = $(this);
        var $list = $(this)
          .next(".select2-container")
          .find("ul.select2-selection__rendered");

        $list.sortable({
          placeholder: "ui-state-highlight select2-selection__choice",
          forcePlaceholderSize: true,
          items: "li:not(.select2-search__field)",
          tolerance: "pointer",
          stop: function () {
            $($list.find(".select2-selection__choice").get().reverse()).each(
              function () {
                var id = $(this).data("data").id;
                var option = $select.find('option[value="' + id + '"]')[0];
                $select.prepend(option);
              }
            );
          },
        });
      }
    });

  // Ajax order search boxes for sellers
  $(":input.wc-riothere-main-categories-search")
    .filter(":not(.enhanced)")
    .each(function () {
      var select2_args = {
        allowClear: $(this).data("allow_clear") ? true : false,
        placeholder: $(this).data("placeholder"),
        minimumInputLength: $(this).data("minimum_input_length")
          ? $(this).data("minimum_input_length")
          : "1",
        escapeMarkup: function (m) {
          return m;
        },
        ajax: {
          url: riothere_global.ajax_url,
          dataType: "json",
          delay: 1000,
          data: function (params) {
            return {
              term: params.term,
              action: "woocommerce_json_search_main_categories",
              security: riothere_global.search_main_categories_nonce,
              exclude: $(this).data("exclude"),
            };
          },
          processResults: function (data) {
            var terms = [];
            if (data) {
              $.each(data, function (id, text) {
                terms.push({
                  id: id,
                  text: text,
                });
              });
            }
            return {
              results: terms,
            };
          },
          cache: true,
        },
      };

      select2_args = $.extend(select2_args, getEnhancedSelectFormatString());

      $(this).selectWoo(select2_args).addClass("enhanced");

      if ($(this).data("sortable")) {
        var $select = $(this);
        var $list = $(this)
          .next(".select2-container")
          .find("ul.select2-selection__rendered");

        $list.sortable({
          placeholder: "ui-state-highlight select2-selection__choice",
          forcePlaceholderSize: true,
          items: "li:not(.select2-search__field)",
          tolerance: "pointer",
          stop: function () {
            $($list.find(".select2-selection__choice").get().reverse()).each(
              function () {
                var id = $(this).data("data").id;
                var option = $select.find('option[value="' + id + '"]')[0];
                $select.prepend(option);
              }
            );
          },
        });
      }
    });

  $(".filter-date").each(function () {
    // Date picker fields setup
    var from = $(this).find("input.riothere-date-from"),
      to = $(this).find("input.riothere-date-to");

    from.datepicker({ dateFormat: "yy-mm-dd" });
    to.datepicker({ dateFormat: "yy-mm-dd" });
    // by default, the dates look like this "April 3, 2017"
    // I decided to make it 2017-04-03 with this parameter datepicker({dateFormat : "yy-mm-dd"});

    // the rest part of the script prevents from choosing incorrect date interval
    from.on("change", function () {
      to.datepicker("option", "minDate", from.val());
    });

    to.on("change", function () {
      from.datepicker("option", "maxDate", to.val());
    });
  });

  $(".add_seller_role_to_customers_who_own_product").on("click", function () {
    $.ajax({
      type: "POST",
      url: riothere_global.ajax_url,
      data: {
        action: "add_sller_role_to_customers_handler",
        ajax_nonce: riothere_global.ajax_nonce,
      },
      success: function (data) {
        alert("data -> API is getting called");
      },
      error: function (error) {
        console.log("errorThrown", error); // error

        alert("!!!!!! data -> API is getting called");
      },
    });
  });

  $(".products_sync_button").on("click", function () {
    $(".products_sync_button").prop("disabled", true);

    $.ajax({
      type: "POST",
      url: `${riothere_global.rest_url}riothere/v1/refresh-search`,
      data: {},
      success: function (data) {
        $(".products_sync_button").prop("disabled", false);
        alert("Syncing Complete");
      },
      error: function (error) {
        alert("Syncing Failed: " + JSON.stringify(error));
      },
    });
  });

  // Seller Reports
  $("#riothere_seller_report_submit_btn").on("click", function () {
    const sellerId = $("#riothere_seller_reports_seller_id").val();
    const dateFrom = $("#riothere_seller_reports_date_from").val();
    const dateTo = $("#riothere_seller_reports_date_to").val();
    const status = $("#riothere_seller_reports_status").val();
    const dateRange = $("#riothere_seller_reports_daterange").val();

    let url = "admin.php?page=seller-reports";

    if (sellerId) {
      url += `&seller_id=${sellerId}`;
    }
    if (dateFrom) {
      url += `&date_from=${dateFrom}`;
    }
    if (dateTo) {
      url += `&date_to=${dateTo}`;
    }
    if (status) {
      url += `&status=${status}`;
    }
    if (dateRange) {
      url += `&date_range=${dateRange}`;
    }

    window.location.href = url;
  });

  const selectedValue = $("#riothere_seller_reports_daterange").val();
  if (selectedValue === "Custom") {
    $("#riothere_seller_reports_date_input").show();
  }

  $("#riothere_seller_reports_daterange").on("change", () => {
    const selectedValue = $("#riothere_seller_reports_daterange").val();

    if (selectedValue === "Custom") {
      $("#riothere_seller_reports_date_input").show();
    }
    if (selectedValue === "Today") {
      $("#riothere_seller_reports_date_input").hide();
      let today = new Date();
      let dd = today.getDate();

      let mm = today.getMonth() + 1;
      const yyyy = today.getFullYear();
      if (dd < 10) {
        dd = `0${dd}`;
      }

      if (mm < 10) {
        mm = `0${mm}`;
      }
      today = `${yyyy}-${mm}-${dd}`;
      $("#riothere_seller_reports_date_from").val(today);
      $("#riothere_seller_reports_date_to").val(today);
    }
    if (selectedValue === "This Week") {
      $("#riothere_seller_reports_date_input").hide();
      const today = new Date();

      let firstDay = new Date(
        today.setDate(today.getDate() - today.getDay() + 1)
      );
      let lastDay = new Date(
        today.setDate(today.getDate() - today.getDay() + 7)
      );

      let first_dd = firstDay.getDate();
      let first_mm = firstDay.getMonth() + 1;
      const first_yyyy = firstDay.getFullYear();
      if (first_dd < 10) {
        first_dd = `0${first_dd}`;
      }

      if (first_mm < 10) {
        first_mm = `0${first_mm}`;
      }
      firstDay = `${first_yyyy}-${first_mm}-${first_dd}`;

      let last_dd = lastDay.getDate();
      let last_mm = lastDay.getMonth() + 1;
      const last_yyyy = lastDay.getFullYear();
      if (last_dd < 10) {
        last_dd = `0${last_dd}`;
      }

      if (last_mm < 10) {
        last_mm = `0${last_mm}`;
      }
      lastDay = `${last_yyyy}-${last_mm}-${last_dd}`;

      // const firstDayFormated =
      //   firstDay.getFullYear() +
      //   "-" +
      //   (today.getMonth() + 1) +
      //   "-" +
      //   firstDay.getDate();

      // const lastDayFormated =
      //   lastDay.getFullYear() +
      //   "-" +
      //   (lastDay.getMonth() + 1) +
      //   "-" +
      //   lastDay.getDate();

      $("#riothere_seller_reports_date_from").val(firstDay);
      $("#riothere_seller_reports_date_to").val(lastDay);
    }
    if (selectedValue === "This Month") {
      $("#riothere_seller_reports_date_input").hide();
      const today = new Date();

      let firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
      let lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

      let first_dd = firstDay.getDate();
      let first_mm = firstDay.getMonth() + 1;
      const first_yyyy = firstDay.getFullYear();
      if (first_dd < 10) {
        first_dd = `0${first_dd}`;
      }

      if (first_mm < 10) {
        first_mm = `0${first_mm}`;
      }
      firstDay = `${first_yyyy}-${first_mm}-${first_dd}`;

      let last_dd = lastDay.getDate();
      let last_mm = lastDay.getMonth() + 1;
      const last_yyyy = lastDay.getFullYear();
      if (last_dd < 10) {
        last_dd = `0${last_dd}`;
      }

      if (last_mm < 10) {
        last_mm = `0${last_mm}`;
      }
      lastDay = `${last_yyyy}-${last_mm}-${last_dd}`;

      // const firstDayFormated =
      //   firstDay.getFullYear() +
      //   "-" +
      //   (today.getMonth() + 1) +
      //   "-" +
      //   firstDay.getDate();

      // const lastDayFormated =
      //   lastDay.getFullYear() +
      //   "-" +
      //   (lastDay.getMonth() + 1) +
      //   "-" +
      //   lastDay.getDate();
      console.log("test from down here");
      $("#riothere_seller_reports_date_from").val(firstDay);
      $("#riothere_seller_reports_date_to").val(lastDay);
    }
    if (selectedValue === "All Time") {
      $("#riothere_seller_reports_date_input").hide();
      $("#riothere_seller_reports_date_from").val("");
      $("#riothere_seller_reports_date_to").val("");
    }
  });

  // Wishlist related functions
  $(":input.wc-riothere-products-search")
    .filter(":not(.enhanced)")
    .each(function () {
      var select2_args = {
        allowClear: $(this).data("allow_clear") ? true : false,
        placeholder: $(this).data("placeholder"),
        minimumInputLength: $(this).data("minimum_input_length")
          ? $(this).data("minimum_input_length")
          : "1",
        escapeMarkup: function (m) {
          return m;
        },
        ajax: {
          url: riothere_global.ajax_url,
          dataType: "json",
          delay: 1000,
          data: function (params) {
            return {
              term: params.term,
              action: "woocommerce_json_search_sku",
              security: riothere_global.search_sku_nonce,
              exclude: $(this).data("exclude"),
            };
          },
          processResults: function (data) {
            var terms = [];
            if (data) {
              $.each(data, function (id, text) {
                terms.push({
                  id: id,
                  text: text,
                });
              });
            }
            return {
              results: terms,
            };
          },
          cache: true,
        },
      };

      select2_args = $.extend(select2_args, getEnhancedSelectFormatString());

      $(this).selectWoo(select2_args).addClass("enhanced");

      if ($(this).data("sortable")) {
        var $select = $(this);
        var $list = $(this)
          .next(".select2-container")
          .find("ul.select2-selection__rendered");

        $list.sortable({
          placeholder: "ui-state-highlight select2-selection__choice",
          forcePlaceholderSize: true,
          items: "li:not(.select2-search__field)",
          tolerance: "pointer",
          stop: function () {
            $($list.find(".select2-selection__choice").get().reverse()).each(
              function () {
                var id = $(this).data("data").id;
                var option = $select.find('option[value="' + id + '"]')[0];
                $select.prepend(option);
              }
            );
          },
        });
      }
    });

  $(":input.wc-riothere-wishlists-brands-search")
    .filter(":not(.enhanced)")
    .each(function () {
      var select2_args = {
        allowClear: $(this).data("allow_clear") ? true : false,
        placeholder: $(this).data("placeholder"),
        minimumInputLength: $(this).data("minimum_input_length")
          ? $(this).data("minimum_input_length")
          : "1",
        escapeMarkup: function (m) {
          return m;
        },
        ajax: {
          url: riothere_global.ajax_url,
          dataType: "json",
          delay: 1000,
          data: function (params) {
            return {
              term: params.term,
              action: "woocommerce_json_search_wishlists_brand",
              security: riothere_global.search_wishlists_brands_nonce,
              exclude: $(this).data("exclude"),
            };
          },
          processResults: function (data) {
            var terms = [];
            if (data) {
              $.each(data, function (id, text) {
                terms.push({
                  id: id,
                  text: text,
                });
              });
            }
            return {
              results: terms,
            };
          },
          cache: true,
        },
      };

      select2_args = $.extend(select2_args, getEnhancedSelectFormatString());

      $(this).selectWoo(select2_args).addClass("enhanced");

      if ($(this).data("sortable")) {
        var $select = $(this);
        var $list = $(this)
          .next(".select2-container")
          .find("ul.select2-selection__rendered");

        $list.sortable({
          placeholder: "ui-state-highlight select2-selection__choice",
          forcePlaceholderSize: true,
          items: "li:not(.select2-search__field)",
          tolerance: "pointer",
          stop: function () {
            $($list.find(".select2-selection__choice").get().reverse()).each(
              function () {
                var id = $(this).data("data").id;
                var option = $select.find('option[value="' + id + '"]')[0];
                $select.prepend(option);
              }
            );
          },
        });
      }
    });

  $(":input.wc-riothere-wishlists-colors-search")
    .filter(":not(.enhanced)")
    .each(function () {
      var select2_args = {
        allowClear: $(this).data("allow_clear") ? true : false,
        placeholder: $(this).data("placeholder"),
        minimumInputLength: $(this).data("minimum_input_length")
          ? $(this).data("minimum_input_length")
          : "1",
        escapeMarkup: function (m) {
          return m;
        },
        ajax: {
          url: riothere_global.ajax_url,
          dataType: "json",
          delay: 1000,
          data: function (params) {
            return {
              term: params.term,
              action: "woocommerce_json_search_wishlists_colors",
              security: riothere_global.search_wishlists_colors_nonce,
              exclude: $(this).data("exclude"),
            };
          },
          processResults: function (data) {
            var terms = [];
            if (data) {
              $.each(data, function (id, text) {
                terms.push({
                  id: id,
                  text: text,
                });
              });
            }
            return {
              results: terms,
            };
          },
          cache: true,
        },
      };

      select2_args = $.extend(select2_args, getEnhancedSelectFormatString());

      $(this).selectWoo(select2_args).addClass("enhanced");

      if ($(this).data("sortable")) {
        var $select = $(this);
        var $list = $(this)
          .next(".select2-container")
          .find("ul.select2-selection__rendered");

        $list.sortable({
          placeholder: "ui-state-highlight select2-selection__choice",
          forcePlaceholderSize: true,
          items: "li:not(.select2-search__field)",
          tolerance: "pointer",
          stop: function () {
            $($list.find(".select2-selection__choice").get().reverse()).each(
              function () {
                var id = $(this).data("data").id;
                var option = $select.find('option[value="' + id + '"]')[0];
                $select.prepend(option);
              }
            );
          },
        });
      }
    });

  $(":input.wc-riothere-wishlists-size-search")
    .filter(":not(.enhanced)")
    .each(function () {
      var select2_args = {
        allowClear: $(this).data("allow_clear") ? true : false,
        placeholder: $(this).data("placeholder"),
        minimumInputLength: $(this).data("minimum_input_length")
          ? $(this).data("minimum_input_length")
          : "1",
        escapeMarkup: function (m) {
          return m;
        },
        ajax: {
          url: riothere_global.ajax_url,
          dataType: "json",
          delay: 1000,
          data: function (params) {
            return {
              term: params.term,
              action: "woocommerce_json_search_wishlists_size",
              security: riothere_global.search_wishlists_size_nonce,
              exclude: $(this).data("exclude"),
            };
          },
          processResults: function (data) {
            var terms = [];
            if (data) {
              $.each(data, function (id, text) {
                terms.push({
                  id: id,
                  text: text,
                });
              });
            }
            return {
              results: terms,
            };
          },
          cache: true,
        },
      };

      select2_args = $.extend(select2_args, getEnhancedSelectFormatString());

      $(this).selectWoo(select2_args).addClass("enhanced");

      if ($(this).data("sortable")) {
        var $select = $(this);
        var $list = $(this)
          .next(".select2-container")
          .find("ul.select2-selection__rendered");

        $list.sortable({
          placeholder: "ui-state-highlight select2-selection__choice",
          forcePlaceholderSize: true,
          items: "li:not(.select2-search__field)",
          tolerance: "pointer",
          stop: function () {
            $($list.find(".select2-selection__choice").get().reverse()).each(
              function () {
                var id = $(this).data("data").id;
                var option = $select.find('option[value="' + id + '"]')[0];
                $select.prepend(option);
              }
            );
          },
        });
      }
    });

  //Cart related functions
  $(":input.wc-riothere-products-search")
    .filter(":not(.enhanced)")
    .each(function () {
      var select2_args = {
        allowClear: $(this).data("allow_clear") ? true : false,
        placeholder: $(this).data("placeholder"),
        minimumInputLength: $(this).data("minimum_input_length")
          ? $(this).data("minimum_input_length")
          : "1",
        escapeMarkup: function (m) {
          return m;
        },
        ajax: {
          url: riothere_global.ajax_url,
          dataType: "json",
          delay: 1000,
          data: function (params) {
            return {
              term: params.term,
              action: "woocommerce_json_search_sku",
              security: riothere_global.search_sku_nonce,
              exclude: $(this).data("exclude"),
            };
          },
          processResults: function (data) {
            var terms = [];
            if (data) {
              $.each(data, function (id, text) {
                terms.push({
                  id: id,
                  text: text,
                });
              });
            }
            return {
              results: terms,
            };
          },
          cache: true,
        },
      };

      select2_args = $.extend(select2_args, getEnhancedSelectFormatString());

      $(this).selectWoo(select2_args).addClass("enhanced");

      if ($(this).data("sortable")) {
        var $select = $(this);
        var $list = $(this)
          .next(".select2-container")
          .find("ul.select2-selection__rendered");

        $list.sortable({
          placeholder: "ui-state-highlight select2-selection__choice",
          forcePlaceholderSize: true,
          items: "li:not(.select2-search__field)",
          tolerance: "pointer",
          stop: function () {
            $($list.find(".select2-selection__choice").get().reverse()).each(
              function () {
                var id = $(this).data("data").id;
                var option = $select.find('option[value="' + id + '"]')[0];
                $select.prepend(option);
              }
            );
          },
        });
      }
    });

  $(":input.wc-riothere-carts-brands-search")
    .filter(":not(.enhanced)")
    .each(function () {
      var select2_args = {
        allowClear: $(this).data("allow_clear") ? true : false,
        placeholder: $(this).data("placeholder"),
        minimumInputLength: $(this).data("minimum_input_length")
          ? $(this).data("minimum_input_length")
          : "1",
        escapeMarkup: function (m) {
          return m;
        },
        ajax: {
          url: riothere_global.ajax_url,
          dataType: "json",
          delay: 1000,
          data: function (params) {
            return {
              term: params.term,
              action: "woocommerce_json_search_carts_brand",
              security: riothere_global.search_carts_brands_nonce,
              exclude: $(this).data("exclude"),
            };
          },
          processResults: function (data) {
            var terms = [];
            if (data) {
              $.each(data, function (id, text) {
                terms.push({
                  id: id,
                  text: text,
                });
              });
            }
            return {
              results: terms,
            };
          },
          cache: true,
        },
      };

      select2_args = $.extend(select2_args, getEnhancedSelectFormatString());

      $(this).selectWoo(select2_args).addClass("enhanced");

      if ($(this).data("sortable")) {
        var $select = $(this);
        var $list = $(this)
          .next(".select2-container")
          .find("ul.select2-selection__rendered");

        $list.sortable({
          placeholder: "ui-state-highlight select2-selection__choice",
          forcePlaceholderSize: true,
          items: "li:not(.select2-search__field)",
          tolerance: "pointer",
          stop: function () {
            $($list.find(".select2-selection__choice").get().reverse()).each(
              function () {
                var id = $(this).data("data").id;
                var option = $select.find('option[value="' + id + '"]')[0];
                $select.prepend(option);
              }
            );
          },
        });
      }
    });

  $(":input.wc-riothere-carts-colors-search")
    .filter(":not(.enhanced)")
    .each(function () {
      var select2_args = {
        allowClear: $(this).data("allow_clear") ? true : false,
        placeholder: $(this).data("placeholder"),
        minimumInputLength: $(this).data("minimum_input_length")
          ? $(this).data("minimum_input_length")
          : "1",
        escapeMarkup: function (m) {
          return m;
        },
        ajax: {
          url: riothere_global.ajax_url,
          dataType: "json",
          delay: 1000,
          data: function (params) {
            return {
              term: params.term,
              action: "woocommerce_json_search_wishlists_colors",
              security: riothere_global.search_wishlists_colors_nonce,
              exclude: $(this).data("exclude"),
            };
          },
          processResults: function (data) {
            var terms = [];
            if (data) {
              $.each(data, function (id, text) {
                terms.push({
                  id: id,
                  text: text,
                });
              });
            }
            return {
              results: terms,
            };
          },
          cache: true,
        },
      };

      select2_args = $.extend(select2_args, getEnhancedSelectFormatString());

      $(this).selectWoo(select2_args).addClass("enhanced");

      if ($(this).data("sortable")) {
        var $select = $(this);
        var $list = $(this)
          .next(".select2-container")
          .find("ul.select2-selection__rendered");

        $list.sortable({
          placeholder: "ui-state-highlight select2-selection__choice",
          forcePlaceholderSize: true,
          items: "li:not(.select2-search__field)",
          tolerance: "pointer",
          stop: function () {
            $($list.find(".select2-selection__choice").get().reverse()).each(
              function () {
                var id = $(this).data("data").id;
                var option = $select.find('option[value="' + id + '"]')[0];
                $select.prepend(option);
              }
            );
          },
        });
      }
    });

  $(":input.wc-riothere-carts-size-search")
    .filter(":not(.enhanced)")
    .each(function () {
      var select2_args = {
        allowClear: $(this).data("allow_clear") ? true : false,
        placeholder: $(this).data("placeholder"),
        minimumInputLength: $(this).data("minimum_input_length")
          ? $(this).data("minimum_input_length")
          : "1",
        escapeMarkup: function (m) {
          return m;
        },
        ajax: {
          url: riothere_global.ajax_url,
          dataType: "json",
          delay: 1000,
          data: function (params) {
            return {
              term: params.term,
              action: "woocommerce_json_search_carts_size",
              security: riothere_global.search_carts_size_nonce,
              exclude: $(this).data("exclude"),
            };
          },
          processResults: function (data) {
            var terms = [];
            if (data) {
              $.each(data, function (id, text) {
                terms.push({
                  id: id,
                  text: text,
                });
              });
            }
            return {
              results: terms,
            };
          },
          cache: true,
        },
      };

      select2_args = $.extend(select2_args, getEnhancedSelectFormatString());

      $(this).selectWoo(select2_args).addClass("enhanced");

      if ($(this).data("sortable")) {
        var $select = $(this);
        var $list = $(this)
          .next(".select2-container")
          .find("ul.select2-selection__rendered");

        $list.sortable({
          placeholder: "ui-state-highlight select2-selection__choice",
          forcePlaceholderSize: true,
          items: "li:not(.select2-search__field)",
          tolerance: "pointer",
          stop: function () {
            $($list.find(".select2-selection__choice").get().reverse()).each(
              function () {
                var id = $(this).data("data").id;
                var option = $select.find('option[value="' + id + '"]')[0];
                $select.prepend(option);
              }
            );
          },
        });
      }
    });

  // Add the Export buttons on the appropriate pages
  const wishlistExportButton = `<form method='get' action='admin.php?page=spee-dashboard'>
  <input type='hidden' name='page' value='spee-dashboard'/>
  <input type='hidden' name='noheader' value='1'/>
  <input style='display:none' type='radio' name='format' id='formatCSV' value='csv' checked='checked'/>
  <input type='submit' name='export' id='csvExport' value='Export' style='color: #2271b1;
  border-color: #2271b1;
  background: #f6f7f7;
  vertical-align: top;
  display: inline-block;
  text-decoration: none;
  font-size: 13px;
  line-height: 2.15384615;
  min-height: 30px;
  padding: 0 10px;
  cursor: pointer;
  border-width: 1px;
  border-style: solid;
  -webkit-appearance: none;
  border-radius: 3px;
  white-space: nowrap;
  box-sizing: border-box;' />
</form>`;

  const cartExportButton = `<form method='get' action='admin.php?page=cart-spee-dashboard'>
  <input type='hidden' name='page' value='cart-spee-dashboard'/>
  <input type='hidden' name='noheader' value='1'/>
  <input style='display:none' type='radio' name='format' id='formatCSV' value='csv' checked='checked'/>
  <input type='submit' name='export' id='csvExport' value='Export' style='color: #2271b1;
  border-color: #2271b1;
  background: #f6f7f7;
  vertical-align: top;
  display: inline-block;
  text-decoration: none;
  font-size: 13px;
  line-height: 2.15384615;
  min-height: 30px;
  padding: 0 10px;
  cursor: pointer;
  border-width: 1px;
  border-style: solid;
  -webkit-appearance: none;
  border-radius: 3px;
  white-space: nowrap;
  box-sizing: border-box;' />
</form>`;

  if ($("#posts-filter").parents(".post-type-wishlists").length > 0) {
    $(wishlistExportButton).insertAfter("#posts-filter");
  }

  if ($("#posts-filter").parents(".post-type-carts").length > 0) {
    $(cartExportButton).insertAfter("#posts-filter");
  }

  $(":input#csvExport").on("click", function () {
    $(".csvExport_hidden_field").remove();

    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    const category = urlParams.get("_category_id");
    if (category) {
      $(
        `<input type='hidden' class='csvExport_hidden_field' name='category_id' value='${category}'/>`
      ).insertBefore("#csvExport");
    }

    const sku = urlParams.get("_product_id");
    if (sku) {
      $(
        `<input type='hidden' class='csvExport_hidden_field' name='product_id' value='${sku}'/>`
      ).insertBefore("#csvExport");
    }

    const brand = urlParams.get("_product_brand");
    if (brand) {
      $(
        `<input type='hidden' class='csvExport_hidden_field' name='product_brand' value='${brand}'/>`
      ).insertBefore("#csvExport");
    }

    const size = urlParams.get("_product_size");
    if (size) {
      $(
        `<input type='hidden' class='csvExport_hidden_field' name='product_size' value='${size}'/>`
      ).insertBefore("#csvExport");
    }

    const color = urlParams.get("_product_color");
    if (color) {
      $(
        `<input type='hidden' class='csvExport_hidden_field' name='product_color' value='${color}'/>`
      ).insertBefore("#csvExport");
    }
  });

  // Export button on Seller Reports page
  const sellerReportsExportButton = `<form method='get' action='admin.php?page=seller-reports-spee-dashboard'>
  <input type='hidden' name='page' value='seller-reports-spee-dashboard'/>
  <input type='hidden' name='noheader' value='1'/>
  <input style='display:none' type='radio' name='format' id='formatCSV' value='csv' checked='checked'/>
  <input type='submit' name='export' id='sellerReportsCsvExport' value='Export' style='color: #2271b1;
  border-color: #2271b1;
  background: #f6f7f7;
  vertical-align: top;
  display: inline-block;
  text-decoration: none;
  font-size: 13px;
  line-height: 2.15384615;
  min-height: 30px;
  padding: 0 10px;
  cursor: pointer;
  border-width: 1px;
  border-style: solid;
  -webkit-appearance: none;
  border-radius: 3px;
  white-space: nowrap;
  box-sizing: border-box;' />
</form>`;

  if (
    $("#post-body-content").parents(".toplevel_page_seller-reports").length > 0
  ) {
    $(sellerReportsExportButton).insertAfter("#post-body-content");
  }

  $(":input#sellerReportsCsvExport").on("click", function () {
    $(".csvExport_hidden_field").remove();

    const queryString = window.location.search;
    const urlParams = new URLSearchParams(queryString);
    console.log("urlParams = ", urlParams);

    const sellerId = urlParams.get("seller_id");
    if (sellerId) {
      $(
        `<input type='hidden' class='csvExport_hidden_field' name='seller_id' value='${sellerId}'/>`
      ).insertBefore("#sellerReportsCsvExport");
    }

    const dateFrom = urlParams.get("date_from");
    if (dateFrom) {
      $(
        `<input type='hidden' class='csvExport_hidden_field' name='date_from' value='${dateFrom}'/>`
      ).insertBefore("#sellerReportsCsvExport");
    }

    const dateTo = urlParams.get("date_to");
    if (dateTo) {
      $(
        `<input type='hidden' class='csvExport_hidden_field' name='date_to' value='${dateTo}'/>`
      ).insertBefore("#sellerReportsCsvExport");
    }

    const status = urlParams.get("status");
    if (status) {
      $(
        `<input type='hidden' class='csvExport_hidden_field' name='status' value='${status}'/>`
      ).insertBefore("#sellerReportsCsvExport");
    }
  });

  // Try & Buy on Dashboard
  $("#riothere_fetch_try_and_buy_button").on("click", function () {
    $("#riothere_try_and_buy_dashboard_table tbody tr").remove();
    $("#riothere_fetch_try_and_buy_button").prop("disabled", true);
    const date = $("#try-and-buy-date-picker").val();

    $.ajax({
      type: "POST",
      url: `${riothere_global.rest_url}riothere/v1/try-and-buy-orders-to-retrieve-and-deliver`,
      data: {
        time_selected: date.toString(),
      },
      success: function (result) {
        $("#riothere_try_and_buy_dashboard_table tbody").append(
          Object.keys(result.data).map(
            (orderId) =>
              `<tr>
              <td>${orderId}</td>
              <td>${(result.data[orderId]?.items_to_deliver || []).join(
                ", "
              )}</td>
              <td>${(result.data[orderId]?.items_to_retrieve || []).join(
                ", "
              )}</td>
              <td>${(result.data[orderId]?.items_purchased || []).join(
                ", "
              )}</td>
            </tr>`
          )
        );
        $("#riothere_fetch_try_and_buy_button").prop("disabled", false);
      },
      error: function (error) {
        alert("Error: " + JSON.stringify(error));
      },
    });
  });
})(jQuery);
