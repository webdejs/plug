jQuery(document).ready(function($) {
    $('#schemacraft_upload_logo_button').click(function(e) {
        e.preventDefault();
        var button = $(this);
        var image_url_field = $('#schemacraft_org_logo');

        var frame = wp.media({
            title: 'Select or Upload Logo',
            button: {
                text: 'Use this logo'
            },
            multiple: false // Set to true if you want to allow multiple image selection
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            image_url_field.val(attachment.url);
        });

        frame.open();
    });
});
