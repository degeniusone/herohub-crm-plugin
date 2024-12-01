jQuery(document).ready(function($) {
    // Gallery upload button
    $('#herohub-gallery-upload-button').on('click', function(e) {
        e.preventDefault();

        // Create media uploader
        var gallery_uploader = wp.media({
            title: 'Select Property Gallery Images',
            button: {
                text: 'Add to Gallery'
            },
            multiple: true
        });

        // When images are selected
        gallery_uploader.on('select', function() {
            var attachments = gallery_uploader.state().get('selection').toJSON();
            
            // Add each selected image to the gallery
            $.each(attachments, function(index, attachment) {
                var image_html = 
                    '<li class="herohub-gallery-image" data-image-id="' + attachment.id + '">' +
                    '<input type="hidden" name="property_gallery_images[]" value="' + attachment.id + '">' +
                    '<img src="' + attachment.sizes.thumbnail.url + '" alt="">' +
                    '<a href="#" class="herohub-remove-gallery-image">&times;</a>' +
                    '</li>';
                
                $('#herohub-gallery-images-list').append(image_html);
            });
        });

        // Open media uploader
        gallery_uploader.open();
    });

    // Remove gallery image
    $(document).on('click', '.herohub-remove-gallery-image', function(e) {
        e.preventDefault();
        $(this).closest('.herohub-gallery-image').remove();
    });

    // Make gallery images sortable
    $('#herohub-gallery-images-list').sortable({
        placeholder: 'herohub-gallery-sortable-placeholder',
        cursor: 'move'
    });
});
