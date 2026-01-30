/**
 * Stock Image Fetcher Pro - Admin Dashboard JavaScript
 */

jQuery(document).ready(function ($) {

    /**
     * Quality Slider
     */
    $('#sifp-quality-slider').on('input', function () {
        $('#sifp-quality-value').text($(this).val());
    });

    /**
     * Source Selector Tab Switching
     */
    $('#sifp_active_source').on('change', function () {
        var selectedSource = $(this).val();
        $('.sifp-api-config-section').removeClass('active');

        // Find the index of the source to show the corresponding section
        var index = $(this).find('option:selected').index();
        $('.sifp-api-config-section').eq(index).addClass('active');
    }).trigger('change');

    /**
     * Test API Connection
     */
    $('.sifp-test-api').on('click', function () {
        var $btn = $(this);
        var $status = $('#sifp-api-status');
        var source = $btn.data('source');
        var apiKey = '';

        if (source !== 'custom') {
            apiKey = $('input[name="sifp_' + source + '_api_key"]').val();

            if (!apiKey) {
                showStatus('Please enter an API key for ' + source + ' first.', 'error');
                return;
            }
        }

        // Update button state
        $btn.prop('disabled', true).html(
            '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="animation: spin 1s linear infinite;"><circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="2" fill="none" opacity="0.3"/><path d="M8 2a6 6 0 016 6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg> Testing...'
        );

        // Active source to test
        var sourceType = $('select[name="sifp_active_source"]').val() || 'freepik';

        $.ajax({
            url: stockFetcherProAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'sifp_test_api',
                api_key: apiKey,
                source_type: source,
                nonce: stockFetcherProAdmin.nonce
            },
            success: function (response) {
                $btn.prop('disabled', false).html(
                    '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 2v12M2 8h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg> Test Connection'
                );

                if (response.success) {
                    showStatus(response.data, 'success');
                } else {
                    showStatus(response.data, 'error');
                }
            },
            error: function () {
                $btn.prop('disabled', false).html(
                    '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 2v12M2 8h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg> Test Connection'
                );
                showStatus('Network error. Please try again.', 'error');
            }
        });
    });

    /**
     * Show Status Message
     */
    function showStatus(message, type) {
        var $status = $('#sifp-api-status');
        $status
            .removeClass('success error')
            .addClass(type)
            .text(message)
            .slideDown(300);

        setTimeout(function () {
            $status.slideUp(300);
        }, 5000);
    }

    /**
     * Add CSS for spin animation
     */
    if (!document.getElementById('sifp-admin-animations')) {
        var style = document.createElement('style');
        style.id = 'sifp-admin-animations';
        style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
        document.head.appendChild(style);
    }

    /**
     * Animate stat cards on page load
     */
    $('.sifp-stat-card').each(function (index) {
        $(this).css({
            'opacity': '0',
            'transform': 'translateY(20px)'
        });

        setTimeout(function (card) {
            $(card).css({
                'opacity': '1',
                'transform': 'translateY(0)',
                'transition': 'all 0.4s ease-out'
            });
        }, index * 100, this);
    });

    /**
     * Toggle Settings Sections (Future feature)
     */
    $('.sifp-card-header').css('cursor', 'default');
});
