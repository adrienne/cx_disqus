<?php
/*
* @Author: Adrienne
* @Date:   2014-06-13 18:41:04
* @Last Modified by:   Adrienne
* @Last Modified time: 2014-06-13 18:42:13
*/
?>

<?= form_open($post_url); ?>

<?php

    $this->table->clear();
    $this->table->set_template($cp_table_template);
    $this->table->set_heading(
        array('data' => lang('preference'), 'width' => "50%"),
        array('data' => lang('setting')));


    $this->table->add_row(
        '<strong>Forum Name</strong>'.BR.
        '<div class="subtext"></div>',
        form_input('forumname')
    );

    $this->table->add_row(
        '<strong>Forum Slug (Short Name)</strong>'.BR.
        '<div class="subtext"></div>',
        form_input('forumshortname')
    );


    echo $this->table->generate();
?>

<div style="text-align: right;">
    <?= form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit')); ?>
</div>

<?= form_close(); ?>
