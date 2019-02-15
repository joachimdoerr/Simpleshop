var Simpleshop = (function ($) {
    var searchHandle = null,
        functionListParams = {};

    $(document).ready(function () {
        initProductSelect();
        initVariantSelect();
        initProductRexCategoryLink();
    });

    function addLoading($container) {
        var css = $container.offset(),
            $loading = $('<div class="pjax-loading"><div class="spinner"><div></div><div></div><div></div></div></div>');

        css.height = $container.outerHeight();
        css.width = $container.outerWidth();
        $('body').append($loading.addClass('show').css(css));

        return $loading;
    }

    function initProductRexCategoryLink() {
        var $container = $('#linking-container');

        if ($container.length) {
            var $input = $container.find('#REX_LINK_rex_category');

            functionListParams.cat_id = 0;

            window.setInterval(function () {
                if (functionListParams.cat_id != $input.val()) {
                    functionListParams.cat_id = $input.val();
                    showFunctionList();
                }
            }, 1000);
        }
    }

    function showFunctionList() {
        var $ajContainer = $('#linking-container .pjax-container'),
            $loading = addLoading($ajContainer);

        $.ajax({
            url: rex.simpleshop.ajax_url,
            cache: false,
            data: {
                'debug': rex.debug,
                'cat_id': functionListParams.cat_id,
                'controller': 'BeApi.list_functions',
                'fragment': 'link_product_rex_categories',
                'rex-api-call': 'simpleshop_be_api',
                'func': 'link_product_rex_categories',
                'search': functionListParams.search || '',
                'page': functionListParams.page || 0
            }
        }).done(function (resp) {
            $ajContainer.html(resp.message.html);
            $loading.remove();
        });
    }

    function initVariantSelect() {
        $('table.variants tbody').sortable({
            animation: 150,
            handle: '.sort-handle',
            update: function (e, ui) {
                updateVariantPrio($(this));
            }
        });
    }

    function updateVariantPrio($table) {
        $table.find('tr').each(function (index) {
            $(this).find('input.prio').val(index);
        });
    }

    function initProductSelect() {
        $('select.product-select2').select2({
            debug: true,
            ajax: {
                url: rex.simpleshop.ajax_url,
                data: function (params) {
                    return {
                        'rex-api-call': 'simpleshop_api',
                        controller: 'Package.selectProducts',
                        page: params.page,
                        term: params.term
                    }
                },
                processResults: function (data, params) {
                    params.page = params.page || 0;
                    return data.message.result
                }
            }
        });
    }

    cloneInput = function (el, length) {
        var $this = $(el),
            name = $this.prop('name');

        if (name.match(/[a-z\d]+\[[a-z\d]+\]\[[a-z\d]\]/i)) {
            name = name.replace(/([a-z\d]+)\[([a-z\d]+)\]\[([a-z\d]+)\]/i, '$1[$2][' + length + ']');
        }
        $this.prop('name', name);
    };

    return {
        saveVariants: function (_this) {
            updateVariantPrio($(_this).find('table.variants'));
        },
        cloneCoupon: function (_this) {
            var $this = $(_this),
                $input = $this.parent().find('input.coupon-clone-count');

            $this.prop('href', $this.prop('href') + $input.val());
        },
        addShippingPackage: function (el) {
            var $this = $(el),
                $tr = $this.parents('tr'),
                $pallett = $tr.find('.pallett:last'),
                $weights = $tr.find('.weights:last'),
                $dimensions = $tr.find('.dimensions:last'),
                index = $tr.find('.dimensions').length;

            var __dimensions = $dimensions.clone(),
                __weights = $weights.clone(),
                __pallett = $pallett.clone();

            __dimensions.find('input').val('').each(function () {
                cloneInput(this, index);
            });
            __weights.find('input').val('').each(function () {
                cloneInput(this, index);
            });
            __pallett.find('input').removeAttr('checked').each(function () {
                cloneInput(this, index);
            });

            $dimensions.after(__dimensions);
            $pallett.after(__pallett);
            $weights.after(__weights);

            return false;
        },
        selectFunctionListItem: function (_this) {
            var $this = $(_this),
                $li = $this.parents('li');

            $li.toggleClass('active');

            $.ajax({
                url: rex.simpleshop.ajax_url,
                method: 'GET',
                data: {
                    'debug': rex.debug,
                    'id': _this.value,
                    'cat_id': functionListParams.cat_id,
                    'action': $li.hasClass('active') ? 'add' : 'remove',
                    'controller': 'Product.be_toggleRexCategoryId',
                    'rex-api-call': 'simpleshop_be_api',
                }
            }).done(function (resp) {
            });
        },
        showFunctionListItems: function (_this, type) {
            if (type === 'search') {
                if (searchHandle) {
                    window.clearTimeout(searchHandle);
                }
                searchHandle = window.setTimeout(function () {
                    functionListParams.search = _this.value;
                    functionListParams.page = 0;
                    showFunctionList();
                }, 800);
            }
            else if (type === 'paging') {
                var page = $(_this).data('page');

                if (page !== '') {
                    functionListParams.page = $(_this).data('page');
                    showFunctionList();
                }
            }
        }
    };
})(jQuery);