const $ = jQuery;

function render_issue_list(data) {
    const issue_list = document.getElementById("intcp_issue_list");
    issue_list.innerHTML = "";
    data.forEach(element => {
        let li = document.createElement('li');
        li.appendChild(document.createTextNode(element.title + ' '));
        const button = document.createElement('button');
        button.appendChild(document.createTextNode("X"));
        button.addEventListener('click', () => {
            delete_assignment(parseInt(element.issue_id));
        });
        li.appendChild(button);
        issue_list.appendChild(li);
    });
}

function delete_assignment(issue_id) {
    $.post(intcp_issue_apply_magazine_meta_box_obj.url, {
        action: 'intcp_issue_ajax_delete_issue',
        intcp_issue_field_value: issue_id,
        post_ID: jQuery('#post_ID').val()
    }, function (data) {
        if(data.success) {
            render_issue_list(data.data);
        } 
    });
}

/*jslint browser: true, plusplus: true */
(function ($, window, document) {
    'use strict';
    // execute when the DOM is ready
    $(document).ready(function () {
        // js 'change' event triggered on the wporg_field form field
        $('#intcp_magazine').on('change', function () {
            // jQuery post method, a shorthand for $.ajax with POST
            $.post(intcp_issue_apply_magazine_meta_box_obj.url,                        // or ajaxurl
                   {
                       action: 'intcp_magazine_ajax_change',                // POST data, action
                       intcp_magazine_field_value: $('#intcp_magazine').val(), // POST data, wporg_field_value
                       post_ID: jQuery('#post_ID').val()           // The ID of the post currently being edited
                   }, function (data) {
                        if(data.success) {
                            document.getElementById("intcp_issues").innerHTML = "";
                            console.log(data.data)
                            data.data.forEach(element => {
                                console.log(element)
                                let opt = document.createElement("option");
                                opt.setAttribute('value', element.id);
                                opt.appendChild(document.createTextNode(element.title));
                                document.getElementById("intcp_issues").appendChild(opt);
                            });
                        } 
                    }
            );
        });
        $('#intcp_add_button').on('click', function () {
            // jQuery post method, a shorthand for $.ajax with POST
            $.post(intcp_issue_apply_magazine_meta_box_obj.url,                        // or ajaxurl
                   {
                       action: 'intcp_issue_ajax_add_issue',                // POST data, action
                       intcp_issue_field_value: $('#intcp_issues').val(), // POST data, wporg_field_value
                       intcp_issue_field_value_position: $('#intcp_position_input').val(),
                       post_ID: jQuery('#post_ID').val()           // The ID of the post currently being edited
                   }, function (data) {
                        if(data.success) {
                            render_issue_list(data.data);
                        } 
                    }
            );
        });
    });
}(jQuery, window, document));