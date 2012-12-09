<?php
/*
Template Name: Readerwalls(读者墙)
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
<h2 style="color:#f00">灌水先锋队</h2>
<?php
    $query="SELECT COUNT(comment_ID) AS cnt, comment_author, comment_author_url, comment_author_email FROM (SELECT * FROM $wpdb->comments LEFT OUTER JOIN $wpdb->posts ON ($wpdb->posts.ID=$wpdb->comments.comment_post_ID) WHERE comment_date > date_sub( NOW(), INTERVAL 24 MONTH ) AND user_id='0' AND comment_author_email != '' AND post_password='' AND comment_approved='1' AND comment_type='') AS tempcmt GROUP BY comment_author_email ORDER BY cnt DESC LIMIT 36";
    $wall = $wpdb->get_results($query);
    $maxNum = $wall[0]->cnt;
    foreach ($wall as $comment)
    {
        $width = round(40 / ($maxNum / $comment->cnt),2);
        if( $comment->comment_author_url )
        $url = $comment->comment_author_url;
        else $url="#";
        $avatar = get_avatar( $comment->comment_author_email, $size = '36', $default = get_bloginfo('wpurl').'/avatar/default.jpg' );
        $tmp = "<li><a target=\"_blank\" href=\"".$comment->comment_author_url."\">".$avatar."<em>".$comment->comment_author."</em> <strong>+".$comment->cnt."</strong><br/>".$comment->comment_author_url."</a></li>";
        $output .= $tmp;
     }
    $output = "<ul class=\"readers-list\">".$output."</ul>";
    echo $output ;
?>
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