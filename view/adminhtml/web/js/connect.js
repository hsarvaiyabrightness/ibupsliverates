define([
    "jquery",
    "Magento_Ui/js/modal/alert",
    "mage/translate",
    "jquery/ui",
    'mage/validation'
], function ($, alert, $t) {
    "use strict";

    $.widget('upsliverates.connect', {
        options: {
            ajaxUrl: '',
            activateBtn: '#ups_dashboard_liverates_addonconnection'
        },
        _create: function () {
            var self = this;

            $(this.options.activateBtn).click(function (e) {
                self._ajaxSubmitLiveratesAddon();
            });

            $(document).on("click","#upsaddonlrdeactivate",function() {
                if(confirm($.mage.__('Do you want to disable live rates?'))) {
                    self._ajaxDeactivateLiveratesAddon();
                }
            });

            $(document).on("click","#upsaddonlrremove",function() {
                if(confirm($.mage.__('Do you want to remove live rates?'))) {
                    self._ajaxRemoveLiveratesAddon();
                }
            });

            $(document).on("click","#upsaddonlrreactivate",function() {
                if(confirm($.mage.__('Do you want to enable live rates?'))) {
                    self._ajaxEnableLiveratesAddon();
                }
            });

            $(document).on("click","#upsaddonlrreconn",function() {
                if(confirm($.mage.__('Do you want to re-connect live rates?'))) {
                    self._ajaxReconnectLiveratesAddon();
                }
            });
        },

        _ajaxReconnectLiveratesAddon: function () {
            $.ajax({
                url: this.options.ajaxUrl,
                data: {
                    store_id: $('#store_id').val(),
                    conn_status: $('#conn_status').val(),
                    integration_name: $('#integration_name').val(),
                    type: 'upsliverates_reconnect'
                },
                dataType: 'json',
                showLoader: true,
                success: function (result) {
                    if ( result.type == 'addon_connection_error') {
                        alert({
                            title: result.message,
                            content: result.data,
                            actions: {
                                always: function(){}
                            }
                        });
                    } else {
                        if (result.status == true && result.type == "addon_success") {
                            
                            var addonActiveMsgSuccess = $.mage.__('Connected Successfully');
                            $('#ups_dashboard_liverates table').before('<div class="messages msg_upsdashboard_active"><div class="message message-success"><div data-ui-id="messages-message-success">'+addonActiveMsgSuccess+'</div></div></div>');
                            $('#row_ups_dashboard_liverates_addonconnection').hide();

                            var deactiveAddonBtn = $.mage.__('Deactive Live Rates');
                            var removeAddonBtn = $.mage.__('Remove Live Rates');
                            $('#row_ups_dashboard_liverates_addonconnection').after('<tr><td><button class="action-default ui-button ui-corner-all ui-widget action- scalable action-secondary " id="upsaddonlrdeactivate">'+deactiveAddonBtn+'</button></td><td></td><td><button class="action-default ui-button ui-corner-all ui-widget action- scalable action-detaule" id="upsaddonlrremove">'+removeAddonBtn+'</button></td></tr>');
                                                        
                            $(location).prop('href', result.redirect_url);

                        } else if (result.status == true && result.type == "reactive") {

                                var addonActiveMsgSuccess = $.mage.__('UPS shipping live rate service disabled');
                                var reactiveAddonBtn = $.mage.__('Activate Live Rate');
                                $('#ups_dashboard_liverates table').before('<div class="messages msg_upsdashboard_active"><div class="message message-success"><div data-ui-id="messages-message-success">'+addonActiveMsgSuccess+'</div></div></div>');
                                $('#row_ups_dashboard_liverates_addonconnection').hide();
                                $('#row_ups_dashboard_liverates_addonconnection').after('<tr><td><button class="action-default ui-button ui-corner-all ui-widget action- scalable action-primary " id="upsaddonlrreactivate">'+reactiveAddonBtn+'</button></td></tr>');
                            
                        } else if (result.status == true && result.type == "pending") {

                            var addonActiveMsgSuccess = $.mage.__('Connecting to UPS Shipping Live Rate Service. Please Wait...');
                            $('#ups_dashboard_liverates table').before('<div class="messages msg_upsdashboard_active"><div class="message message-success"><div data-ui-id="messages-message-success">'+addonActiveMsgSuccess+'</div></div></div>');
                            $('#row_ups_dashboard_liverates_addonconnection').hide();
                            $(location).prop('href', result.redirect_url);
                        } else if (result.error == true) {
                            alert({
                                title: result.message,
                                content: result.data,
                                actions: {
                                    always: function(){}
                                }
                            });
                        }
                    }
                }
            });     
        },

        _ajaxEnableLiveratesAddon: function () {
            $.ajax({
                url: this.options.ajaxUrl,
                data: {
                    store_id: $('#store_id').val(),
                    conn_status: $('#conn_status').val(),
                    integration_name: $('#integration_name').val(),
                    type: 'upsliverates_reactive'
                },
                dataType: 'json',
                showLoader: true,
                success: function (result) {
                    location.reload();
                }
            });     
        },

        _ajaxRemoveLiveratesAddon: function () {
            $.ajax({
                url: this.options.ajaxUrl,
                data: {
                    store_id: $('#store_id').val(),
                    conn_status: $('#conn_status').val(),
                    integration_name: $('#integration_name').val(),
                    type: 'upsliverates_delete'
                },
                dataType: 'json',
                showLoader: true,
                success: function (result) {
                    location.reload();
                }
            });     
        },
        
        _ajaxDeactivateLiveratesAddon: function () {
            $.ajax({
                url: this.options.ajaxUrl,
                data: {
                    store_id: $('#store_id').val(),
                    conn_status: $('#conn_status').val(),
                    integration_name: $('#integration_name').val(),
                    type: 'upsliverates_deactivate'
                },
                dataType: 'json',
                showLoader: true,
                success: function (result) {
                    location.reload();
                }
            });     
        },

        _ajaxSubmitLiveratesAddon: function () {

            $.ajax({
                url: this.options.ajaxUrl,
                data: {
                    store_id: $('#store_id').val(),
                    conn_status: $('#conn_status').val(),
                    integration_name: $('#integration_name').val(),
                    type: 'addon_connect'
                },
                dataType: 'json',
                showLoader: true,
                success: function (result) {
                    if ( result.type == 'addon_connection_error') {
                        alert({
                            title: result.message,
                            content: result.data,
                            actions: {
                                always: function(){}
                            }
                        });
                    } else {
                        if (result.status == true && result.type == "addon_success") {
                            
                            var addonActiveMsgSuccess = $.mage.__('Connected Successfully');
                            $('#ups_dashboard_liverates table').before('<div class="messages msg_upsdashboard_active"><div class="message message-success"><div data-ui-id="messages-message-success">'+addonActiveMsgSuccess+'</div></div></div>');
                            $('#row_ups_dashboard_liverates_addonconnection').hide();

                            var deactiveAddonBtn = $.mage.__('Deactive Live Rates');
                            var removeAddonBtn = $.mage.__('Remove Live Rates');
                            $('#row_ups_dashboard_liverates_addonconnection').after('<tr><td><button class="action-default ui-button ui-corner-all ui-widget action- scalable action-secondary " id="upsaddonlrdeactivate">'+deactiveAddonBtn+'</button></td><td></td><td><button class="action-default ui-button ui-corner-all ui-widget action- scalable action-detaule" id="upsaddonlrremove">'+removeAddonBtn+'</button></td></tr>');
                                                        
                            $(location).prop('href', result.redirect_url);

                        } else if (result.status == true && result.type == "reactive") {

                            var addonActiveMsgSuccess = $.mage.__('UPS shipping live rate service disabled');
                            var reactiveAddonBtn = $.mage.__('Activate Live Rate');
                            $('#ups_dashboard_liverates table').before('<div class="messages msg_upsdashboard_active"><div class="message message-success"><div data-ui-id="messages-message-success">'+addonActiveMsgSuccess+'</div></div></div>');
                            $('#row_ups_dashboard_liverates_addonconnection').hide();
                            $('#row_ups_dashboard_liverates_addonconnection').after('<tr><td><button class="action-default ui-button ui-corner-all ui-widget action- scalable action-primary " id="upsaddonlrreactivate">'+reactiveAddonBtn+'</button></td></tr>');
                        
                        } else if (result.status == true && result.type == "pending") {

                            var addonActiveMsgSuccess = $.mage.__('Connecting to UPS Shipping Live Rate Service. Please Wait...');
                            $('#ups_dashboard_liverates table').before('<div class="messages msg_upsdashboard_active"><div class="message message-success"><div data-ui-id="messages-message-success">'+addonActiveMsgSuccess+'</div></div></div>');
                            $('#row_ups_dashboard_liverates_addonconnection').hide();
                            $(location).prop('href', result.redirect_url);
                        } else if (result.error == true) {
                            alert({
                                title: result.message,
                                content: result.data,
                                actions: {
                                    always: function(){}
                                }
                            });
                        }
                    }
                }
            });
        }
    });
    
    var hidefields = {
        activeaddon: $('#row_ups_dashboard_liverates_addonconnection')
    };

    var dashboardactiveMessage = $.mage.__('Please Activate UPS Core');
    var addonActiveMsg = $.mage.__('Connected Successfully');

    if ($('#conn_status').val() == '1') {
        $.each(hidefields, function(key, value) {
            value.show();
        });

        if ($('#ups_dashboard_liverates_addonconnection').attr('addonstatus') == 'connected') {
            var addonActiveMsgSuccess = $.mage.__('Connected Successfully');
            $('#ups_dashboard_liverates table').before('<div class="messages msg_upsdashboard_active"><div class="message message-success"><div data-ui-id="messages-message-success">'+addonActiveMsgSuccess+'</div></div></div>');
            $('#row_ups_dashboard_liverates_addonconnection').hide();

            var deactiveAddonBtn = $.mage.__('Deactive Live Rates');
            var removeAddonBtn = $.mage.__('Remove Live Rates');
            $('#row_ups_dashboard_liverates_addonconnection').after('<tr><td><button class="action-default ui-button ui-corner-all ui-widget action- scalable action-secondary " id="upsaddonlrdeactivate">'+deactiveAddonBtn+'</button></td><td></td><td><button class="action-default ui-button ui-corner-all ui-widget action- scalable action-detaule" id="upsaddonlrremove">'+removeAddonBtn+'</button></td></tr>');
            
        }

        if ($('#ups_dashboard_liverates_addonconnection').attr('addonstatus') == 'reactive') {
            
            var addonActiveMsgSuccess = $.mage.__('UPS shipping live rate service disabled');
            $('#ups_dashboard_liverates table').before('<div class="messages msg_upsdashboard_active"><div class="message message-error"><div data-ui-id="messages-message-success">'+addonActiveMsgSuccess+'</div></div></div>');
            $('#row_ups_dashboard_liverates_addonconnection').hide();

            var reactiveAddonBtn = $.mage.__('Activate Live Rate');
            $('#row_ups_dashboard_liverates_addonconnection').after('<tr><td><button class="action-default ui-button ui-corner-all ui-widget action- scalable action-primary " id="upsaddonlrreactivate">'+reactiveAddonBtn+'</button></td></tr>');
        }

        if ($('#ups_dashboard_liverates_addonconnection').attr('addonstatus') == 'session_expired') {
            $('#row_ups_dashboard_liverates_addonconnection').hide();
            var reactiveReconAddonBtn = $.mage.__('Reconnect Live Rates');
            $('#row_ups_dashboard_liverates_addonconnection').after('<tr><td><button class="action-default ui-button ui-corner-all ui-widget action- scalable action-primary " id="upsaddonlrreconn">'+reactiveReconAddonBtn+'</button></td></tr>');

            var addonActiveMsgSession = $.mage.__('Live Rates Session Expired');
            $('#ups_dashboard_liverates table').before('<div class="messages msg_upsdashboard_sessionexpired"><div class="message message-warning"><div data-ui-id="messages-message-warning">'+addonActiveMsgSession+'</div></div></div>');

        }

        if ($('#ups_dashboard_liverates_addonconnection').attr('addonstatus') == 'pending') {
           $('#row_ups_dashboard_liverates_addonconnection').hide();
           var addonActiveMsgSuccess = $.mage.__('Connecting to UPS Shipping Live Rate Service. Please Wait...');
           $('#ups_dashboard_liverates table').before('<div class="messages msg_upsdashboard_active"><div class="message message-success"><div data-ui-id="messages-message-success">'+addonActiveMsgSuccess+'</div></div></div>');
        }

    }else{
        $.each(hidefields, function(key, value) {
            value.hide();
        });
        $('#ups_dashboard_liverates-head').append('<div class="messages msg_upsdashboard_active"><div class="message message-warning"><div data-ui-id="messages-message-warning">'+dashboardactiveMessage+'</div></div></div>');
    }

    return $.upsliverates.connect;
});
