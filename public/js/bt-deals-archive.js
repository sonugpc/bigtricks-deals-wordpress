/**
 * Deals Archive Filtering JavaScript
 *
 * @package Bigtricks_Deals
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize filters
        initDealsFilters();
        initSidebarFilters();
    });

    function initDealsFilters() {
        var $applyBtn = $('#bt-apply-filters');
        var $clearBtn = $('#bt-clear-filters');
        var $grid = $('#bt-deals-grid');
        var $loadMoreWrapper = $('.bt-deals-load-more-wrapper');
        var isLoading = false;

        // Apply filters on button click
        $applyBtn.on('click', function(e) {
            e.preventDefault();
            applyFilters();
        });

        // Clear filters
        $clearBtn.on('click', function(e) {
            e.preventDefault();
            clearFilters();
        });

        // Search on enter key
        $('#bt-search').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                applyFilters();
            }
        });



        // View toggle (for future enhancement)
        $('.bt-view-btn').on('click', function() {
            $('.bt-view-btn').removeClass('active');
            $(this).addClass('active');
            // Could implement grid/list view toggle here
        });
    }

    function initSidebarFilters() {
        // Sidebar search
        $('#bt-search-btn').on('click', function() {
            applyFilters();
        });

        $('#bt-sidebar-search').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                applyFilters();
            }
        });

        // Price filter apply button
        $('#bt-apply-price-filter').on('click', function() {
            applyFilters();
        });

        // Filter panel toggles
        $('.bt-filter-toggle').on('click', function() {
            var target = $(this).data('target');
            var $content = $('#' + target + '-content');
            var $toggle = $(this);

            $content.slideToggle(200, function() {
                $toggle.toggleClass('rotated');
            });
        });

        // Auto-apply filters when checkboxes change
        $('input[name="bt-category-filter"], input[name="bt-store-filter"]').on('change', function() {
            // Debounce the filter application
            clearTimeout(window.filterTimeout);
            window.filterTimeout = setTimeout(function() {
                applyFilters();
            }, 500);
        });

        // Clear all sidebar filters
        $('#bt-sidebar-clear-filters').on('click', function(e) {
            e.preventDefault();
            clearSidebarFilters();
        });
    }

    function applyFilters() {
        var $grid = $('#bt-deals-grid');
        var $loadMoreWrapper = $('.bt-deals-load-more-wrapper');
        var isLoading = false;

        if (isLoading) return;

        var filters = getFilterData();

        // Show loading state on any available apply button
        var $applyBtn = $('#bt-apply-filters');
        if ($applyBtn.length) {
            $applyBtn.prop('disabled', true).text('Filtering...');
        }

        isLoading = true;

        // AJAX request
        $.ajax({
            url: btDealsAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'filter_deals',
                nonce: btDealsAjax.nonce,
                filters: filters
            },
            success: function(response) {
                if (response.success) {
                    // Update grid
                    $grid.html(response.data.html);

                    // Update load more button if exists
                    if (response.data.load_more) {
                        $loadMoreWrapper.html(response.data.load_more);
                    } else {
                        $loadMoreWrapper.empty();
                    }

                    // Update results count
                    updateResultsCount(response.data.count || 0);
                } else {
                    $grid.html('<p>No deals found matching your criteria.</p>');
                    $loadMoreWrapper.empty();
                    updateResultsCount(0);
                }
            },
            error: function() {
                $grid.html('<p>Error loading deals. Please try again.</p>');
                $loadMoreWrapper.empty();
                updateResultsCount(0);
            },
            complete: function() {
                if ($applyBtn.length) {
                    $applyBtn.prop('disabled', false).text('Apply Filters');
                }
                isLoading = false;
            }
        });
    }

    function clearFilters() {
        // Clear old style filters
        $('#bt-search').val('');
        $('#bt-category').val('');
        $('#bt-store').val('');
        $('#bt-min-price').val('');
        $('#bt-max-price').val('');

        // Clear sidebar filters
        clearSidebarFilters();

        // Reload all deals
        applyFilters();
    }

    function clearSidebarFilters() {
        // Clear search
        $('#bt-sidebar-search').val('');

        // Uncheck all checkboxes
        $('input[name="bt-category-filter"], input[name="bt-store-filter"]').prop('checked', false);

        // Clear price inputs
        $('#bt-sidebar-min-price, #bt-sidebar-max-price').val('');
    }

    function getFilterData() {
        // Get selected categories
        var categories = [];
        $('input[name="bt-category-filter"]:checked').each(function() {
            categories.push($(this).val());
        });

        // Get selected stores
        var stores = [];
        $('input[name="bt-store-filter"]:checked').each(function() {
            stores.push($(this).val());
        });

        // Get search term (prefer sidebar search if available)
        var searchTerm = $('#bt-sidebar-search').val() || $('#bt-search').val() || '';

        return {
            search: searchTerm.trim(),
            categories: categories,
            stores: stores,
            min_price: $('#bt-sidebar-min-price').val() || $('#bt-min-price').val() || '',
            max_price: $('#bt-sidebar-max-price').val() || $('#bt-max-price').val() || ''
        };
    }

    function updateResultsCount(count) {
        var $resultsText = $('#bt-results-text');
        if ($resultsText.length) {
            var text = count === 1 ? '1 Deal' : count + ' Deals';
            $resultsText.text(text);
        }
    }

    // Handle load more for filtered results
    $(document).on('click', '.bt-deals-load-more', function(e) {
        e.preventDefault();

        var $btn = $(this);
        var page = parseInt($btn.data('page'));
        var maxPages = parseInt($btn.data('max-pages'));
        var atts = $btn.data('atts');
        var filters = getCurrentFilters();

        if (page > maxPages) return;

        $btn.prop('disabled', true).text('Loading...');

        $.ajax({
            url: btDealsAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'load_more_deals',
                nonce: btDealsAjax.nonce,
                page: page,
                atts: atts,
                filters: filters
            },
            success: function(response) {
                if (response.success) {
                    $('#bt-deals-grid').append(response.data);

                    if (page < maxPages) {
                        $btn.data('page', page + 1).prop('disabled', false).text('Load More Deals');
                    } else {
                        $btn.remove();
                    }
                } else {
                    $btn.prop('disabled', false).text('No More Deals');
                }
            },
            error: function() {
                $btn.prop('disabled', false).text('Error - Try Again');
            }
        });
    });

    function getCurrentFilters() {
        // Get selected categories
        var categories = [];
        $('input[name="bt-category-filter"]:checked').each(function() {
            categories.push($(this).val());
        });

        // Get selected stores
        var stores = [];
        $('input[name="bt-store-filter"]:checked').each(function() {
            stores.push($(this).val());
        });

        // Get search term (prefer sidebar search if available)
        var searchTerm = $('#bt-sidebar-search').val() || $('#bt-search').val() || '';

        return {
            search: searchTerm.trim(),
            categories: categories,
            stores: stores,
            min_price: $('#bt-sidebar-min-price').val() || $('#bt-min-price').val() || '',
            max_price: $('#bt-sidebar-max-price').val() || $('#bt-max-price').val() || ''
        };
    }

})(jQuery);
