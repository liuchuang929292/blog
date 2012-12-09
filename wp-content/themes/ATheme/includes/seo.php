<?php if ( is_home() ) { ?><title>
<?php
if(isset($_COOKIE['comment_author_'.COOKIEHASH])) {
    $lastCommenter = $_COOKIE['comment_author_'.COOKIEHASH];
    echo "亲爱的".$lastCommenter. "," . " 欢迎回到 ";
} else {
    echo "亲爱的朋友, 欢迎来到 ";
}
?>
<?php bloginfo('name'); ?><?php if ( $paged < 2 ) {} else { echo ('-第'); echo ($paged);echo '页';}?> | <?php bloginfo('description'); ?></title><?php } ?>
<?php if ( is_search() ) { ?><title>搜索结果 | <?php bloginfo('name'); ?></title><?php } ?>
<?php if ( is_single() ) { ?><title><?php echo trim(wp_title('',0)); ?><?php if (get_query_var('page')) { echo '-第'; echo get_query_var('page'); echo '页';}?> | <?php bloginfo('name'); ?></title><?php } ?>
<?php if ( is_page() ) { ?><title><?php echo trim(wp_title('',0)); ?><?php if ( $paged < 2 ) {} else { echo ('-第'); echo ($paged);echo '页';}?> | <?php bloginfo('name'); ?></title><?php } ?>
<?php if ( is_category() ) { ?><title><?php single_cat_title(); ?><?php if ( $paged < 2 ) {} else { echo ('-第'); echo ($paged);echo '页';}?> | <?php bloginfo('name'); ?></title><?php } ?>
<?php if ( is_month() ) { ?><title><?php the_time('F'); ?><?php if ( $paged < 2 ) {} else { echo ('-第'); echo ($paged);echo '页';}?> | <?php bloginfo('name'); ?></title><?php } ?>
<?php if (function_exists('is_tag')) { if ( is_tag() ) { ?><title><?php  single_tag_title("", true); ?><?php if ( $paged < 2 ) {} else { echo ('-第'); echo ($paged); echo '页';}?> | <?php bloginfo('name'); ?></title><?php } ?> <?php } ?>
<?php
if (!function_exists('utf8Substr')) {
 function utf8Substr($str, $from, $len)
 {
     return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$from.'}'.
          '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$len.'}).*#s',
          '$1',$str);
 }
}
if ( is_single() ){
    if ($post->post_excerpt) {
        $description  = $post->post_excerpt;
    } else {
   if(preg_match('/<p>(.*)<\/p>/iU',trim(strip_tags($post->post_content,"<p>")),$result)){
    $post_content = $result['1'];
   } else {
    $post_content_r = explode("\n",trim(strip_tags($post->post_content)));
    $post_content = $post_content_r['0'];
   }
         $description = utf8Substr($post_content,0,220);  
  } 
    $keywords = "";     
    $tags = wp_get_post_tags($post->ID);
    foreach ($tags as $tag ) {
        $keywords = $keywords . $tag->name . ",";
    }
}
?>
<?php echo "\n"; ?>
<?php if ( is_single() ) { ?>
<meta name="description" content="<?php echo trim($description); ?>" />
<meta name="keywords" content="<?php echo rtrim($keywords,','); ?>" />
<?php } ?>
<?php if ( is_home() ) { ?>
<meta name="description" content="<?php echo get_option('swt_description'); ?>" />
<meta name="keywords" content="<?php echo get_option('swt_keywords'); ?>" />
<?php } ?>