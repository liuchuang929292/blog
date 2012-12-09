<?php get_header(); ?>
<div id="wrapper">
    <div id="nav"><?php ATheme_menu('menu'); ?></div>
    <?php ATheme_bulletin(); ?>
    <?php ATheme_breadcrunbs(); ?>
    <?php ATheme_pic_share(); ?>
    <div id="content_wrap">
    <div id="content">
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
            <ul <?php post_class(); ?> id="post-<?php the_ID(); ?>"><li>
            <div class="excerpt">
                <?php $t1=$post->post_date;$t2=date("Y-m-d H:i:s");$diff=(strtotime($t2)-strtotime($t1))/3600;if($diff<24){echo '<span class="new"></span>';} ?>
                <h2><a href="<?php the_permalink() ?>" rel="external" title="详细阅读 <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
                <div class="info">
                    作者：<?php the_author() ?> | 发布：<?php ATheme_time_diff( $time_type = 'post' ); ?> | 分类：<?php the_category(', ') ?> | 围观：<?php if(function_exists(the_views)) { the_views(' 次+', true);}?> | <?php comments_popup_link ('抢沙发','1条评论','%条评论'); ?> | <?php edit_post_link('编辑'); ?>
                </div>
                <?php if (get_option('swt_thumbnail') == 'Display') { ?>
                    <?php if (get_option('swt_articlepic') == 'Display') { ?>
                        <?php include('includes/articlepic.php'); ?>
                        <?php { echo ''; } ?>
                    <?php } else { include(TEMPLATEPATH . '/includes/thumbnail.php'); } ?>
                        <?php { echo ''; } ?>
		        <?php } else { } ?>
                <div class="entry">
                    <span><?php echo mb_strimwidth(strip_tags(apply_filters('the_content', $post->post_content)), 0, 400,"..."); ?></span>
                </div>
                <div class="clear"></div>
                <?php ATheme_tags(); ?>
            </div>
            </li></ul>
            <?php endwhile; ?>
            <?php else: ?>
                <h3><center>什么也找不到.<br/>抱歉,您所找的分类里没有文章!</center></h3>
        <?php endif; ?> 
    <div class="navigation">
        <?php ATheme_pagination($query_string); ?>
    </div>
    </div>
    </div>
<?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>