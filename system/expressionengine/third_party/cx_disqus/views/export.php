<p>
<a href="<?php echo $exp_url; ?>" class="submit">Export XML File</a>
</p>
<p>After the file downloads, go to <a href="https://<?php echo $forumname; ?>.disqus.com/admin/discussions/import/platform/generic/">the import page for your forum</a>
 and upload the file. Make sure "Generic (WXR)" is the import type selected for upload.</p>
 <p>After you are done, make sure to hit the "Match Comments" button below to ensure your comments are properly synchronized with Disqus!</p>
 <p><a href="<?php echo $match_url; ?>" class="submit">Match Comments</a></p>

<?php /* ?>
<p><?= $comment_export_count ?> comments will be exported to Disqus</p>

    <p><strong>Please ensure:</strong></p>

    <ul class="bullets">
        <li><strong>You have given your API application write access to your forum</strong>, and</li>
        <li><strong>Your API application authentication is set to "Inherit permissions"</strong></li>
    </ul>

    <p><a href="http://disqus.com/api/applications/">http://disqus.com/admin/</a></p>

    <?= form_open($post_url); ?>
        <div style="text-align: right;">
            <?= form_submit(array('name' => 'submit', 'value' => lang('export_comments'), 'class' => 'submit')); ?>
        </div>
    <?= form_close(); ?>
<?php */ ?>
