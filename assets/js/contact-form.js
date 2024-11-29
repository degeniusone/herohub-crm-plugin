jQuery(document).ready(function($) {
    let $form = $('#post');
    let isNewContact = window.location.href.includes('post-new.php');
    let fieldValues = {};

    // Create dialog div if it doesn't exist
    if (!$('#changes-dialog').length) {
        $('body').append('<div id="changes-dialog" style="display:none;"></div>');
    }

    // Update the publish button text
    $('#publish').val(isNewContact ? 'Add Contact' : 'Update Contact');

    // Initialize Select2
    let $nationality = $("#contact_nationality");
    $nationality.select2({
        width: "100%",
        minimumResultsForSearch: 6,
        allowClear: false,
        dropdownCssClass: "herohub-select2-dropdown"
    });

    // Store initial values for all fields
    function storeInitialValues() {
        // Store title (first name)
        fieldValues['post_title'] = {
            label: 'First Name',
            oldValue: $('#title').val(),
            newValue: $('#title').val()
        };

        // Store all fields including nationality
        $('#contact-details input[type="text"], #contact-details textarea, #contact-details select').each(function() {
            let $field = $(this);
            let name = $field.attr('name');
            if (!name) return;

            let label = $field.closest('.herohub-field').find('label').text().replace(':', '');
            let value = $field.is('select') ? $field.find('option:selected').text() : $field.val();

            fieldValues[name] = {
                label: label,
                oldValue: value,
                newValue: value
            };
        });
    }

    // Track all field changes including title and nationality
    $('#title, #contact-details input[type="text"], #contact-details textarea, #contact-details select').on('change input', function() {
        let $field = $(this);
        let name = $field.attr('name') || 'post_title';
        
        if (fieldValues[name]) {
            fieldValues[name].newValue = $field.is('select') ? $field.find('option:selected').text() : $field.val();
        }
    });

    // Get all changes
    function getChanges() {
        let changedFields = [];
        
        for (let name in fieldValues) {
            let field = fieldValues[name];
            if (field.oldValue !== field.newValue) {
                changedFields.push({
                    field: field.label,
                    from: field.oldValue || '(empty)',
                    to: field.newValue || '(empty)'
                });
            }
        }
        
        return changedFields;
    }

    // Show updates dialog
    function showUpdatesDialog(changes) {
        let $dialog = $('#changes-dialog');
        let html = '<div class="changes-list">';
        html += '<h3>Contact Updated</h3>';
        html += '<table class="wp-list-table widefat fixed striped">';
        html += '<thead><tr><th>Field</th><th>Previous Value</th><th>New Value</th></tr></thead><tbody>';
        
        changes.forEach(function(change) {
            html += '<tr>';
            html += '<td>' + change.field + '</td>';
            html += '<td>' + change.from + '</td>';
            html += '<td>' + change.to + '</td>';
            html += '</tr>';
        });
        
        html += '</tbody></table>';
        html += '</div>';

        $dialog.html(html);
        
        try {
            $dialog.dialog({
                title: 'Contact Updated',
                width: 600,
                modal: true,
                buttons: {
                    "Close": function() {
                        $(this).dialog('close');
                    }
                },
                close: function() {
                    // Update old values for next change
                    for (let name in fieldValues) {
                        fieldValues[name].oldValue = fieldValues[name].newValue;
                    }
                    $(this).dialog('destroy');
                }
            });
        } catch (e) {
            console.error('Dialog error:', e);
            alert('Contact updated with ' + changes.length + ' changes.');
        }
    }

    // Handle form submission
    function handleSubmit(e) {
        let changes = getChanges();
        
        if (changes.length > 0) {
            e.preventDefault();
            
            // Store changes in hidden field
            let $changesField = $('<input>').attr({
                type: 'hidden',
                name: '_contact_changes',
                value: JSON.stringify(changes)
            });
            $form.append($changesField);
            
            // Submit form normally
            $form.off('submit', handleSubmit);
            showUpdatesDialog(changes);
            
            setTimeout(function() {
                $form.submit();
            }, 100);
        }
    }

    // Initialize
    storeInitialValues();
    $form.on('submit', handleSubmit);
});
