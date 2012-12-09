<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<?php include('includes/seo.php'); ?>
<link rel="stylesheet" type="text/css" href="<?php bloginfo('template_directory'); ?>/style.css" />
<link rel="stylesheet" type="text/css" href="<?php bloginfo('template_url'); ?>/images/pirobox/style.css" />
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="alternate" type="application/atom+xml" title="<?php bloginfo('name'); ?> Atom Feed" href="<?php bloginfo('atom_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<link rel="shortcut icon" href="<?php bloginfo('template_directory'); ?>/images/favicon.ico" />
<?php if (is_archive() && ($paged > 1) && ($paged < $wp_query->max_num_pages)) { ?>
<link rel="prefetch" href="<?php echo get_next_posts_page_link(); ?>" />
<link rel="prerender" href="<?php echo get_next_posts_page_link(); ?>" />
<?php } elseif (is_singular()) { ?>
<link rel="prefetch" href="<?php bloginfo('home'); ?>" />
<link rel="prerender" href="<?php bloginfo('home'); ?>" />
<?php } ?>
<script type="text/javascript" src="//lib.sinaapp.com/js/jquery/1.7.2/jquery.min.js"></script>
<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/pirobox.min.js"></script>    
<script type="text/javascript">
$(document).ready(function() {    
    $().piroBox({
            my_speed: 400,  
            bg_alpha: 0.3, 
            slideShow : true,
            slideSpeed : 4,
            close_all : '.piro_close,.piro_overlay'
    });
});
</script>
<?php wp_head(); ?>
<?php if ( is_singular() ){ ?>
<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/comments-ajax.js"></script>
<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/comments.js"></script>
<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/realgravatar.js"></script>
<?php } ?>
<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/ATheme.js"></script>
<?php include('includes/lazyload.php'); ?>
</head>

<body>
<div id="header">
    <div id="header_inner">
        <?php ATheme_logo(); ?>
        <?php ATheme_menu('top_bar'); ?>
        <form id="searchform" method="get" action="<?php bloginfo('home'); ?>">
        <input type="text" name="s" class="field" value="回车站内搜索..." onfocus="if (this.value == '回车站内搜索...') {this.value = '';}" onblur="if (this.value == '') {this.value = '回车站内搜索...'}" />
        </form>
        <div id="rss"><ul>
        <li class="rssfeed"><a href="<?php bloginfo('rss2_url'); ?>" target="_blank" class="icon4" title="欢迎订阅<?php bloginfo('name'); ?>"></a></li>
        <?php if (get_option('swt_tqq') == 'Display') { ?><li class="tqq"><a href="<?php echo stripslashes(get_option('swt_tqqurl')); ?>" target="_blank" class="icon3" title="我的腾讯微博"></a></li><?php { echo ''; } ?><?php } else { } ?>
        <?php if (get_option('swt_tsina') == 'Display') { ?><li class="tsina"><a href="<?php echo stripslashes(get_option('swt_tsinaurl')); ?>" target="_blank" class="icon2" title="我的新浪微博"></a></li><?php { echo ''; } ?><?php } else { } ?>
        <?php if (get_option('swt_mailqq') == 'Display') { ?><li class="rssmail"><a href="http://mail.qq.com/cgi-bin/feed?u=<?php bloginfo('rss2_url'); ?>" target="_blank" class="icon1" title="用QQ邮箱阅读空间订阅我的博客"></a></li><?php { echo ''; } ?><?php } else { } ?>
        </ul></div>
    </div>
</div>