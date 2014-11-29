<?php
/*
 * @CODOLICENSE
 */

defined('_JEXEC') or die;


if (isset($_GET['to'])) {
    $to = $_GET['to'];
} else {
    $to = '';
}
?>
<script type="text/javascript">
    //(function($) {
    //    $(window).load(function() {

    CODOF.hook.add('on_scripts_loaded', function () {
        $.get(codopm.req_path + 'do=get_config&id=' + codopm.from + '&xhash=' + codopm.xhash, {}, function (data) {

            codopm.config = JSON.parse(data);
        });

        codopm.requests = {
        };

        codopm.inbox();
        codopm.dom_loaded();

        codopm.to = '<?php echo $to; ?>';
        $('#codopm_tabs ul li a').click(function () {
            $('#codopm_tabs ul li').removeClass('active');
            $(this).parent().addClass('active');
            var currentTab = $(this).attr('href');
            $('.codopm_tabs_divs').hide();
            $(currentTab).show();
            return false;
        });

        if (codopm.to !== "") {

            $('#codopm_compose_a').trigger('click');
            $('#codopm_to_id').val(codopm.to);
        }

        //});

    }, 2);
    //}(jQuery));
</script>

<?php
require 'client/inbox.php';


?>

<!--<script type="text/javascript" src="<?php echo codopm::$path; ?>client/js/jquery.form.min.js"></script>
<script type="text/javascript" src="<?php echo codopm::$path; ?>client/js/jquery-ui-1.10.3.custom.min.js"></script>
<script type="text/javascript" src="<?php echo codopm::$path; ?>client/js/jquery.autosize.min.js"></script>
-->
<div class="<?php echo $row_class ?> codopm">
    <div class="col-md-6">

        <div class="codo_send_pm" style="display: <?php echo ($user->id == codopm::$profile_id) ? 'none' : 'block' ?>">
            <div class="codo_btn codo_btn_def" id="codopm_send_pm"><i class="icon-mail"></i> <?php codopm::t('Send private message'); ?></div>
        </div>
        <div id="codopm_tabs" class="codopm_tabs" style="display: <?php echo ($user->id == codopm::$profile_id) ? 'block' : 'none' ?>">
            <ul>
                <li><a id="codopm_compose_a" href="#codopm_tab-1"><?php codopm::t('Compose') ?></a></li>
                <li style="display: <?php echo ($user->id == codopm::$profile_id) ? 'block' : 'none' ?>" class="active"><a href="#codopm_tab-2" id="codopm_inbox_a"><?php codopm::t('Inbox') ?></a></li>
                <li style="display:none"><a id="codopm_tab_conversations" href="#codopm_conversations"></a></li>

            </ul>

            <div class="codopm_toolbar codopm_inbox_toolbar" id="codopm_inbox_toolbar">

                <div class="codopm_navigator">

                    <div class="codopm_navigator_range" id="codopm_navigator_range"></div>

                    <div class="codopm_navigator_controls">
                        <div id="codopm_inbox_previous" class="codopm_navigator_navigate btn"><span class="icon-codoprev"></span></div>
                        <div id="codopm_inbox_next" class="codopm_navigator_navigate btn"><span class="icon-codonext"></span></div>
                    </div>

                </div>

                <div id="codopm_inbox_refresh" class="codopm_refresh btn"><span class="icon-codorefresh"></span></div>
            </div>

            <div class="codopm_toolbar codopm_conv_toolbar" id="codopm_conv_toolbar">
                <div id="codopm_conv_refresh" class="codopm_refresh btn"><span class="icon-codorefresh"></span></div>
            </div>

            <div class="codopm_tab_content">
                <div class="codopm_tabs_divs" id="codopm_tab-1" style="display:none">
                    <?php
                    require "client/compose.php";
                    ?>
                </div>

                <div class="codopm_tabs_divs" id="codopm_tab-2">
                    <div id="codopm_inbox" class="codopm_inbox">
                    </div>
                </div>

                <div class="codopm_tabs_divs" id="codopm_conversations" style="display:none">

                    <div class="codopm_reply_box" >
                        <form enctype="multipart/form-data" method="post" id="codopm_reply_form" action="<?php echo codopm::$req_path; ?>do=send&id=<?php echo $user->id; ?>&xhash=<?php echo codopm::$xhash; ?>">

                            <div class="codopm_reply_area" id="codopm_reply_area">
                                <textarea placeholder="<?php codopm::t('Click to enter text to reply'); ?>"></textarea>
                            </div>

                            <div id="codopm_reply_attachments_list" class="codopm_reply_attachments_list">
                            </div>

                            <div class="codopm_compose_attachments" id="codopm_reply_attachments">
                                <input class="codopm_default_reply_file" type="file" name="file_0"/>
                            </div>
                        </form>

                        <div style="display:none" class="codopm_reply_toolbar" id="codopm_reply_toolbar">

                            <button id="codopm_reply_btn" class="btn-primary btn"><?php codopm::t('Reply') ?> </button>
                            <div id="codopm_reply_attach" class="codopm_reply_attach btn"> <?php codopm::t('Add files') ?> </div>
                            <div style="top: 10px;left: 20px;" id="codopm_attachment_progress_r" class="codopm_attachment_progress">
                                <div id="codopm_attachment_bar_r" class="codopm_attachment_bar"></div>
                                <div id="codopm_attachment_percent_r" class="codopm_attachment_percent">0%</div >
                            </div>

                        </div>
                    </div>

                    <div class="codopm_conversation_content" id="codopm_conversation_content">


                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
