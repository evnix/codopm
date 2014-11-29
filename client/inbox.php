<?php
defined('_JEXEC') or die;
/**
 * @package Component codoPM for Joomla! 3.0
 * @author codologic
 * @copyright (C) 2013 - codologic
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
?>
<script>

    CODOF.hook.add('on_scripts_loaded', function() {

//-----------------------------------------------------------------------------------------
        codopm.conversations = {
            last_date: 0
        };
//-----------------------------------------------------------------------------------------
        codopm.range = {
            previous: "enabled",
            next: "enabled",
            range: {
                from: 0,
                to: 0,
                total: 0
            }
        };
//-----------------------------------------------------------------------------------------
        codopm.no_with_commas = function(no) {
            return no.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        };
//-----------------------------------------------------------------------------------------
        codopm.boldify = function(no) {

            return "<b>" + codopm.no_with_commas(no) + "</b>";
        };
//-----------------------------------------------------------------------------------------
        codopm.create_range = function(from, to, total) {

            $('.codopm_navigator_navigate').removeClass('codopm_navigator_disabled');
            codopm.range.previous = codopm.range.next = 'enabled';

            if (parseInt(from) <= 1) {
                //disable previous

                codopm.range.previous = 'disabled';
                $('#codopm_inbox_previous').addClass('codopm_navigator_disabled');
            }

            if (parseInt(to) === parseInt(total)) {
                //disable next

                codopm.range.next = 'disabled';
                $('#codopm_inbox_next').addClass('codopm_navigator_disabled');
            }

            codopm.range.range = {
                from: from,
                to: to,
                total: total
            };

            var str = codopm.boldify(from) + "&#8211" + codopm.boldify(to) + " of " + codopm.boldify(total);
            $('#codopm_navigator_range').html(str);
        };
//-----------------------------------------------------------------------------------------
        codopm.range.go_previous = function() {

            if (codopm.range.previous !== 'disabled')
                codopm.fill_inbox("previous");
        };
//-----------------------------------------------------------------------------------------
        codopm.range.go_next = function() {

            if (codopm.range.next !== 'disabled')
                codopm.fill_inbox("next");
        };
//-----------------------------------------------------------------------------------------
        codopm.today = new Date();
//-----------------------------------------------------------------------------------------
        codopm.download_file = function(iname) {

            window.open(codopm.path + "client/uploads/" + iname);
        };
//-----------------------------------------------------------------------------------------
        codopm.conversations.create_conversation = function(id, name, msg, date, attachments) {
            var time = codopm.date.get_readable_time(date);

            var ln = attachments.length, iname, rname,
                    images = '<div class="codopm_inbox_attachment_container">',
                    ext,
                    iexts = ["png", "jpg", "jpeg", "pjpeg", "gif", "bmp"],
                    files = [];


            for (var i = 0; i < ln; i++) {

                iname = attachments[i].uname;
                rname = attachments[i].name;
                if (rname.length > codopm.config.max_filename_len) {
                    rname = rname.substr(0, codopm.config.max_filename_len) + "...";
                }

                ext = iname.substr(iname.lastIndexOf('.') + 1);
                if (iexts.indexOf(ext.toLowerCase()) > -1) {
                    images += "<div class='codopm_inbox_attachment'><a href='" + codopm.path + "client/download.php?filename=" + iname + "'><img src='" + codopm.path + "client/uploads/" + iname + "' /></a><div class='codopm_inbox_attachment_title'>" + rname + "</div></div>";
                } else {
                    files.push(attachments[i]);
                }

            }

            images += '</div>';

            ln = files.length;

            var files_str = '';
            for (var i = 0; i < ln; i++) {

                if (typeof files[i] === "object") {
                    iname = files[i].uname;
                    rname = files[i].name;
                    if (rname.length > codopm.config.max_filename_len) {
                        rname = rname.substr(0, codopm.config.max_filename_len) + "...";
                    }

                    files_str += "<div onclick='codopm.download_file(\'" + iname + "\')' class='codopm_inbox_attachment_file'><a>" + rname + "</a><div class='codopm_inbox_attachment_file_download'><a href='" + codopm.path + "client/uploads/" + iname + "'>download</a></div></div>";
                }
            }

            images += files_str;

            var str = "<div id='codopm_conversation_node_" + id + "' class='codopm_conversation_node'>" +
                       "<div class='codopm_conversation_time'>" + time + "</div>" +
                       "<div onclick='codopm.conversations.delete_conversation(" + id + ")' class='codopm_conversation_delete'>&times;</div>" +
                       "<span class='codopm_conversation_head'>" +
                         "<span class='codopm_conversation_name'>" + name + "</span>" +
                       "</span>" +
                       "<div class='codopm_conversation_message'>" + msg + "</div>" + images +
                       //"</div>" +
                      "</div>";


            return str;
        };
//-----------------------------------------------------------------------------------------
        codopm.conversations.delete_conversation = function(id) {

            $.post(codopm.req_path + 'do=delete_conversation&id=' + codopm.from + '&xhash=' + codopm.xhash,
                    {
                        msg_id: id
                    }
            , function(data) {

                data = JSON.parse(data);

                if (data.has_error == false) {
                    $('#codopm_conversation_node_' + id).remove();

                }
            }
            );
        };
//-----------------------------------------------------------------------------------------
        codopm.conversations.is_latest_today = function() {

            return  codopm.conversations.latest_date.toDateString() === codopm.today.toDateString();
        };
//-----------------------------------------------------------------------------------------
        codopm.conversations.scroll_to_first = function() {

            $("html, body").animate({
                scrollTop: $("#codopm_conversation_content .codopm_conversation_node:first").offset().top
            }, 400);
        };
//-----------------------------------------------------------------------------------------
        codopm.conversations.add_read_more = function() {


            var id = "codopm_msg_offset_" + codopm.conversations.offset;
            var pid = "codopm_pmsg_offset_" + codopm.conversations.offset;

            var str = '<div id=' + pid + ' class="codopm_msg_read_more row">\n\
                            <span id=' + id + '  class="btn inline col-md-8" ><?php codopm::t('Load older messages') ?></span>\n\
                            <span onclick="codopm.conversations.scroll_to_first()" class="btn inline col-md-4"><?php codopm::t('Top') ?></span>\n\
                      </div>';

            $('#codopm_conversation_content').append(str);

            $('#' + id).click(function() {

                codopm.conversations.get_more();
                setTimeout(function() {
                    $('#' + pid).hide();
                }, 500);
            });

        };
//-----------------------------------------------------------------------------------------
        codopm.conversations.create = function(msgs) {

            var len = msgs.length,
                    str = '',
                    today = new Date(),
                    date_sep = false,
                    date, last_date, time, pretty_time;

            if (!len)
                return;

            if (!codopm.conversations.last_date) {
                codopm.conversations.last_date = new Date(msgs[0].time * 1000);
            }

            var first_date = codopm.conversations.last_date;
            var last_date = new Date(msgs[len - 1].time * 1000);


            if (!codopm.date.is_today(first_date, today) || !codopm.date.is_today(last_date, today)) {
                date_sep = true;
            }

            if (!codopm.conversations.latest_date || first_date > codopm.conversations.latest_date) {
                codopm.conversations.latest_date = first_date;
            }

            codopm.conversations.last_date = last_date;

            for (var i = 0; i < len; i++) {

                date = new Date(msgs[i].time * 1000);

                if (date_sep && last_date && !codopm.date.is_today(date, last_date)
                        && date.toDateString().replace(/ /g, "") !== codopm.conversations.magical_sep) {
                    last_date = date;

                    if (codopm.date.is_this_year(today, date)) {

                        //same year
                        pretty_time = codopm.date.get_month_name(date) + " " + date.getDate();
                    } else {

                        //different year
                        // YY/MM/DD
                        pretty_time = date.getFullYear() + "/" + (date.getMonth() + 1) + "/" + date.getDate();
                    }

                    str += "<div class='codopm_date_sep' month='" + codopm.inbox.month + "'>" + pretty_time + "</div>";
                } else {

                    if (!codopm.conversations.magical_sep) {

                        codopm.conversations.magical_sep = date.toDateString().replace(/ /g, "");
                        var mag_id = 'codopm_first_magical_date_' + codopm.conversations.magical_sep;
                        if (codopm.date.is_this_year(today, date)) {

                            //same year
                            pretty_time = codopm.date.get_month_name(date) + " " + date.getDate();
                        } else {

                            //different year
                            // YY/MM/DD
                            pretty_time = date.getFullYear() + "/" + (date.getMonth() + 1) + "/" + date.getDate();
                        }
                        str += "<div style='display:none' id='" + mag_id + "' class='codopm_date_sep' month='" + codopm.inbox.month + "'>" + pretty_time + "</div>";

                    }
                }

                var attachments = JSON.parse(msgs[i].attachments);
                str += codopm.conversations.create_conversation(msgs[i].msg_id, msgs[i].msg_from_name, msgs[i].message, date, attachments);
            }
            return str;
        };
//-----------------------------------------------------------------------------------------
        codopm.conversations.get_more = function() {

            codopm.conversations.offset += codopm.conversations.limit;


            $.get(codopm.req_path + 'do=load_more_conversations&id=' + codopm.from + '&xhash=' + codopm.xhash,
                    {
                        msg_to: codopm.conversations.to,
                        msg_from: codopm.conversations.from,
                        msg_offset: codopm.conversations.offset
                    },
            function(data) {

                data = JSON.parse(data);

                if (data.has_error == false) {

                    var str = codopm.conversations.create(data.conversations);
                    $('#codopm_conversation_content').append(str);

                    if (data.read_more === 'yes')
                        codopm.conversations.add_read_more();

                    if (codopm.conversations.magical_sep) {

                        $('#codopm_first_magical_date_' + codopm.conversations.magical_sep).show();
                    }
                }
                else {
                    console.log("error");
                }

            }

            );

        };
//-----------------------------------------------------------------------------------------
        codopm.date = {
            is_today: function(d1, d2) {
                return (d1.toDateString() === d2.toDateString());
            },
            is_this_year: function(d1, d2) {

                return (d1.getFullYear() === d2.getFullYear());
            },
            get_month_name: function(date) {

                var month = date.getMonth();

                codopm.inbox.month = codopm.inbox.months[month];
                return codopm.inbox.month.substring(0, 3);
            },
            get_readable_time: function(date) {

                var HH = date.getHours();
                var MM = date.getMinutes();

                return this.format_time(HH, MM);
            },
            format_time: function(HH, MM) {

                if (MM < 10) {
                    MM = "0" + MM;
                }

                var period = "AM";
                if (HH > 12) {
                    period = "PM";
                }
                else {
                    period = "AM";
                }
                HH = ((HH > 12) ? HH - 12 : HH);

                return HH + ":" + MM + " " + period;
            }

        };
//-----------------------------------------------------------------------------------------
        codopm.load_conversations = function(to, from) {

            $('.codopm_toolbar').hide();
            $('#codopm_conv_toolbar').show();

            codopm.conversations.to = to;
            codopm.conversations.from = from;


            $.get(codopm.req_path + 'do=get_conversations&id=' + codopm.from + '&xhash=' + codopm.xhash,
                    {
                        msg_to: codopm.conversations.to,
                        msg_from: codopm.conversations.from
                    },
            function(data) {

                data = JSON.parse(data);

                if (data.has_error == false) {

                    codopm.conversations.offset = 0;
                    codopm.conversations.limit = parseInt(data.offset);
                    codopm.conversations.magical_sep = false;
                    $('#codopm_tab_conversations').trigger('click');

                    var str = codopm.conversations.create(data.conversations);
                    $('#codopm_conversation_content').html(str);

                    if (data.read_more === 'yes')
                        codopm.conversations.add_read_more();
                }
                else {
                    console.log("error");
                }

            }

            );
        };
//-----------------------------------------------------------------------------------------
        codopm.inbox = function() {

            codopm.requests.get_messages = 'not started';

            codopm.inbox.months = [
                "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "Novemver", "December"
            ];



            var get_pretty_time = function(today, date) {

                var pretty_time;

                if (codopm.date.is_today(today, date)) {

                    //same day

                    var HH = date.getHours();
                    var MM = date.getMinutes();

                    if (HH === today.getHours() && MM === today.getMinutes()) {

                        //same minute

                        var sec_ago = today.getSeconds() - date.getSeconds();
                        pretty_time = sec_ago + " <?php codopm::t('seconds ago') ?>";

                        pretty_time = (parseInt(pretty_time) === 0) ? "<?php codopm::t('just now') ?>" : pretty_time;
                    } else {

                        pretty_time = codopm.date.format_time(HH, MM);
                    }


                } else if (codopm.date.is_this_year(today, date)) {

                    //same year

                    pretty_time = codopm.date.get_month_name(date) + " " + date.getDate();

                } else {

                    //different year

                    // YY/MM/DD
                    pretty_time = date.getFullYear() + "/" + (date.getMonth() + 1) + "/" + date.getDate();
                }

                return pretty_time;
            };

//-----------------------------------------------------------------------------------------
            codopm.fill_inbox = function(type) {

                if (typeof type === "undefined")
                    type = "default";

                if (type === "default") {
                    codopm.range.range.start = 0;
                }

                $('.codopm_toolbar').hide();
                $('#codopm_inbox_toolbar').show();


                if (codopm.requests.get_messages === 'started')
                    return;

                codopm.requests.get_messages = 'started';

                $.get(codopm.req_path + 'do=get_messages&id=' + codopm.from + '&xhash=' + codopm.xhash,
                        {
                            "range": codopm.range.range,
                            type: type
                        },
                function(data) {

                    data = JSON.parse(data);

                    codopm.create_range(data.start, data.end, data.count);
                    codopm.my_name = data.my_name;

                    if (data.has_error == false) {

                        var messages = data.messages,
                                len = messages.length,
                                str = '',
                                today = new Date(), pretty_time,
                                date, bg_cls, frm_name;

                        if (len === 0)
                            str = '<div class="codopm_nomsgs"><?php codopm::t('No messages found!') ?></div>';

                        for (var i = 0; i < len; i++) {

                            date = new Date(messages[i].time * 1000);

                            pretty_time = get_pretty_time(today, date);


                            var message = '';

                            if (messages[i].message !== null) {
                                message = messages[i].message.substring(0, 50);
                                message = message.replace(/<br\/>/g, " ");
                            }

                            if (messages[i].recd == 1) {

                                bg_cls = 'codopm_inbox_msg_read';
                            } else {
                                bg_cls = '';
                            }

                            if (messages[i].msg_from == codopm.from) {

                                //my message

                                frm_name = messages[i].msg_to_name;
                            } else {

                                //his message

                                frm_name = messages[i].msg_from_name;
                            }

                            str += '<div onclick="codopm.load_conversations(' + messages[i].msg_to + ',' + messages[i].msg_from + ')" class="codopm_inbox_msg ' + bg_cls + '">' +
                                    '<div class="codopm_inbox_from_name">' + frm_name + '</div>' +
                                    '<div class="codopm_inbox_content">' + message + '</div>' +
                                    '<div class="codopm_inbox_time">' + pretty_time + '</div>' +
                                    '</div>';

                        }

                        $('#codopm_inbox').html(str);

                    } else {


                    }
                    codopm.requests.get_messages = 'ended';

                });
            };

            codopm.fill_inbox();


        };
//---------------------------------------------------------------------------------------
        codopm.dom_loaded = function() {

            $('.codopm_reply_box').on('change', '.codopm_default_reply_file', function() {
                codopm.onchange_fileadd($(this), 'codopm_default_reply_file', 'reply');
            });

            $('.codopm_compose_attachments').on('change', '.codopm_default_compose_file', function() {
                codopm.onchange_fileadd($(this), 'codopm_default_compose_file', 'compose');
            });

            codopm.ele = {
                reply_box: $('.codopm_reply_box'),
                reply_btn: $('#codopm_reply_btn'),
                attach_btn: $('#codopm_reply_attach'),
                textarea: $('#codopm_reply_area > textarea'),
            };


            $(document).mouseup(function(e) {

                var container = codopm.ele.reply_box;
                var reply_btn = codopm.ele.reply_btn;
                var attach_btn = codopm.ele.attach_btn;


                if (!container.is(e.target) // if the target of the click isn't the container...
                        && container.has(e.target).length === 0) // ... nor a descendant of the container
                {
                    codopm.ele.textarea.css("height", "26px")
                            .css('min-height', '26px');
                    $('#codopm_reply_toolbar').hide();
                    $('#codopm_reply_attachments_list').hide().removeClass('border-codo-top');
                    $('#codopm_reply_attachments').css('left', '-999em');

                } else {

                    if ($(e.target).is('input'))
                        return false;

                    if (!reply_btn.is(e.target) && !attach_btn.is(e.target)) {

                        codopm.ele.textarea.focus();
                    }

                    if (codopm.ele.textarea.height() < codopm.ele.textarea.css('maxHeight'))
                        codopm.ele.textarea.trigger('autosize.resize');
                    else {

                        codopm.ele.textarea.css('max-height', '300px');
                    }

                    if (codopm.ele.textarea.height() < 60) {

                        codopm.ele.textarea.css('height', '60px')
                                .css('min-height', '60px');
                    }
                    $('#codopm_reply_toolbar').show();
                    $('#codopm_reply_attachments_list').show().addClass('border-codo-top');
                    $('#codopm_reply_attachments').css('left', '0');

                }

            });

            codopm.ele.textarea.autosize().addClass('codopm_textarea_transition');

            $('#codopm_conv_refresh').on("click", function() {
                codopm.load_conversations(codopm.conversations.to, codopm.conversations.from);
            });

            codopm.ele.reply_btn.click(function() {

                var msg = codopm.ele.textarea.val(),
                        to;

                if ($.trim(msg) === "")
                    return;

                if (codopm.from == codopm.conversations.from) {
                    //my message

                    to = codopm.conversations.to;
                } else {

                    to = codopm.conversations.from;
                }
                
                
                msg = msg.replace("<", "&lt;");
                msg = msg.replace("<", "&gt;");
                msg = msg.replace(/\r/g, "<br/>");
                msg = msg.replace(/\r?\n/g, "<br/>");

                var options = {
                    beforeSend: function()
                    {
                        $("#codopm_attachment_progress_r").css('display', 'inline-block');

                        $("#codopm_attachment_bar_r").width('0%');
                        $("#codopm_attachment_percent_r").html("0%");

                    },
                    uploadProgress: function(event, position, total, percentComplete)
                    {
                        $("#codopm_attachment_bar_r").width(percentComplete + '%');
                        $("#codopm_attachment_percent_r").html(percentComplete + '%');
                    },
                    success: function()
                    {
                        $("#codopm_attachment_bar_r").width('100%');
                        $("#codopm_attachment_percent_r").html('100%');
                    },
                    complete: function(data)
                    {

                        data = JSON.parse(data.responseText);
                        if (data.has_error == false) {

                            $('#codopm_reply_area > textarea').val('');


                            var attachments = JSON.parse(data.attachments);
                            var str = codopm.conversations.create_conversation(data.msg_id, codopm.my_name, msg, codopm.today, attachments),
                                    date_sep;

                            if ($('#codopm_conversation_content').find('.codopm_date_sep:first').length > 0) {

                                //date separator exist

                                if (!codopm.conversations.is_latest_today()) {

                                    //latest date separator is not today
                                    date_sep = codopm.date.get_month_name(codopm.today) + " " + codopm.today.getDate();
                                    str = "<div class='codopm_date_sep' month='" + codopm.inbox.month + "'>" + date_sep + "</div>" + str;
                                    $(str).insertBefore('#codopm_conversation_content > .codopm_date_sep:first');

                                    codopm.conversations.latest_date = codopm.today;

                                } else {
                                    $(str).insertAfter('#codopm_conversation_content > .codopm_date_sep:first');
                                }

                            } else {

                                //no date separator
                                $(str).insertBefore('#codopm_conversation_content > .codopm_conversation_node:first');
                            }

                            $('#codopm_reply_attachments_list').html('');
                            $('#codopm_reply_attachments').html('<input class="codopm_default_reply_file" type="file" name="file_0"/>');
                            codopm.files_attached = 0;
                            $("#codopm_attachment_progress_r").hide();
                            $("#codopm_attachment_bar_r").width('0%');
                            $("#codopm_attachment_percent_r").html("0%");
                            codopm.ele.textarea.css('height', '60px')
                                    .css('min-height', '60px');

                        } else {
                            alert(data.msg);

                        }

                    },
                    error: function()
                    {

                    },
                    data: {
                        to: to,
                        message: msg,
                        reply: true
                    }
                };

                $("#codopm_reply_form").ajaxSubmit(options);

            });

            codopm.ele.attach_btn.click(function() {

                codopm.attach_file('reply');
            });

            $('#codopm_inbox_previous').on('click', codopm.range.go_previous);

            $('#codopm_inbox_next').on('click', codopm.range.go_next);

            $("#codopm_inbox_a").click(function() {
                codopm.fill_inbox();
            });

            $("#codopm_inbox_refresh").click(function() {
                codopm.fill_inbox();
            });



        };
//-----------------------------------------------------------------------------------------

    });

</script>
