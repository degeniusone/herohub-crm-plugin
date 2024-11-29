jQuery(document).ready(function($) {
    'use strict';

    // Initialize tooltips
    $('[data-tooltip]').each(function() {
        $(this).tooltip({
            content: $(this).data('tooltip'),
            position: { my: 'left center', at: 'right+10 center' }
        });
    });

    // Refresh dashboard data periodically
    function refreshDashboardData() {
        if ($('.herohub-dashboard').length) {
            $.ajax({
                url: herohubAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'herohub_refresh_dashboard',
                    nonce: herohubAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update system overview
                        $('.stat-value').each(function() {
                            const key = $(this).data('key');
                            if (response.data[key]) {
                                $(this).text(response.data[key]);
                            }
                        });

                        // Update recent activity
                        if (response.data.activities) {
                            const activityList = $('.activity-list');
                            activityList.empty();
                            response.data.activities.forEach(function(activity) {
                                activityList.append(`
                                    <div class="activity-item">
                                        <span class="activity-time">${activity.time_ago}</span>
                                        <span class="activity-text">${activity.description}</span>
                                    </div>
                                `);
                            });
                        }

                        // Update performance stats
                        if (response.data.performance) {
                            $('.performance-value').each(function() {
                                const key = $(this).data('key');
                                if (response.data.performance[key]) {
                                    $(this).text(response.data.performance[key]);
                                }
                            });
                        }

                        // Update top performers
                        if (response.data.performers) {
                            const performersList = $('.performers-list');
                            performersList.empty();
                            response.data.performers.forEach(function(performer) {
                                performersList.append(`
                                    <div class="performer-item">
                                        <span class="performer-name">${performer.name}</span>
                                        <span class="performer-stats">
                                            <span class="deals">${performer.deals_closed} deals</span>
                                            <span class="revenue">${performer.revenue}</span>
                                        </span>
                                    </div>
                                `);
                            });
                        }
                    }
                }
            });
        }
    }

    // Refresh dashboard every 5 minutes
    if ($('.herohub-dashboard').length) {
        setInterval(refreshDashboardData, 300000);
    }

    // Handle responsive menu toggle
    $('.menu-toggle').on('click', function(e) {
        e.preventDefault();
        $('.herohub-menu').toggleClass('active');
    });

    // Handle collapsible cards
    $('.dashboard-card h2').on('click', function() {
        $(this).closest('.dashboard-card').find('.card-content').slideToggle();
        $(this).toggleClass('collapsed');
    });

    // Handle date range picker if present
    if ($.fn.daterangepicker) {
        $('.date-range-picker').daterangepicker({
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            alwaysShowCalendars: true,
            startDate: moment().subtract(29, 'days'),
            endDate: moment()
        }, function(start, end) {
            refreshDashboardData();
        });
    }

    // Handle export functionality
    $('.export-data').on('click', function(e) {
        e.preventDefault();
        const type = $(this).data('type');
        
        $.ajax({
            url: herohubAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'herohub_export_data',
                type: type,
                nonce: herohubAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = response.data.download_url;
                }
            }
        });
    });

    // Handle settings form submission
    $('#herohub-settings-form').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitButton = $form.find('button[type="submit"]');
        
        $submitButton.prop('disabled', true);
        
        $.ajax({
            url: herohubAdmin.ajaxurl,
            type: 'POST',
            data: $form.serialize() + '&action=herohub_save_settings&nonce=' + herohubAdmin.nonce,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    const $message = $('<div class="notice notice-success"><p>Settings saved successfully!</p></div>');
                    $form.before($message);
                    setTimeout(function() {
                        $message.fadeOut(function() {
                            $(this).remove();
                        });
                    }, 3000);
                }
            },
            complete: function() {
                $submitButton.prop('disabled', false);
            }
        });
    });
});
