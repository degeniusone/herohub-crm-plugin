jQuery(document).ready(function($) {
    'use strict';

    // Initialize datepickers for date fields
    $('.herohub-date-picker').datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true
    });

    // Handle dynamic phone number formatting
    $('.herohub-phone-field').on('input', function() {
        let phone = $(this).val().replace(/\D/g, '');
        if (phone.length >= 10) {
            phone = phone.match(/(\d{3})(\d{3})(\d{4})/);
            $(this).val('(' + phone[1] + ') ' + phone[2] + '-' + phone[3]);
        }
    });

    // Handle dynamic price formatting
    $('.herohub-price-field').on('input', function() {
        let price = $(this).val().replace(/[^\d.]/g, '');
        if (price) {
            price = parseFloat(price).toLocaleString('en-US', {
                style: 'currency',
                currency: 'USD'
            });
            $(this).val(price);
        }
    });

    // Confirmation for deleting activities
    $('.delete-activity').on('click', function(e) {
        if (!confirm(herohubCRM.confirmDelete)) {
            e.preventDefault();
        }
    });

    // AJAX contact search
    let searchTimeout;
    $('#contact-search').on('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = $(this).val();
        
        if (searchTerm.length < 3) return;

        searchTimeout = setTimeout(function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'herohub_search_contacts',
                    nonce: herohubCRM.nonce,
                    term: searchTerm
                },
                success: function(response) {
                    if (response.success) {
                        $('#contact-search-results').html(response.data);
                    }
                }
            });
        }, 500);
    });

    // Handle activity type selection
    $('#activity-type').on('change', function() {
        const type = $(this).val();
        $('.activity-type-fields').hide();
        $('#' + type + '-fields').show();
    });

    // Initialize tooltips
    $('.herohub-tooltip').tooltip();

    // Handle bulk actions
    $('#doaction, #doaction2').on('click', function(e) {
        const action = $(this).prev('select').val();
        if (action === 'delete') {
            if (!confirm(herohubCRM.confirmBulkDelete)) {
                e.preventDefault();
            }
        }
    });
});
