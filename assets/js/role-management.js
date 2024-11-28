jQuery(document).ready(function($) {
    $('.herohub-manager-select').on('change', function() {
        const $select = $(this);
        const agentId = $select.data('agent-id');
        const managerId = $select.val();

        $.ajax({
            url: heroHubRoles.ajaxUrl,
            type: 'POST',
            data: {
                action: 'assign_agent_to_manager',
                nonce: heroHubRoles.nonce,
                agent_id: agentId,
                manager_id: managerId
            },
            success: function(response) {
                if (response.success) {
                    alert(heroHubRoles.messages.assignSuccess);
                } else {
                    alert(heroHubRoles.messages.assignError + ': ' + response.data);
                    $select.val(''); // Reset selection on error
                }
            },
            error: function() {
                alert(heroHubRoles.messages.assignError);
                $select.val(''); // Reset selection on error
            }
        });
    });
});
