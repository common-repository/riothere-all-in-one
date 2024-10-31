(function ($) {
  "use strict";
  const promotionsData = window.riothere_admin_campaigns_global;
  const isAddPage = $('input[name="is_add_mode"]').val() === "true";
  const campaignID = $('input[name="campaign_id"]').val();

  if (isAddPage) {
    // setActionsButtonStatus({disabled: true});
  }
  setupFields();
  $(".post-type-riothere-campaign form").on("submit", function (event) {
    const isFormValid = handleValidatePromotionData();
    if (!isFormValid) {
      event.preventDefault();
      setActionsButtonStatus({ disabled: true });
    }
  });

  $(".campaign-filter-products-btn").on("click", function () {
    if (handleValidatePromotionData()) {
      getProducts();
    }
  });

  function getProducts() {
    const filters = getFilterRules();
    $(".promotion-products-list").html("");
    showProductsListStatus("loading");
    $(".post-type-riothere-campaign .campaign-filter-products-btn").prop(
      "disabled",
      true
    );

    $.ajax({
      type: "POST",
      url: promotionsData.ajax_url,
      data: {
        action: "campaigns_get_products",
        ajax_nonce: promotionsData.campaigns_nonce,
        include: filters.include,
        exclude: filters.exclude,
        free_shipping_promotion_id: campaignID,
        is_new_page: isAddPage ? "true" : "false",
      },
      success: function (data) {
        const response = JSON.parse(data);
        if (response.success) {
          const sellerExcludedProductIds = JSON.parse(
            $("#sellers_excluded_product_ids").val()
          );

          const availableProducts = response.data.filter(({ id }) => {
            return (
              sellerExcludedProductIds.find(
                (productId) => parseInt(productId) === parseInt(id)
              ) === undefined
            );
          });
          if (availableProducts.length > 0) {
            availableProducts.forEach((productData) => {
              const itemTemplate = $(
                ".promotion-product-item.template"
              ).clone();
              itemTemplate.removeClass("template");
              itemTemplate.data("id", productData.id);
              itemTemplate
                .find(".promotion-product-item-image img")
                .attr("src", productData.image_url);
              itemTemplate.find(".product-sku").html(productData.sku);
              itemTemplate.find(".product-name").html(productData.title);
              itemTemplate
                .find(".promotion-product-regular-price")
                .html(productData.price);

              $(".promotion-products-list").append(itemTemplate);
            });

            showProductsListStatus(null);
          } else {
            showProductsListStatus("empty");
          }
        }
        $(".post-type-riothere-promotion .campaign-filter-products-btn").prop(
          "disabled",
          false
        );
      },
      error: function (error) {
        console.log("errorThrown", error); // error
        $(".post-type-riothere-promotion .campaign-filter-products-btn").prop(
          "disabled",
          false
        );
      },
    });
  }

  function showProductsListStatus(status) {
    const emptyStatusElement = $(
      ".promotion-list-status.promotion-products-empty"
    );
    const filterStatusElement = $(
      ".promotion-list-status.promotion-disable-edit"
    );
    const loadingStatusElement = $(
      ".promotion-list-status.promotion-products-loading"
    );
    emptyStatusElement.removeClass("active");
    filterStatusElement.removeClass("active");
    loadingStatusElement.removeClass("active");
    if (status === "empty") {
      emptyStatusElement.addClass("active");
    } else if (status === "filter") {
      filterStatusElement.addClass("active");
    } else if (status === "loading") {
      loadingStatusElement.addClass("active");
    }
  }

  function getFilterRules() {
    const includeProducts = jQuery('select[name="include_products[]"]').val();
    const includeSellers = jQuery('select[name="include_sellers[]"]').val();
    const includeCategories = jQuery(
      'select[name="include_product_categories[]"]'
    ).val();
    const includeTags = jQuery('select[name="include_product_tags[]"]').val();
    const includeMinPrice = jQuery(
      'input[name="include_product_min_price"]'
    ).val();
    const includeMaxPrice = jQuery(
      'input[name="include_product_max_price"]'
    ).val();

    const excludeProducts = jQuery('select[name="exclude_products[]"]').val();
    const excludeSellers = jQuery('select[name="exclude_sellers[]"]').val();
    const excludeCategories = jQuery(
      'select[name="exclude_product_categories[]"]'
    ).val();
    const excludeTags = jQuery('select[name="exclude_product_tags[]"]').val();
    const excludeMinPrice = jQuery(
      'input[name="exclude_product_min_price"]'
    ).val();
    const excludeMaxPrice = jQuery(
      'input[name="exclude_product_max_price"]'
    ).val();

    return {
      include: {
        product_ids: includeProducts,
        categories: includeCategories,
        tags: includeTags,
        sellers: includeSellers,
        min_price: includeMinPrice,
        max_price: includeMaxPrice,
      },
      exclude: {
        product_ids: excludeProducts,
        categories: excludeCategories,
        tags: excludeTags,
        sellers: excludeSellers,
        min_price: excludeMinPrice,
        max_price: excludeMaxPrice,
      },
    };
  }

  function formatProductSelect2Result(productElement) {
    if (!productElement.id) {
      return productElement.text;
    }
    const productData = $(productElement.element).data("product");

    return $(
      `<span class="select2-product-option">
                <img src="${productData.image_url}" alt="">
                <span><strong>SKU:</strong> ${productData.sku} <br/> <strong>Product title</strong> ${productData.title}</span>
            </span>`
    );
  }

  // function formatProductSelect3Result(productElement) {
  //   if (!productElement.id) {
  //     return productElement.text;
  //   }
  //   const productData = $(productElement.element).data("product");

  //   return $(
  //     `<span class="select2-product-option">
  //               <span><strong>SKU:</strong> ${productData.sku} <br/> <strong>Product title</strong> ${productData.title}</span>
  //           </span>`
  //   );
  // }

  function select2MatchProducts(params, data) {
    const productData = $(data.element).data("product");
    // If there are no search terms, return all of the data
    if ($.trim(params.term) === "") {
      return data;
    }

    // Do not display the item if there is no 'text' property
    if (typeof data.text === "undefined") {
      return null;
    }

    // `params.term` should be the term that is used for searching
    // `data.text` is the text that is displayed for the data object
    if (
      data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1 ||
      productData.title.toLowerCase().indexOf(params.term.toLowerCase()) > -1 ||
      productData.id
        .toString()
        .toLowerCase()
        .indexOf(params.term.toLowerCase()) > -1
    ) {
      var modifiedData = $.extend({}, data, true);
      modifiedData.text += " (matched)";

      // You can return modified objects from here
      // This includes matching the `children` how you want in nested data sets
      return modifiedData;
    }

    // Return `null` if the term should not be displayed
    return null;
  }

  function formatTermsSelect2Result(termElement) {
    if (!termElement.id) {
      return termElement.text;
    }
    const termLevel = $(termElement.element).data("level");

    return $(`<span>${"--".repeat(termLevel)}${termElement.text}</span>`);
  }

  function updateProductsList(type = "include") {
    const filters = getFilterRules();
    const ruleLoaderElem = $(
      `.rules-wrapper.${type}-rules-wrapper .rule-loading`
    );
    const isAllProductLoadedInput = $(
      `input[name="is_${type}_product_currently_all_loaded"]`
    );
    const filterProductRelatedInput = $(
      `input[name="filter_${type}_products_by_filter"]`
    );

    if (
      !filterProductRelatedInput.prop("checked") &&
      isAllProductLoadedInput.val() === "true"
    ) {
      return;
    }
    const rules = filterProductRelatedInput.prop("checked")
      ? filters[type]
      : {};

    ruleLoaderElem.addClass("loading");
    $.ajax({
      type: "POST",
      url: promotionsData.ajax_url,
      data: {
        action: "campaigns_get_products_by_rules",
        ajax_nonce: promotionsData.campaigns_nonce,
        ...rules,
      },
      success: function (data) {
        if (!filterProductRelatedInput.prop("checked")) {
          isAllProductLoadedInput.val("true");
        } else {
          isAllProductLoadedInput.val("false");
        }
        const response = JSON.parse(data);
        if (response.success) {
          const productsInput = $(`select[name="${type}_products[]"]`);
          const selectedProducts = productsInput.val();
          productsInput.html("");
          response.data.forEach((productData) => {
            const option = $(
              `<option value="${productData.id}" ${
                selectedProducts.find(
                  (productId) =>
                    parseInt(productId) === parseInt(productData.id)
                ) !== undefined
                  ? "selected"
                  : ""
              }>${productData.sku}</option>`
            );
            option.data("product", productData);
            productsInput.append(option);
          });
        }
        ruleLoaderElem.removeClass("loading");
      },
      error: function (error) {
        console.log("errorThrown", error); // error
      },
    });
  }

  function setupFields() {
    const excludeProductsElement = $('select[name="exclude_products[]"]');
    const excludeSellersElement = $('select[name="exclude_sellers[]"]');
    const excludeCategoriesElement = $(
      'select[name="exclude_product_categories[]"]'
    );
    const selectedCountries = $('select[name="selected_countries[]"]');
    const selectedCustomers = $('select[name="selected_customers[]"]');
    const excludeTagsElement = $('select[name="exclude_product_tags[]"]');
    const excludeMinPrice = $("#campaigns_settings #exclude_product_min_price");
    const excludeMaxPrice = $("#campaigns_settings #exclude_product_max_price");

    selectedCountries.select2({
      placeholder: "Select countries",
      allowClear: true,
      multiple: true,
      dropdownCssClass: "promotion-field-select2",

      closeOnSelect: false,
    });

    selectedCustomers.select2({
      placeholder: "Select customers",
      allowClear: true,
      multiple: true,
      dropdownCssClass: "promotion-field-select2",

      closeOnSelect: false,
    });

    excludeProductsElement.select2({
      placeholder: "Select products",
      allowClear: true,
      multiple: true,
      dropdownCssClass: "promotion-field-select2",
      templateResult: formatProductSelect2Result,
      closeOnSelect: false,
    });

    excludeSellersElement.select2({
      placeholder: "Select sellers",
      allowClear: true,
      multiple: true,
      dropdownCssClass: "promotion-field-select2",
      closeOnSelect: false,
    });

    excludeCategoriesElement.select2({
      placeholder: "Select product categories",
      allowClear: true,
      multiple: true,
      dropdownCssClass: "promotion-field-select2",
      templateResult: formatTermsSelect2Result,
      closeOnSelect: false,
    });

    excludeTagsElement.select2({
      placeholder: "Select product tags",
      allowClear: true,
      multiple: true,
      dropdownCssClass: "promotion-field-select2",
      templateResult: formatTermsSelect2Result,
      closeOnSelect: false,
    });

    $("#campaigns_settings .edit-promotion-settings-btn").on(
      "click",
      function (event) {
        handleSwitchToEditMode(event, "settings");
      }
    );

    $("#campaigns_settings .edit-promotion-rules-btn").on(
      "click",
      function (event) {
        handleSwitchToEditMode(event, "rules");
      }
    );

    $("#campaigns_settings .edit-promotion-products-btn").on(
      "click",
      function (event) {
        handleSwitchToEditMode(event, "products");
      }
    );

    // Exclude rules event listener
    [excludeMinPrice, excludeMaxPrice].forEach((element) => {
      element.on("blur keyup", () => {
        handleValidatePromotionData();
      });
      element.on("change", () => {
        const isIncludePriceRulesValid = validateProductPrice("exclude");
        if (isIncludePriceRulesValid) {
          updateProductsList("exclude");
        }
        handleValidatePromotionData();
      });
    });

    [
      excludeSellersElement,
      excludeCategoriesElement,
      excludeTagsElement,
    ].forEach((element) => {
      element.on("change", function () {
        const isExcludePriceRulesValid = validateProductPrice("exclude");
        if (isExcludePriceRulesValid) {
          updateProductsList("exclude");
        }
        showProductsListStatus("filter");
      });
    });

    $('input[name="filter_exclude_products_by_filter"]').on(
      "change",
      function () {
        updateProductsList("exclude");
      }
    );
  }

  function handleSwitchToEditMode(event, mode = "settings") {
    event.preventDefault();
    if (mode === "settings") {
      $('#campaigns_settings input[name="edit-promotion-settings"]').val(
        "true"
      );
      $("#campaigns_settings .section#promotion-view-settings").removeClass(
        "section-active"
      );
      $("#campaigns_settings .section#promotion-edit-settings").addClass(
        "section-active"
      );
      $("#campaigns_settings .promotion-btn.edit-promotion-rules-btn").remove();
    } else if (mode === "rules") {
      $('#campaigns_settings input[name="edit-promotion-rules"]').val("true");
      $('#campaigns_settings input[name="edit-promotion-settings"]').val(
        "true"
      );
      $("#campaigns_settings .section#promotion-view-rules").removeClass(
        "section-active"
      );
      $(
        "#campaigns_settings .section#promotion-view-included-products"
      ).removeClass("section-active");
      $("#campaigns_settings .section#promotion-edit-rules").addClass(
        "section-active"
      );
      $("#campaigns_settings .section#promotion-view-settings").removeClass(
        "section-active"
      );
      $("#campaigns_settings .section#promotion-edit-settings").addClass(
        "section-active"
      );
      updateProductsList("exclude");
      getProducts();
    } else if (mode === "products") {
      $(
        '#campaigns_settings input[name="promotion-view-included-products_status"]'
      ).val("true");
      $("#campaigns_settings .promotion-btn.edit-promotion-rules-btn").remove();
      $(
        "#campaigns_settings #promotion-view-included-products .promotion-h2 span"
      ).html("Edit Mode");

      $([document.documentElement, document.body]).animate(
        {
          scrollTop:
            $("#campaigns_settings #promotion-view-included-products").offset()
              .top - 100,
        },
        1000
      );
    } else {
      $("#campaigns_settings .section").removeClass("section-active");
      $("#campaigns_settings .section#promotion-edit").addClass(
        "section-active"
      );
    }
  }

  function handleShowInputError(input_id, error) {
    const inputElement = $(`.promotion-field #${input_id}`);
    if (inputElement.length > 0) {
      const errorElement = $(inputElement)
        .closest(".promotion-field")
        .find(".promotion-field-error");
      if (error !== null) {
        errorElement.addClass("active");
        errorElement.html(error);
        $([document.documentElement, document.body]).animate(
          {
            scrollTop: inputElement.offset().top - 100,
          },
          1000
        );
      } else {
        errorElement.removeClass("active");
        errorElement.html("");
      }
    } else {
      console.warn("calling show input error on invalid input_id");
    }
  }

  function validateProductPrice(type = "include") {
    let isMinPriceValid = true;
    let isMaxPriceValid = true;
    const productMinPrice = $(`input[name="${type}_product_min_price"]`).val();
    const productMaxPrice = $(`input[name="${type}_product_max_price"]`).val();
    const minInputId = `${type}_product_min_price`;
    const maxInputId = `${type}_product_max_price`;
    if (productMinPrice !== "" && productMaxPrice !== "") {
      if (
        isNaN(parseFloat(productMaxPrice)) &&
        isNaN(parseFloat(productMinPrice))
      ) {
        isMinPriceValid = false;
        isMaxPriceValid = false;
        handleShowInputError(
          minInputId,
          "Start price should be a number above or equal to zero and less than max price"
        );
        handleShowInputError(
          maxInputId,
          "Max price should be a number above zero and greater than min price"
        );
      } else if (
        isNaN(parseFloat(productMaxPrice)) ||
        parseFloat(productMaxPrice) <= 0
      ) {
        isMaxPriceValid = false;
        handleShowInputError(
          maxInputId,
          "Max price should be a number above zero and greater than min price"
        );
      } else if (
        isNaN(parseFloat(productMinPrice)) ||
        parseFloat(productMinPrice) < 0
      ) {
        isMinPriceValid = false;
        handleShowInputError(
          minInputId,
          "Start price should be a number above or equal to zero and less than max price"
        );
      } else if (parseFloat(productMinPrice) > parseFloat(productMaxPrice)) {
        isMinPriceValid = false;
        isMaxPriceValid = false;
        handleShowInputError(
          minInputId,
          "Start price should be less than max price"
        );
        handleShowInputError(
          maxInputId,
          "Max price should be greater than min price"
        );
      }
    } else if (
      productMinPrice !== "" &&
      (isNaN(parseFloat(productMinPrice)) || parseFloat(productMinPrice) < 0)
    ) {
      isMinPriceValid = false;
      handleShowInputError(
        minInputId,
        "Start price should be a number above or equal to zero"
      );
    } else if (
      productMaxPrice !== "" &&
      (isNaN(parseFloat(productMaxPrice)) || parseFloat(productMaxPrice) < 0)
    ) {
      isMaxPriceValid = false;
      handleShowInputError(
        maxInputId,
        "Max price should be a number above zero"
      );
    }
    if (isMinPriceValid) {
      handleShowInputError(minInputId, null);
    }
    if (isMaxPriceValid) {
      handleShowInputError(maxInputId, null);
    }
    return isMinPriceValid && isMaxPriceValid;
  }

  function handleValidatePromotionData() {
    let isValid = true;

    const isExcludePriceRulesValid = validateProductPrice("exclude");
    if (!isExcludePriceRulesValid) {
      isValid = false;
    }
    setActionsButtonStatus({ disabled: !isValid });
    showProductsListStatus("filter");

    return isValid;
  }

  function setActionsButtonStatus({ disabled }) {
    $('.post-type-riothere-promotion.post-php input[type="submit"]').prop(
      "disabled",
      disabled === true
    );
    $('.post-type-riothere-promotion.post-new-php input[type="submit"]').prop(
      "disabled",
      disabled === true
    );
    $(".post-type-riothere-promotion .campaign-filter-products-btn").prop(
      "disabled",
      disabled === true
    );
  }
})(jQuery);
