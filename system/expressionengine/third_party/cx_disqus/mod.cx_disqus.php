<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
 * This file is part of CX Disqus Comments for ExpressionEngine
 *
 * (c) Adrian Macneil <support@exp-resso.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

include(PATH_THIRD.'cx_disqus/_config.php');

class Cx_disqus {

	public function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->load->model('cx_disqus_model');
	}

	public function act_auth() {
		$mycode = $this->EE->input->get_post('code');
		$myurl = strstr($this->EE->cx_disqus_model->settings['cp_base_url'],'?',TRUE);

		$mysess = $this->EE->session->userdata('session_id');
		$this->EE->functions->redirect($myurl.'?S='.$mysess.AMP.'D=cp'.AMP.CX_DISQUS_CP.AMP.'method=newapplication'.AMP.'code='.$mycode);

		}

	public function act_export() {

		$sql1 = "SELECT DISTINCT t.title, t.url_title, l.comment_url, t.entry_id AS thread_identifier,
				FROM_UNIXTIME(t.entry_date) AS post_date,
				CASE WHEN ( comment_expiration_date = 0 OR comment_expiration_date > NOW() ) THEN 'open' ELSE 'closed' END AS comment_status
				FROM
					exp_channel_titles t
					LEFT JOIN exp_comments c ON t.entry_id = c.entry_id
					LEFT JOIN exp_channels l ON t.channel_id = l.channel_id
				WHERE
					comment_id IS NOT NULL
				ORDER BY
					thread_identifier, comment_date;";
		$result1 = $this->EE->db->query($sql1)->result_array();
		$sql2 = "SELECT DISTINCT c.entry_id, c.comment_id, `c.ip_address`,
					FROM_UNIXTIME(c.comment_date) AS comment_date, c.`url` as author_url,
					c.`name` AS author_name, c.`email` AS author_email, c.`comment` AS comment_content,
					CASE WHEN status = 'o' THEN 1 ELSE 0 END AS comment_approved
				FROM
					exp_comments c
				ORDER BY
					comment_date;";
		$result2 = $this->EE->db->query($sql2)->result_array();

		if(sizeof($result1) == 0 || sizeof($result2) == 0) {
			return FALSE;
			}

		$xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\" ?><rss></rss>");

		$xml->addAttribute('version', '2.0');
		$xml->addAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/','xmlns');
		$xml->addAttribute('xmlns:dsq', 'http://www.disqus.com/','xmlns');
		$xml->addAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/','xmlns');
		$xml->addAttribute('xmlns:wp', 'http://wordpress.org/export/1.0/','xmlns');
		$channel = $xml->addChild('channel');

		foreach($result1 as $key=>$row) {
			$urlbase = rtrim($_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'],'/' );
			$comments_url = ( strpos($row['comment_url'],$_SERVER['SERVER_NAME']) > -1 )
					? substr($row['comment_url'], (strpos($row['comment_url'],$_SERVER['SERVER_NAME'])+strlen($_SERVER['SERVER_NAME'])) )
					: $row['comment_url'] ;
			$post_id = $row['thread_identifier'];
			$item = $channel->addChild('item');
			$item->addChild('title',$row['title']);
			$item->addChild('content:encoded',$row['title'],'content:encoded');
			$item->addChild('link',$urlbase.$comments_url.$row['url_title'].'/');
			$item->addChild('dsq:thread_identifier',$row['thread_identifier'],'dsq');
			$item->addChild('wp:post_date_gmt',$row['post_date'],'wp');
			$item->addChild('wp:comment_status',$row['comment_status'],'wp');
			foreach($result2 as $inkey=>$inrow) {
				$comment_post_id = $inrow['entry_id'];
				if($comment_post_id == $post_id) {
					$comment = $item->addChild('wp:comment',NULL,'wp');
					$comment->addChild('wp:comment_id',$inrow['comment_id'],'wp');
					$comment->addChild('wp:comment_author',$inrow['author_name'],'wp');
					$comment->addChild('wp:comment_author_email',$inrow['author_email'],'wp');
					$comment->addChild('wp:comment_author_url',$inrow['author_url'],'wp');
					$comment->addChild('wp:comment_date_gmt',$inrow['comment_date'],'wp');
					$comment->addChild('wp:comment_approved',$inrow['comment_approved'],'wp');
					$comment->addChild('wp:comment_content',$inrow['comment_content'],'wp');
					$comment->addChild('wp:comment_author_IP',$inrow['ip_address'],'wp');
					}
				}
			}

		$dt = time();
		$me = $xml->asXML();

		// the str_replace below is because simplexml is doing something strange.
		file_put_contents( APPPATH.'cache/'.$dt.'.xml',str_replace(array(' xmlns:xmlns="xmlns"',' xmlns:wp="wp"',' xmlns:dsq="dsq"',' xmlns:content="content:encoded"'),'',$xml->saveXML()) );
		ob_flush();
		header('Content-Type: application/xml');
		header('Content-disposition: attachment; filename=commentexport.xml');

		readfile(APPPATH.'cache/'.$dt.'.xml');
exit;
	}

	public function comments()
	{
		$this->EE->load->library('javascript');

		$forum = $this->EE->cx_disqus_model->settings['forum_shortname'];

		$entry_id = (int)$this->EE->TMPL->fetch_param('entry_id');
		if ($entry_id <= 0)
		{
			return lang('invalid_parameter').' entry_id';
		}

		$title = $this->EE->TMPL->fetch_param('title');
		if (empty($title))
		{
			$entry = $this->EE->cx_disqus_model->get_entry($entry_id);
			if (empty($entry))
			{
				return lang('invalid_parameter').' entry_id';
			}

			$title = $entry['title'];
		}

		$dev_mode = $this->EE->TMPL->fetch_param('developer');

		$tagdata = trim($this->EE->TMPL->tagdata);
		if (empty($tagdata))
		{
			$tagdata = <<<EOT
<div>
	{comment}<br />
	<small>Posted by {name} on {comment_date format="%Y-%m-%d %H:%i:%s"}</small>
</div>
EOT;
		}

		$tag_vars = $this->EE->cx_disqus_model->find_comments($entry_id);

		$out = '<div id="disqus_thread"><noscript>';

		if ( ! empty($tag_vars))
		{
			$out .= $this->EE->TMPL->parse_variables($tagdata, $tag_vars);
		}

		$out .= '</noscript></div>';

		$out .= '
<script type="text/javascript">
	var disqus_shortname = '.json_encode($forum).';
	var disqus_identifier = '.json_encode((string)$entry_id).';
	var disqus_title = '.json_encode($title).';';

		if ($dev_mode == 'yes')
		{
			$out .= "\n\tvar disqus_developer = 1;";
		}

		$out .= '
	(function() {
		var dsq = document.createElement("script"); dsq.type = "text/javascript"; dsq.async = true;
		dsq.src = "https://" + disqus_shortname + ".disqus.com/embed.js";
		(document.getElementsByTagName("head")[0] || document.getElementsByTagName("body")[0]).appendChild(dsq);
	})();';

		// determine whether we need to run a comment sync
		$sync_interval = CX_API_THROTTLE;
		if ($this->EE->TMPL->fetch_param('sync') !== FALSE)
		{
			$sync_interval = (int)$this->EE->TMPL->fetch_param('sync');
		}

		if ($this->EE->cx_disqus_model->settings['last_api_request'] < ($this->EE->localize->now - $sync_interval))
		{
			// ask the client to call our cron function via AJAX
			$act_url = $this->EE->functions->fetch_site_index().QUERY_MARKER.
				'ACT='.$this->EE->functions->fetch_action_id('Cx_disqus', 'act_sync').'&sync='.$sync_interval;
			$out .= '
	(function() { if (window.XMLHttpRequest) {
		var xmlhttp = new XMLHttpRequest(); xmlhttp.open("GET", "'.$act_url.'", true); xmlhttp.send();
	}})();';
		}

		$out .= "\n</script>";
		return $out;
	}

	/**
	 * Sync cron function
	 *
	 * Sync comments with Disqus. Normally called via AJAX every 10 minutes
	 */
	public function act_sync()
	{
		// determine whether we need to run the comment sync
		// (another client may have already called this function)
		$sync_interval = CX_API_THROTTLE;
		if ($this->EE->input->get('sync') !== FALSE)
		{
			$sync_interval = (int)$this->EE->input->get('sync');
		}

		if ($this->EE->cx_disqus_model->settings['last_api_request'] >= ($this->EE->localize->now - $sync_interval))
		{
			exit('NOT REQUIRED');
		}

		// record the current time, to prevent another sync starting
		$this->EE->cx_disqus_model->settings['last_api_request'] = $this->EE->localize->now;
		$this->EE->cx_disqus_model->save_settings();

		$this->EE->load->library('disqusapi');
		$this->EE->disqusapi->setKey($this->EE->cx_disqus_model->settings['secretkey']);

		$query = array(
			'forum' => $this->EE->cx_disqus_model->settings['forum_shortname'],
			'access_token' => $this->EE->cx_disqus_model->settings['access_token'],
			'related' => 'thread',
			'limit' => 100,
			'order' => 'asc'
		);

		$latest_comment_date = $this->EE->cx_disqus_model->settings['last_comment_date'];
		if ( ! empty($latest_comment_date) > 0)
		{
			$query['since'] = $latest_comment_date + 1;
		}

		$response = $this->EE->disqusapi->forums->listPosts($query);
		$entry_ids = array();

		foreach ($response as $comment)
		{
			$entry_ids[] = $this->EE->cx_disqus_model->insert_disqus_comment($comment);
		}

		$this->EE->cx_disqus_model->save_settings();

		// update comment counts
		if ( ! empty($entry_ids))
		{
			$this->EE->cx_disqus_model->recount_entry_comments($entry_ids);
		}

		exit('OK');
	}
}

/* End of file mod.cx_disqus.php */
