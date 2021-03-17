(function ($) {
    $(document).ready(
        function ($) {

            // Initialize some variables
            var nonce = mz_mindbody_access.login_nonce,
            // Shortcode atts for current page.
            atts = mz_mindbody_access.atts,
            restricted_content = mz_mindbody_access.restricted_content,
            membership_types = atts.membership_types,
            purchase_types = atts.purchase_types,
            contract_types = atts.contract_types,
            number_of_mbo_log_access_checks = 0,
            siteID = mz_mindbody_access.siteID;
            
            var mz_mindbody_access_state = {

                logged_in: (mz_mindbody_access.logged_in == 1) ? true : false,
                action: undefined,
                target: undefined,
                siteID: undefined,
                nonce: undefined,
                has_access: mz_mindbody_access.has_access,
                content: undefined,
                alert_class: undefined,
                spinner: '<div class="d-flex justify-content-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>',
                content_wrapper: '<div id="mzAccessContainer"></div>',
                notice_box: $('#mboAccessNotice').html(),
                notice: undefined,
                footer: '<div class="modal__footer" id="loginFooter">\n' +
                '    <a href="https://clients.mindbodyonline.com/ws.asp?&amp;studioid='+siteID+'>" class="btn btn-primary" id="MBOSite">Visit Mindbody Site</a>\n' +
                '    <a class="btn btn-primary" id="MBOLogout">Logout</a>\n' +
                '</div>\n',
                header: undefined,
                message: undefined,
                client_first_name: undefined,
                client_id: undefined,

                login_form: $('#mzLogInContainer').html(),
            
                access_container: $('#mzAccessContainer').html(),
            
                initialize: function (target) {
                    this.target = $(target).attr("href");
                    this.siteID = $(target).attr('data-siteID');
                    this.nonce = $(target).attr("data-nonce");
                }
            };
        
            /*
            * Render inner content of content wrapper based on state
            */
            function render_mbo_access_activity()
            {
                // Clear content and content wrapper
                mz_mindbody_access_state.content = '';
                $('#mzAccessContainer').html = '';
            
                if (mz_mindbody_access_state.action == 'processing') {
            
                    mz_mindbody_access_state.content += mz_mindbody_access_state.spinner;
                
                } else if (mz_mindbody_access_state.action == 'login_failed') {
            
                    mz_mindbody_access_state.content += mz_mindbody_access_state.login_form;
                    mz_mindbody_access_state.content += '<div class="alert alert-warning">' + mz_mindbody_access_state.message + '</div>';
                
                } else if (mz_mindbody_access_state.action == 'redirect') {
            
                    mz_mindbody_access_state.content += '<div class="alert alert-success">' + mz_mindbody_access_state.message + '</div>';
                    mz_mindbody_access_state.content += mz_mindbody_access_state.spinner;
                
                } else if (mz_mindbody_access_state.action == 'logout') {
            
                    mz_mindbody_access_state.content += '<div class="alert alert-info">' + mz_mindbody_access_state.message + '</div>';
                    mz_mindbody_access_state.content += mz_mindbody_access_state.login_form;
                    $('#signupModalFooter').remove();
                
                } else if (mz_mindbody_access_state.action == 'error') {
            
                    mz_mindbody_access_state.content += '<div class="alert alert-danger">' + mz_mindbody_access_state.message + '</div>';
                
                } else if (mz_mindbody_access_state.action == 'denied') {
            
                    mz_mindbody_access_state.content += mz_mindbody_access_state.message;
                    mz_mindbody_access_state.content += mz_mindbody_access_state.footer;
                
                } else if (mz_mindbody_access_state.action == 'granted') {
            
                    mz_mindbody_access_state.content += '<div class="alert alert-success">' + mz_mindbody_access_state.message + '</div>';
                    mz_mindbody_access_state.content += restricted_content;
                    mz_mindbody_access_state.content += mz_mindbody_access_state.footer;
                
                } else {
            
                    // check access
                    mz_mbo_access_check_client_access();
                }
            
                // Render the content to DOM
                if ($('#mzAccessContainer')) {
                    $('#mzAccessContainer').html(mz_mindbody_access_state.content);
                }
            
                // Then reset message
                mz_mindbody_access_state.message = undefined;
            }
           
            /**
            * Sign In to MBO
            */
            $(document).on(
                'submit', 'form[id="mzLogIn"]', function (ev) {
                    ev.preventDefault();
            
                    var form = $(this);
                    var formData = form.serializeArray();
                    var result = { };
                    $.each(
                        $('form').serializeArray(), function () {
                            result[this.name] = this.value;
                        }
                    );

                    $.ajax(
                        {
                            dataType: 'json',
                            url: mz_mindbody_access.ajaxurl,
                            type: form.attr('method'),
                            context: this, // So we have access to form data within ajax results
                            data: {
                                action: 'ajax_login_check_access_permissions',
                                form: form.serialize(),
                                nonce: result.nonce
                            },
                            beforeSend: function () {
                                  mz_mindbody_access_state.action = 'processing';
                                  render_mbo_access_activity();
                            },
                            success: function (json) {
                                var formData = $(this).serializeArray();
                                var result = { };
                                $.each(
                                    $('form').serializeArray(), function () {
                                        result[this.name] = this.value;
                                    }
                                );

                                if (json.type == "success") {
                                    mz_mindbody_access_state.logged_in = true;
                                    mz_mindbody_access_state.client_id = json.client_id;
                                    mz_mindbody_access_state.message = json.logged;
                                    // If there are redirects, this is just a login usage
                                    if ((json.client_access_level === 1) && (!!atts.level_1_redirect)) {
                                                mz_mindbody_access_state.action = 'redirect';
                                                mz_mindbody_access_state.message += 'Redirecting you to the classes page.';
                                                render_mbo_access_activity();
                                                setTimeout(
                                                    function () {
                                                        window.location.href = atts.level_1_redirect}, 3000
                                                );
                                    } else if ((json.client_access_level === 2) && (!!atts.level_2_redirect)) {
                                              mz_mindbody_access_state.action = 'redirect';
                                              mz_mindbody_access_state.message += 'Redirecting you to the members page.';
                                              render_mbo_access_activity();
                                            setTimeout(
                                                function () {
                                                    window.location.href = atts.level_2_redirect}, 3000
                                            );
                                    } else if ((json.client_access_level === 0) && (!!atts.denied_redirect)) {
                                        mz_mindbody_access_state.action = 'redirect';
                                        mz_mindbody_access_state.message += 'Redirecting you to our pricing page.';
                                        render_mbo_access_activity();
                                        setTimeout(
                                            function () {
                                                window.location.href = atts.denied_redirect}, 3000
                                        );
                                    }  else if (atts.access_levels.indexOf(String(json.client_access_level)) != -1) {
                                        // This page contains restricted content
                                        // Check to see if client access level matches one set in shortcode
                                        mz_mindbody_access_state.action = 'granted';
                                        render_mbo_access_activity();                            
                            
                                    } else {
                        
                                        mz_mindbody_access_state.action = 'denied';
                                        mz_mindbody_access_state.message += '</br>';
                                        mz_mindbody_access_state.message += '<div class="alert alert-warning">'  + mz_mindbody_access.atts.denied_message + ':';
                                        mz_mindbody_access_state.message += '<ul>';
                            
                                        if (mz_mindbody_access.required_services && mz_mindbody_access.atts.access_levels) {
                                            for (var i=0; i < mz_mindbody_access.atts.access_levels.length; i++) {
                                                var level = mz_mindbody_access.atts.access_levels[i];
                                                for (var j=0; j < mz_mindbody_access.required_services[level].length; j++) {
                                                    mz_mindbody_access_state.message += '<li>' + mz_mindbody_access.required_services[level][j] + '</li>';
                                                }
                                            }
                                        }
                            
                                        mz_mindbody_access_state.message += '</ul></div>';
                                        render_mbo_access_activity();
                                    }
                        
                                } else {
                                    mz_mindbody_access_state.action = 'login_failed';
                                    mz_mindbody_access_state.message = json.logged;
                                    render_mbo_access_activity();
                                }
                            } // ./ Ajax Success
                        }
                    ) // End Ajax
                    .fail(
                        function (json) {
                            mz_mindbody_access_state.message = 'ERROR LOGGING IN';
                            render_mbo_access_activity();
                            console.log(json);
                        }
                    ); // End Fail

                }
            );
        
            /**
             * Check access permissions
             */
            function mz_mbo_access_check_client_access()
            {
                $.ajax(
                    {
                        dataType: 'json',
                        url: mz_mindbody_access.ajaxurl,
                        context: this, // So we have access to form data within ajax results
                        data: {
                            action: 'ajax_login_check_access_permissions',
                            nonce: mz_mindbody_access.login_nonce
                        },
                        beforeSend: function () {
                            mz_mindbody_access_state.action = 'processing';
                            render_mbo_access_activity();
                        },
                        success: function (json) {
                            if ((json.type == "success") && (atts.access_levels.indexOf(String(json.client_access_level)) != -1)) {
                                mz_mindbody_access_state.logged_in = true;
                                mz_mindbody_access_state.action = 'granted';
                                mz_mindbody_access_state.message = json.message;
                                render_mbo_access_activity();
                            } else {
                                 mz_mindbody_access_state.action = 'denied';
                                 mz_mindbody_access_state.message = json.logged + '<div class="alert alert-warning">' + mz_mindbody_access.atts.denied_message + ' ' + mz_mindbody_access.membership_types + '</div>';
                                 render_mbo_access_activity();
                            }
                        } // ./ Ajax Success
                    }
                ) // End Ajax
                .fail(
                    function (json) {
                         mz_mindbody_access_state.message = 'ERROR CHECKING ACCESS';
                         render_mbo_access_activity();
                         console.log(json);
                    }
                ); // End Fail
            }
        
        
            /**
                   * Logout of MBO
                   */
            $(document).on(
                'click', "#MBOLogout", function (ev) {
                    ev.preventDefault();
                    var nonce = $(this).attr("data-nonce");

                    $.ajax(
                        {
                            dataType: 'json',
                            url: mz_mindbody_access.ajaxurl,
                            data: {action: 'ajaxClientLogOut', nonce: mz_mindbody_access.logout_nonce},
                            beforeSend: function () {
                                mz_mindbody_access_state.action = 'processing';
                                render_mbo_access_activity();
                            },
                            success: function (json) {
                                if (json.type == "success") {
                                    mz_mindbody_access_state.logged_in = false;
                                    mz_mindbody_access_state.action = 'logout';
                                    mz_mindbody_access_state.message = json.message;
                                    render_mbo_access_activity();
                                } else {
                                    mz_mindbody_access_state.action = 'logout_failed';
                                    mz_mindbody_access_state.message = json.message;
                                    render_mbo_access_activity();
                                }
                            } // ./ Ajax Success
                        }
                    ) // End Ajax
                    .fail(
                        function (json) {
                            mz_mindbody_access_state.message = 'ERROR LOGGING OUT';
                            render_mbo_access_activity();
                            console.log(json);
                        }
                    ); // End Fail
                }
            );
    
            /**
             * Continually Check if Client is Logged in and Update Status
             *
             * This asks server to check if session has been set with client info
             */
            setInterval(mz_mbo_check_client_logged, 15000);

            function mz_mbo_check_client_logged( )
            {    
                // Only do this up to 1000 times or so so it's not pinging server all day
                // The count is vague because it's also updated by check_client_access
                number_of_mbo_log_access_checks++;
                if (number_of_mbo_log_access_checks >= 1000) { return;
                }
            
                //this will repeat every 15 seconds
                $.ajax(
                    {
                        dataType: 'json',
                        url: mz_mindbody_access.ajaxurl,
                        data: {action: 'ajaxCheckClientLogged', nonce: 'mz_check_client_logged'},
                        success: function (json) {
                            if (json.type == "success") {
                                mz_mindbody_access_state.logged_in = (json.message == 1 ? true : false);
                            }
                        } // ./ Ajax Success
                    }
                ); // End Ajax
            }
        
    
            /**
             * Check and update Client Access once per hour = 3600000
             */
            setInterval(mz_mbo_update_client_access, 3600000);

            function mz_mbo_update_client_access( )
            {    
                // Only do this up to 250 times or so
                number_of_mbo_log_access_checks++;
                if (number_of_mbo_log_access_checks >= 500) { return;
                }
            
                if (!mz_mindbody_access_state.logged_in) { return;
                }
            
                $.ajax(
                    {
                        dataType: 'json',
                        url: mz_mindbody_access.ajaxurl,
                        context: this, // So we have access to form data within ajax results
                        data: {
                            action: 'ajax_check_access_permissions',
                            nonce: mz_mindbody_access.login_nonce,
                            client_id: mz_mindbody_access.client_id
                        },
                        success: function (json) {
                            if (json.type == "success") {
                                if (mz_mindbody_access_state.has_access == false && atts.access_levels.indexOf(String(json.client_access_level)) != -1) {
                                           mz_mindbody_access_state.has_access = true;
                                           mz_mindbody_access_state.action = 'granted';
                                           mz_mindbody_access_state.message = 'Access Granted.';
                                           render_mbo_access_activity();
                                }
                                if (mz_mindbody_access_state.has_access == true && json.client_access_level == 0) {
                                           mz_mindbody_access_state.has_access = false;
                                           mz_mindbody_access_state.action = 'denied';
                                           mz_mindbody_access_state.message = '<div class="alert alert-warning">' + atts.access_expired + '</div>';
                                           render_mbo_access_activity();
                                }
                            } 
                        } // ./ Ajax Success
                    }
                ) // End Ajax
                .fail(
                    function (json) {
                         mz_mindbody_access_state.message = 'ERROR LOGGING IN';
                         console.log(json);
                    }
                ); // End Fail
            
            }
        }
    );
})(jQuery);