<?php

if ( ! defined('CX_DISQUS_NAME'))
{
	define('CX_DISQUS_NAME', 'CX Disqus Comments');
	define('CX_DISQUS_CLASS', 'Cx_disqus');
	define('CX_DISQUS_VERSION', '1.2.4');
    define('CX_DISQUS_DOCS', 'https://github.com/expressodev/cx_disqus');
    define('CX_API_THROTTLE', 600);
	define('CX_API_KEY_SPECIAL', 'E8Uh5l5fHZ6gD8U3KycjAIAk46f68Zw7C6eW8WSjZvCLXebZ7p0r1yrYDrLilk2F');
    define('CX_DISQUS_CP', 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=cx_disqus');
}

$config['name'] = CX_DISQUS_NAME;
$config['version'] = CX_DISQUS_VERSION;
$config['nsm_addon_updater']['versions_xml'] = 'http://exp-resso.com/rss/cx-disqus-comments/versions.rss';
