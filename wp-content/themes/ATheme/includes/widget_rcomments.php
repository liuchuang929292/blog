<h3>最新评论</h3>
<div class="rcomments-content">
<ul>
<?php
$limit_num = '10'; //这里定义显示的评论数量
$my_email = "'" . get_bloginfo ('admin_email') . "'"; //这里是自动检测博主的邮件，实现博主的评论不显示
$rc_comms = $wpdb->get_results("
 SELECT ID, post_title, comment_ID, comment_author, comment_author_email, comment_content
 FROM $wpdb->comments LEFT OUTER JOIN $wpdb->posts
 ON ($wpdb->comments.comment_post_ID = $wpdb->posts.ID)
 WHERE comment_approved = '1'
 AND comment_type = ''
 AND post_password = ''
 AND comment_author_email != $my_email
 ORDER BY comment_date_gmt
 DESC LIMIT $limit_num
 ");
$rc_comments = '';
foreach ($rc_comms as $rc_comm) { //get_avatar($rc_comm,$size='50')
$rc_comments .= "<li>". get_avatar($rc_comm,$size='50') ."<span class='zsnos_comment_author'>" . $rc_comm->comment_author . ": </span><a href='"
. get_permalink($rc_comm->ID) . "#comment-" . $rc_comm->comment_ID
//. htmlspecialchars(get_comment_link( $rc_comm->comment_ID, array('type' => 'comment'))) // 可取代上一行, 会显示评论分页ID, 但较耗资源
. "' title='on " . $rc_comm->post_title . "'>" . strip_tags($rc_comm->comment_content)
. "</a></li>\n";
}
$rc_comments = convert_smilies($rc_comments);
echo $rc_comments;
?>
</ul>
</div>