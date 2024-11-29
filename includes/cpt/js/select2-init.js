jQuery(document).ready(function($) {
    // Initialize Select2 for nationality dropdown
    $('.herohub-select2').select2({
        placeholder: 'Select Nationality',
        allowClear: true,
        width: '100%',
        dropdownCssClass: 'herohub-select2-dropdown',
        containerCssClass: 'herohub-select2-container',
        matcher: function(params, data) {
            // Custom matching function for more flexible search
            if ($.trim(params.term) === '') {
                return data;
            }

            // Normalize search term and option text
            var searchTerm = params.term.toLowerCase();
            var text = data.text.toLowerCase();

            // Match from the beginning of words or anywhere in the text
            var matchStart = text.indexOf(searchTerm) === 0;
            var matchAnywhere = text.indexOf(searchTerm) > -1;

            if (matchStart || matchAnywhere) {
                return data;
            }

            return false;
        }
    });
});
