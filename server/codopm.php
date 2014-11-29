<?php

/*
 * @CODOLICENSE
 */

if (!isset($_SESSION)) {

    define('_JEXEC', 'JOO');
    defined('_JEXEC') or die;

    session_start();

    require 'connector.php';

    if (md5($_GET['id'] . codopm::$secret) != $_GET['xhash']) {

        die("CODOPM SAYS: Invalid X=hash");
    }

    codopm::$table['mail_column'] = 'email';
    codopm::$upload_path = "../";

    codopm_do_route();
    
} else {

    codopm::$db = \DB::getPDO();

    dispatch('codopm', 'codopm_do_route');
    dispatch_post('codopm', function() {

        codopm_do_route();
    });
}

class response {

    public $has_error = false;
    public $msg = "";

}

function pexecute($query, $variable_array = array()) {

    $sth = codopm::$db->prepare($query);   
    $sth->execute($variable_array);
    return $sth;
}

function findexts($fn) {
    $str = explode('/', $fn);
    $len = count($str);
    if (strpos($str[($len - 1)], '.') === False)
        return False; // Has not .
    $str2 = explode('.', $str[($len - 1)]);
    $len2 = count($str2);
    $ext = $str2[($len2 - 1)];
    return $ext;
}

function send_message($from, $to, $message) {


    $resp = new response();
    $resp->has_error = false;

    if ($to == "") {

        $to = $_POST['hc_to'];
    }

    if (strpos($to, ",") !== FALSE) {

        $to_ids = explode(",", $to);
    } else {

        $to_ids = array($to);
    }

    $done = false;

    $query = "SELECT id,name,username  FROM " . codopm::$db_prefix . "users WHERE username=:toA OR " . codopm::$table['mail_column'] . "=:toB OR id=:toC";
    $sth = codopm::$db->prepare($query);

    $ins_query = "INSERT INTO codopm_messages (thread_hash,msg_from,msg_from_name,msg_to,msg_to_name,message,attachments,owner,sent,recd,time)
            VALUES(:thread_hash,:msg_from,:msg_from_name,:msg_to,:msg_to_name,:msg,:attachments,:owner,NOW(),:recd,:time)";
    $ins = codopm::$db->prepare($ins_query);

    foreach ($to_ids as $to) {

        $to = trim($to);

        if ($to == "") {

            continue;
        }

        $sth->execute(array(':toA' => $to, ':toB' => $to, ':toC' => $to));
        $red = $sth->fetch();

        if (count($red) <= 0) {

            $resp->has_error = true;
            $resp->msg = "user not found.";
            echo json_encode($resp);
            return;
        }

        $to = $red["id"];
        $to_name = htmlentities($red['name'], ENT_QUOTES, "UTF-8");

        if ($to_name == null || $to_name == "") {

            $to_name = $red['username'];
        }

        $time = microtime(true);
        $thread_hash = generate_thread_hash($from, $to);

        $from_name = $_SESSION[codopm::$secret . "from_name"];

        if (!$done) {

            //upload files only once
            $file_names = upload_files($resp);
            $done = true;
        }

        $attachments = json_encode($file_names);

        $vars = array(':thread_hash' => $thread_hash, ':msg_from' => $from, ':msg_from_name' => $from_name, ':msg_to' => $to,
            ':msg_to_name' => $to_name, ':msg' => $message, ':attachments' => $attachments, ':owner' => $from, ':recd' => "1", ':time' => $time);
        $ins->execute($vars);


        if ($from != $to) {

            //duplicate msg for reciever
            $vars["owner"] = $to;
            $vars["recd"] = "0";
            $ins->execute($vars);
        }
    }

    $resp->has_error = false;
    $resp->msg_id = codopm::$db->lastInsertId();
    $resp->attachments = $attachments;
    $resp->msg = "Message successfully sent.";
    echo json_encode($resp);
}

function upload_files(&$resp) {

    $output_dir = codopm::$upload_path . 'client/uploads/';
    $file_size = 0;
    $valid_exts = codopm::$config['valid_exts'];
    $file_size_limit = codopm::$config['per_filesize_limit'] * 1024; //2 MB
    $total_file_size_limit = codopm::$config['total_filesize_limit'] * 1024; //10MB
    $file_ext = explode(",", $valid_exts);
    $file_names = array();

    foreach ($_FILES as $file) {

        $file_uploaded_ext = strtolower(findexts($file["name"]));

        if (!in_array($file_uploaded_ext, $file_ext)) {
            $resp->has_error = true;
            $resp->msg = "file extension not supported";
        } else if ($file["size"] > $file_size_limit) {
            $resp->has_error = true;
            $resp->msg = "file size cannot be greater than $file_size_limit KB";
        } else if ($file["error"] > 0) {
            $resp->has_error = true;
            $resp->msg = "file could not be uploaded [" . $_FILES["myfile"]["error"] . "]";
        } else if ($file["error"] == 0 && $file_size < $total_file_size_limit) {
            $file_size += $file['size'];
            $uname = time() . rand(22, 333) . "." . $file_uploaded_ext;
            move_uploaded_file($file["tmp_name"], $output_dir . $uname);
            $file_names[] = array("name" => strip_tags($file["name"]), "uname" => $uname);
        } else {
            $resp->has_error = true;
            $resp->msg = "Total file size exceeds $total_file_size_limit KB";
        }
    }


    return $file_names;
}

function generate_thread_hash($from, $to) {

    $from = (int) $from;
    $to = (int) $to;

    $arr = array($from, $to);

    sort($arr);
    return implode("_", $arr);
}

