jQuery(document).ready(function($) {
    // Initialize Select2 for all dropdowns
    $('.herohub-select2').each(function() {
        var placeholder = $(this).find('option:first').text();
        $(this).select2({
            placeholder: placeholder,
            allowClear: false,
            width: '100%',
            dropdownCssClass: 'herohub-select2-dropdown',
            containerCssClass: 'herohub-select2-container',
            minimumResultsForSearch: Infinity // Disable search for these dropdowns
        });
    });

    // Special handling for nationality dropdown with search
    $('#contact_nationality').select2({
        placeholder: 'Select Nationality',
        allowClear: false,
        width: '100%',
        dropdownCssClass: 'herohub-select2-dropdown',
        containerCssClass: 'herohub-select2-container',
        minimumResultsForSearch: 0, // Always show search for nationality
        matcher: function(params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }

            var searchTerm = params.term.toLowerCase();
            var text = data.text.toLowerCase();

            var matchStart = text.indexOf(searchTerm) === 0;
            var matchAnywhere = text.indexOf(searchTerm) > -1;

            if (matchStart || matchAnywhere) {
                return data;
            }

            return false;
        }
    });
});
