<?php
defined('_JEXEC') or die;
/**
 * @package Component codoPM for Joomla! 3.0
 * @author codologic
 * @copyright (C) 2013 - codologic
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
?><style>
    .codopm_message{
        width:50%;
        height:110px;
    }

    .codopm_compose input{


        width:50%;
    }
</style>
<div class="codopm_compose">
    <form enctype="multipart/form-data" method="post" id="codopm_compose" action="<?php echo codopm::$req_path; ?>do=send&id=<?php echo $user->id; ?>&xhash=<?php echo codopm::$xhash; ?>">

        <label><?php codopm::t('To') ?></label><input name="hc_to" placeholder="username or email" type="text" id="codopm_to_id" />
        <input type="hidden" name='to' id="actual_to"/><br/>
        <label><?php codopm::t('Message') ?></label>
        <textarea id="codopm_message" class="codopm_message"></textarea>

        <div id="codopm_compose_attach">
            <span class="codopm_attach" ></span>
            <span style="vertical-align: top"> <?php codopm::t('Attach files') ?> </span>
        </div>

        <div class="codopm_compose_attachments" id="codopm_compose_attachments">
            <input class="codopm_default_compose_file" type="file" name="file_0"/>            
        </div>

        <div class="codopm_compose_attachments_title"><?php codopm::t('Attached files') ?>: </div>

        <div id="codopm_compose_attachments_list" class="codopm_compose_attachments_list">
        </div>

    </form>
    <div id="codopm_attachment_progress" class="codopm_attachment_progress">
        <div id="codopm_attachment_bar" class="codopm_attachment_bar"></div>
        <div id="codopm_attachment_percent" class="codopm_attachment_percent">0%</div >
    </div>

    <button id="codopm_sendbtn" class="btn btn-primary"><?php codopm::t('Send') ?> </button>
    &nbsp;&nbsp;<span class="codopm_msg_sent"><?php codopm::t('Your message has been sent'); ?>!
            <a target="_blank" href="<?php echo codopm::$profile_path; ?>"><?php codopm::t('View message'); ?></a>
    </span>
</div>
<script>
    CODOF.hook.add('on_scripts_loaded', function() {

        codopm.files_attached = 0;

        codopm.delete_attachment = function(id) {

            $('#' + id).remove();
            $('#visible_' + id).remove();

            if ($('.codopm_compose_attachments').children().length === 0) {
                $('.codopm_compose_attachments_title').hide();
            }
        };

        codopm.attach_file = function(type) {

            var attach = $('#codopm_' + type + '_attachments');
            codopm.files_attached++;

            var id = type + 'codopm_attach_input_' + codopm.files_attached;
            var div = '<input id="' + id + '" type="file" name="file_' + codopm.files_attached + '"/>';

            attach.append(div);


            $('#' + id).on('change', function() {

                codopm.onchange_fileadd($(this), id, type);
            });

        };


        codopm.onchange_fileadd = function(ele, id, type) {

            var div = ele[0],
                    filename = '', filesize = '';

            if (typeof div.files === "undefined") {
                //stupid IE

                filename = ele.val().split('/').pop().split('\\').pop();
            } else {
                var file = div.files[0];
                filename = file.name;
                filesize = "(" + Math.ceil(file.size / 1000) + " KB)";
            }

            if (filename.length > 50) {
                filename = filename.substr(0, 50) + "...";
            }

            var list = '<div id="visible_' + id + '" class="codopm_' + type + '_attachments_list_div">' + filename + ' <b>' + filesize + '</b><div onclick="codopm.delete_attachment(\'' + id + '\')" style="margin-right: 2px;" class="codopm_conversation_delete">&times;</div>';
            $('#codopm_' + type + '_attachments_list').append(list);

            if (type === 'reply') {
                setTimeout(function() {
                    $('#codopm_reply_area > textarea').focus();
                }, 100);
            } else {
                $('.codopm_' + type + '_attachments_title').show();

            }

            //i finished my job offscreen me
            ele.css('left', '-999em');

            //add new file at same position
            codopm.attach_file(type);
        };

        $('#codopm_compose_attach').click(function() {

            codopm.attach_file('compose');
        });

        $('#codopm_compose_a').click(function() {
            $('.codopm_toolbar').hide();
        });

        $('#codopm_compose').submit(function() {

            return false;
        });

        $('#codopm_sendbtn').click(function() {

            var to = $('#actual_to').val();
            if ($.trim(to) === "") {

                //Wow the user acually typed the name
                to = $('#codopm_to_id').val();
            }

            var msg = $('#codopm_message').val();
            msg = msg.replace("<", "&lt;");
            msg = msg.replace("<", "&gt;");

            if ($.trim(to) === "" || $.trim(msg) === "") {
                alert('fields cannot be left empty');
                return false;
            }

            msg = msg.replace(/\r/g, "<br/>");
            msg = msg.replace(/\r?\n/g, "<br/>");



            var options = {
                beforeSend: function()
                {
                    $("#codopm_attachment_progress").show();
                    //clear everything
                    $("#codopm_attachment_bar").width('0%');
                    $("#codopm_attachment_percent").html("0%");
                },
                uploadProgress: function(event, position, total, percentComplete)
                {
                    //if ($('#codopm_compose_file').val() !== "") {
                    $("#codopm_attachment_bar").width(percentComplete + '%');
                    $("#codopm_attachment_percent").html(percentComplete + '%');
                    //}

                },
                success: function()
                {
                    //if ($('#codopm_compose_file').val() !== "") {

                    $("#codopm_attachment_bar").width('100%');
                    $("#codopm_attachment_percent").html('100%');
                    //}
                },
                complete: function(data)
                {

                    data = JSON.parse(data.responseText);
                    if (data.has_error == false) {

                        if (codopm.profile_id !== codopm.from) {

                            //someone else's profile
                            $('.codopm_msg_sent').show();
                            $('#codopm_message').val('');
                            $('#codopm_compose_file').val("");

                            $('.codopm_compose_attachments_title').hide();
                            $('#codopm_compose_attachments_list').html('');
                            $('#codopm_compose_attachments').html('<input class="codopm_default_compose_file" type="file" name="file_0"/>');
                            codopm.files_attached = 0;
                            $("#codopm_attachment_progress").hide();
                            $("#codopm_attachment_bar").width('0%');
                            $("#codopm_attachment_percent").html("0%");
                            
                        } else {
                            $("#codopm_inbox_a").trigger('click');
                            $('#codopm_message').val('');
                            $('#codopm_compose_file').val("");
                            $('#actual_to').val('');
                            $('#codopm_to_id').val('');

                            $('.codopm_compose_attachments_title').hide();
                            $('#codopm_compose_attachments_list').html('');
                            $('#codopm_compose_attachments').html('<input class="codopm_default_compose_file" type="file" name="file_0"/>');
                            codopm.files_attached = 0;
                            $("#codopm_attachment_progress").hide();
                            $("#codopm_attachment_bar").width('0%');
                            $("#codopm_attachment_percent").html("0%");
                        }
                    } else {
                        alert(data.msg);

                    }


                },
                error: function()
                {

                },
                data: {
                    message: msg
                }
            };

            $("#codopm_compose").ajaxSubmit(options);
        });



        var names = ["Jörn Zaefferer", "Scott González", "John Resig"];

        var accentMap = {
            "á": "a",
            "ö": "o"
        };
        var normalize = function(term) {
            var ret = "";
            for (var i = 0; i < term.length; i++) {
                ret += accentMap[ term.charAt(i) ] || term.charAt(i);
            }
            return ret;
        };

        var codopm_user_cache = {};

        codopm.split = function(val) {
            return val.split(/,\s*/);
        };
        codopm.extractLast = function(term) {
            return codopm.split(term).pop();
        };

        $("#codopm_to_id").autocomplete({
            minLength: 2,
            source: function(request, response) {
                var term = request.term;
                if (term in codopm_user_cache) {

                    //return;
                }

                $.getJSON(codopm.req_path + "do=autocomplete&id=" + codopm.from + "&xhash=" + codopm.xhash, request, function(data, status, xhr) {
                    codopm_user_cache[ term ] = data;
                    response($.ui.autocomplete.filter(
                            data, codopm.extractLast(request.term)));
                });
            },
            focus: function() {

                return false;
            },
            create: function(event, ui) {
                $('.ui-autocomplete').wrap('<span class="codopm_autocomplete"></span>');
            },
            select: function(event, ui) {

                var terms = codopm.split(this.value);
                // remove the current input
                terms.pop();
                // add the selected item
                terms.push(ui.item.value);
                // add placeholder to get the comma-and-space at the end
                terms.push("");
                this.value = terms.join(", ");

                return false;
            }
        });

        $('document').ready(function($) {
            $('#codopm_compose_a').click(function() {

                setTimeout(function() {
                    $('#codopm_to_id').focus();
                }, 100);

            });


            $('#codopm_send_pm').click(function() {

                $('.codo_send_pm').hide();
                $('#codopm_compose_a').trigger('click');
                $('#codopm_tabs').show();
                $('#codopm_to_id').val(codopm.profile_name);

                setTimeout(function() {
                    $('#codopm_message').focus();
                }, 100);
            });


        });

    });

</script>