function save_my_details($id) {


    if (!isset($_SESSION[codopm::$secret . "from_id"]) ||
            (isset($_SESSION[codopm::$secret . "from_id"]) && $_SESSION[codopm::$secret . "from_id"] != $id)) {

        $query = "SELECT username,name FROM " . codopm::$db_prefix . "users WHERE id = :id";

        $obj = pexecute($query, array(":id" => $id));
        $res = $obj->fetch();

        $name = $res['name'];
        if (empty($name)) {

            $name = $res['username'];
        }

        $_SESSION[codopm::$secret . "from_name"] = $name;
    }

    return $_SESSION[codopm::$secret . "from_name"];
}

function codopm_do_route() {

    if (isset($_GET['do'])) {

        require 'config.php';

        $conf = new Config(codopm::$db);
        codopm::$config = $conf->get_config();

        require 'getmessages.php';

        $do = $_GET['do'];

        if ($do == 'get_config') {

            echo json_encode(codopm::$config);
        } else if ($do == 'send') {

            if (!isset($_POST['to'])) {

                $resp = new response();
                $resp->has_error = true;
                $resp->msg = "file size exceeded maximum post size, please remove some files";
                echo json_encode($resp);
                return;
            }

            send_message((int)$_GET['id'], (int)$_POST['to'], strip_tags($_POST['message']));
        } else if ($do == 'get_messages') {

            $id = (int)$_GET['id'];

            $my_name = save_my_details($id);
            $per_page = codopm::$config['msgs_per_page'];
            $start = (int) $_GET['range']['from'];
            $end = (int) $_GET['range']['to'];

            $resp = new response();
            if ($_GET['type'] == 'default') {

                $start = 0;
            } else if ($_GET['type'] == 'next') {

                $start += $per_page;
            } else if ($_GET['type'] == 'previous') {

                $start -= $per_page;
            } else
                exit;


            $messages = get_messages($id, $start);

            $msg_cnt = count($messages);
            $count = get_message_count($id);
            $cnt = $count[0]["count"];

            if ($msg_cnt < $per_page) {

                $end = $cnt;
            } else {
                $end = $start + $per_page - 1;
            }


            if ($start === 0 && $cnt != 0) {
                $start = 1;
                $end = $per_page;
            }


            if (
                    $end > $cnt ||
                    ( $end < $cnt && $cnt < $per_page )
            )
                $end = $cnt;




            $resp->has_error = false;

            $resp->start = $start;
            $resp->end = $end;
            $resp->per_page = $per_page;
            $resp->messages = $messages;
            $resp->my_name = $my_name;
            $resp->count = $cnt;

            echo json_encode($resp);
        } else if ($do == 'get_conversations') {

            $to = (int)$_GET['msg_to'];
            $from = (int)$_GET['msg_from'];
            $offset = codopm::$config['conv_load_offset'];

            $res = get_conversations($to, $from, (int)$_GET['id'], codopm::$config['conv_per_page'] + 1);

            $conversations = $res['conversations'];
            $count = $res['count'];

            set_message_read($to, $from, (int)$_GET['id']);

            $resp = new response();
            $resp->has_error = false;

            if ($count > codopm::$config['conv_per_page']) {
                array_pop($conversations);
                $resp->read_more = 'yes';
            } else {
                $resp->read_more = 'no';
            }


            $resp->conversations = $conversations;
            $resp->offset = $offset;

            echo json_encode($resp);
        } else if ($do == 'delete_conversation') {

            delete_conversation((int)$_POST['msg_id']);

            $resp = new response();
            $resp->has_error = false;

            echo json_encode($resp);
        } else if ($do == 'load_more_conversations') {

            $to = (int)$_GET['msg_to'];
            $from = (int)$_GET['msg_from'];
            $offset = (int) $_GET['msg_offset'];

            $res = get_conversations($to, $from, (int)$_GET['id'], codopm::$config['conv_load_offset'] + 1, $offset);

            $conversations = $res['conversations'];
            $count = $res['count'];

            $resp = new response();
            $resp->has_error = false;

            if ($count > codopm::$config['conv_load_offset']) {
                array_pop($conversations);
                $resp->read_more = 'yes';
            } else {
                $resp->read_more = 'no';
            }


            $resp->conversations = $conversations;
            $resp->offset = $offset;

            echo json_encode($resp);
        } else if ($do == 'autocomplete') {

            $results = array();

            if (isset($_GET['term'])) {

                $terms = strip_tags($_GET['term']);

                if (strpos($terms, ",") !== FALSE) {

                    $_term = explode(",", $terms);
                    $term = trim(end($_term));
                } else {

                    $term = $terms;
                }


                $query = "SELECT name,username FROM " . codopm::$db_prefix . "users "
                        . "WHERE (username LIKE :term1 OR name LIKE :term2)";
                $sth = codopm::$db->prepare($query);

                $variable_array = array(
                    ":term1" => $term . '%',
                    ":term2" => $term . '%'
                );

                $sth->execute($variable_array);

                $result = $sth->fetchAll();

                foreach ($result as $res) {

                    $label = $res['name'];

                    if (empty($label)) {

                        $label = $res['username'];
                    } else {

                        if ($res['name'] != $res['username']) {

                            $label = $res['name'] . " " . "(" . $res['username'] . ")";
                        }
                    }

                    $results[] = array(
                        "id" => $res['username'],
                        "label" => $label,
                        "value" => $res['username']
                    );
                }
            }

            echo json_encode($results);
        }
    } else {

        die("CODOPM SAYS: Invalid Req");
    }
}
