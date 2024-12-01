jQuery(document).ready(function($) {
    const BATCH_SIZE = 100;
    let currentRow = 0;
    let locations = [];
    
    $('#location-file').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            Papa.parse(file, {
                header: true,
                complete: function(results) {
                    locations = results.data;
                    $('#total-rows').text(locations.length);
                    $('#import-form button').prop('disabled', false);
                }
            });
        }
    });

    $('#import-form').on('submit', function(e) {
        e.preventDefault();
        processNextBatch();
    });

    function processNextBatch() {
        if (currentRow >= locations.length) {
            $('#progress').text('Import completed!');
            return;
        }

        const batch = locations.slice(currentRow, currentRow + BATCH_SIZE);
        currentRow += BATCH_SIZE;

        $.ajax({
            url: '/wp-json/herohub/v1/import-locations',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ locations: batch }),
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wpApiSettings.nonce);
            },
            success: function(response) {
                const progress = Math.min(100, (currentRow / locations.length) * 100);
                $('#progress').text(`Processed ${currentRow} of ${locations.length} (${progress.toFixed(1)}%)`);
                processNextBatch();
            },
            error: function(xhr, status, error) {
                console.error('Import error:', error);
                $('#progress').text('Error occurred during import. Check console for details.');
            }
        });
    }
});
