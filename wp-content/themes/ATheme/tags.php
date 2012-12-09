<?php
/*
Template Name: Tags(标签云集)
*/
?>
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
                <div class="context">
<h2 style="color:#f00">标签云集</h2>
<p><strong><?php bloginfo('name'); ?></strong>目前共有标签： <?php echo $count_tags = wp_count_terms('post_tag'); ?>个</p>
<ul class="tag-clouds">
<?php
$tags = get_tags ();
if($tags) {
foreach ( $tags as $tag )
	echo '<li><a title="标签 '.$tag->name.' 下共有'.$tag->count.'篇文章" class="tag-link tag-link-'.$tag->term_id.'" href="' . get_tag_link($tag) . '">' . $tag->name .'</a><strong style="color:#67A611;"> x '.$tag->count.'</strong></li>';
} ?>
</ul>
                </div>
            </div>
            <div class="comments">
			<?php comments_template(); ?>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
    </div>
<?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>