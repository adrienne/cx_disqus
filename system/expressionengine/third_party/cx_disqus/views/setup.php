<?php
/*
* @Author: Adrienne
* @Date:   2014-06-10 15:58:47
* @Last Modified by:   Adrienne
* @Last Modified time: 2014-06-13 18:03:24
*/
?>

<?php if ($display == 'welcome'): ?>

<a class="submit" href="https://disqus.com/api/oauth/2.0/authorize/?scope=read,write,admin&response_type=api_key&redirect_uri=<?php echo $auth_url; ?>">Authorize Disqus and Create Application</a>

<?php else: ?>

<?= form_open($post_url); ?>

<input type="hidden" name="code" value="<?php echo $code; ?>" />

<?php

    $this->table->clear();
    $this->table->set_template($cp_table_template);
    $this->table->set_heading(
        array('data' => lang('preference'), 'width' => "50%"),
        array('data' => lang('setting')));


    $this->table->add_row(
        '<strong>Application Name</strong>'.BR.
        '<div class="subtext"></div>',
        form_input('applabel')
    );

    $this->table->add_row(
        '<strong>Description</strong>'.BR.
        '<div class="subtext"></div>',
        form_input('appdescription')
    );


    echo $this->table->generate();
?>

<div style="text-align: right;">
    <?= form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit')); ?>
</div>

<?= form_close(); ?>

<?php endif; ?>
