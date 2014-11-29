<?php

function build_arr($columns, $values) {

    $multi_value = array();

    foreach ($values as $value) {

        $multi_value[] = combine($columns, $value);
    }

    return $multi_value;
}

function combine($col, $row) {

    $arr = array();

    $i = 0;
    foreach ($row as $val) {

        $arr[$col[$i]] = $val;

        $i++;
    }

    return $arr;
}

Schema::create(PREFIX . 'codopm_messages', function($table) {
    $table->increments('id');
    $table->string('thread_hash', 30);
    $table->integer('msg_from');

    $table->string('msg_from_name', 255);
    $table->integer('msg_to');
    $table->string('msg_to_name', 255);
    $table->text('message');
    $table->text('attachments');
    $table->integer('owner');
    $table->dateTime('sent')->default('0000-00-00 00:00:00');
    $table->integer('recd')->unsigned()->default('0');
    $table->double('time', 15, 4);
});

Schema::create(PREFIX . 'codopm_config', function($table) {
    $table->increments('id');
    $table->string('option_name', 50);
    $table->text('option_value');
});

$columns = array('id', 'option_name', 'option_value');
$values = array(
    array(1, 'max_filename_len', '50'),
    array(2, 'msgs_per_page', '10'),
    array(3, 'valid_exts', 'jpeg,jpg,png,gif,doc,docx,zip'),
    array(4, 'per_filesize_limit', '2000'),
    array(5, 'total_filesize_limit', '10000'),
    array(6, 'conv_per_page', '10'),
    array(7, 'conv_load_offset', '10'));

$configs = build_arr($columns, $values);
DB::table(PREFIX . 'codopm_config')->insert(
        $configs
);
