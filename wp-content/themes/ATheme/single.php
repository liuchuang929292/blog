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
            <div class="excerpt">
                <h2><?php the_title(); ?></h2>
                <div class="meta_info"><ul id="resizer"><li id="f_text">字号：</li><li id="f_s"><a href="javascript:void(0)">小</a></li><li id="f_m"><a href="javascript:void(0)">中</a></li><li id="f_l"><a href="javascript:void(0)">大</a></li></ul> &nbsp; 作者：<?php the_author() ?> &nbsp; 发布：<?php ATheme_time_diff( $time_type = 'post' ); ?> &nbsp; 围观：<?php if(function_exists(the_views)) { the_views(' 次+', true);}?> &nbsp; <?php comments_popup_link ('抢沙发','1条评论','%条评论'); ?> &nbsp; <?php edit_post_link('编辑', ' [ ', ' ] '); ?>
                </div>
                <div class="clear"></div>
                <div class="context">
                    <?php if (get_option('swt_ada') == 'Display') { ?>
                        <div id="adsense1" style="display:none;margin-bottom:10px;"><div id="adsense-loader1" style="display:none;">
                            <center><?php echo stripslashes(get_option('swt_adacode')); ?></center>
                        </div></div>
                    <?php { echo ''; } ?>
                    <?php } else { } ?>
                    <?php the_content('Read more...'); ?>
                    <?php wp_link_pages(array('before' => '<div class="fenye">分页阅读：', 'after' => '', 'next_or_number' => 'next', 'previouspagelink' => '上一页', 'nextpagelink' => "")); ?>   <?php wp_link_pages(array('before' => '', 'after' => '', 'next_or_number' => 'number', 'link_before' =>'<span>', 'link_after'=>'</span>')); ?>   <?php wp_link_pages(array('before' => '', 'after' => '</div>', 'next_or_number' => 'next', 'previouspagelink' => '', 'nextpagelink' => "下一页")); ?>
                    <div class="cut_line"><span>正文部分到此结束</span></div>
                    <div class="post_copyright">
                    <?php if (get_option('swt_adb') == 'Display') { ?>
                        <div id="adsense2" style="display:none;margin:10px 0;"><div id="adsense-loader2" style="display:none;">
                            <center><?php echo stripslashes(get_option('swt_adbcode')); ?></center>
                        </div></div>
                    <?php { echo ''; } ?>
                    <?php } else { } ?>
                    <p>本文固定链接: <a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_permalink() ?> | <?php bloginfo('name');?></a>&nbsp;|<a href="#" onclick="copy_code('<?php the_permalink() ?>'); return false;"><strong style="color:#666;"> +复制链接 </strong></a></p>
                    <p>文章转载请注明: <a href="<?php the_permalink() ?>" rel="bookmark" title="本文固定链接 <?php the_permalink() ?>"><?php the_title(); ?> | <?php bloginfo('name');?></a></p>
                    <p>您还可以继续浏览: <?php ATheme_tags(); ?></p>
                    <?php ATheme_txt_share(); ?>
                    </div>
                    <div class="pre_next">
                    <?php previous_post_link('〖上一篇文章〗%link') ?><br/>
					<?php next_post_link('〖下一篇文章〗%link') ?>
                    </div>
                    <?php include('includes/related.php'); ?>
                </div>
                <div class="comments">
                    <?php comments_template(); ?>
                </div>
            </div>
            <?php endwhile; else: ?>
        <?php endif; ?>
    </div>
    </div>
<?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>