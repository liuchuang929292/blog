<?php
include("includes/shortcode.php");
include("includes/theme_options.php");

if ( function_exists('register_nav_menus') ) {
    register_nav_menus(array(
        'top_bar' => '顶部导航',
        'menu' => '导航菜单',
        'bottom_bar' => '底部导航'
    ));
}
add_filter('show_admin_bar', '__return_false');

//Anti-Spam 防止垃圾评论
class anti_spam {
  function anti_spam() {
    if ( !current_user_can('read') ) {
      add_action('template_redirect', array($this, 'w_tb'), 1);
      add_action('init', array($this, 'gate'), 1);
      add_action('preprocess_comment', array($this, 'sink'), 1);
    }
  }
  // 设栏位
  function w_tb() {
    if ( is_singular() ) {
      // 非中文语系
      if ( stripos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'zh') === false ) {
        add_filter( 'comments_open', create_function('', "return false;") ); // 关闭评论
      } else {
        ob_start(create_function('$input','return preg_replace("#textarea(.*?)name=([\"\'])comment([\"\'])(.+)/textarea>#",
        "textarea$1name=$2w$3$4/textarea><textarea name=\"comment\" cols=\"100%\" rows=\"4\" style=\"display:none\"></textarea>",$input);') );
      }
    }
  }
  // 检查
  function gate() {
    $w = 'w';
    if ( !empty($_POST[$w]) && empty($_POST['comment']) ) {
      $_POST['comment'] = $_POST[$w];
    } else {
      $request = $_SERVER['REQUEST_URI'];
      $way     = isset($_POST[$w]) ? '手动操作' : '未经评论表格';
      $spamcom = isset($_POST['comment']) ? $_POST['comment'] : '';
      $_POST['spam_confirmed'] = "请求: ". $request. "\n方式: ". $way. "\n内容: ". $spamcom. "\n -- 记录成功 --";
    }
  }
  // 处理
  function sink( $comment ) {
    // 不管 Trackbacks/Pingbacks
    if ( in_array( $comment['comment_type'], array('pingback', 'trackback') ) ) return $comment;

    // 已确定为 spam
    if ( !empty($_POST['spam_confirmed']) ) {
      // 方法一: 直接挡掉, 将 die(); 前面两斜线删除即可.
      //die();
      // 方法二: 标记为 spam, 留在数据库检查是否误判.
      add_filter('pre_comment_approved', create_function('', 'return "spam";'));
      $comment['comment_content'] = "[ 小墙判断这是Spam! ]\n". $_POST['spam_confirmed'];
      $this->add_black( $comment );
    } else {
      // 检查头像
      $f = md5( strtolower($comment['comment_author_email']) );
      $g = sprintf( "http://%d.gravatar.com", (hexdec($f{0}) % 2) ) .'/avatar/'. $f .'?d=404';
      $headers = @get_headers( $g );
      if ( !preg_match("|200|", $headers[0]) ) {
        // 没头像的列入待审
        add_filter('pre_comment_approved', create_function('', 'return "0";'));
        //$this->add_black( $comment );
        }
    }
    return $comment;
  }
  // 列入黑名单
  function add_black( $comment ) {
    if (!($comment_author_url = $comment['comment_author_url'])) return;
    if (strpos($comment_author_url, '//')) $comment_author_url = substr($comment_author_url, strpos($comment_author_url, '//') + 2);
    if (strpos($comment_author_url, '/'))  $comment_author_url = substr($comment_author_url, 0, strpos($comment_author_url, '/'));
    update_option('blacklist_keys', $comment_author_url . "\n" . get_option('blacklist_keys'));
  }
}
$anti_spam = new anti_spam();

//评论回复邮件通知（所有回复都邮件通知）
function comment_mail_notify($comment_id) {
  $comment = get_comment($comment_id);
  $parent_id = $comment->comment_parent ? $comment->comment_parent : '';
  $spam_confirmed = $comment->comment_approved;
  if (($parent_id != '') && ($spam_confirmed != 'spam')) {
    $wp_email = 'no-reply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME'])); //e-mail 发出点, no-reply 可改为可用的 e-mail.
    $to = trim(get_comment($parent_id)->comment_author_email);
    $subject = '您在 [' . get_option("blogname") . '] 的留言有了回复';
    $message = '
    <div style="background-color:#eef2fa; border:1px solid #d8e3e8; color:#111; padding:0 15px; -moz-border-radius:5px; -webkit-border-radius:5px; -khtml-border-radius:5px;">
      <p>' . trim(get_comment($parent_id)->comment_author) . '， 您好！</p>
      <p>您曾在《' . get_the_title($comment->comment_post_ID) . '》的留言：<br />
      <span style="font-weight:bold;">' . trim(get_comment($parent_id)->comment_content) . '</span></p>
      <p>-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-</p>
      <p>' . trim($comment->comment_author) . ' 给您的回复：<br />
      <span style="font-weight:bold;">' . trim($comment->comment_content) . '</span><br /></p>
      <p>您可以点击 <a href="' . htmlspecialchars(get_comment_link($parent_id, array('type' => 'comment'))) . '">查看回复完整內容</a></p>
      <p>-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-.-</p>
      <p style="color:#ff0000;">請勿回復本郵件，您的回復我無法收到.如果您有問題，請到 <a href="' . get_option('home') . '">' . get_option('blogname') . '</a> 給我留言.</p>
    </div>';
    $message = convert_smilies($message);
    $from = "From: \"" . get_option('blogname') . "\" <$wp_email>";
    $headers = "$from\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";
    wp_mail( $to, $subject, $message, $headers );
    //echo 'mail to ', $to, '<br/> ' , $subject, $message; // for testing
  }
}
add_action('comment_post', 'comment_mail_notify');

//日志归档
	class hacklog_archives
{
	function GetPosts() 
	{
		global  $wpdb;
		if ( $posts = wp_cache_get( 'posts', 'ihacklog-clean-archives' ) )
			return $posts;
		$query="SELECT DISTINCT ID,post_date,post_date_gmt,comment_count,comment_status,post_password FROM $wpdb->posts WHERE post_type='post' AND post_status = 'publish' AND comment_status = 'open'";
		$rawposts =$wpdb->get_results( $query, OBJECT );
		foreach( $rawposts as $key => $post ) {
			$posts[ mysql2date( 'Y.m', $post->post_date ) ][] = $post;
			$rawposts[$key] = null; 
		}
		$rawposts = null;
		wp_cache_set( 'posts', $posts, 'ihacklog-clean-archives' );;
		return $posts;
	}
	function PostList( $atts = array() ) 
	{
		global $wp_locale;
		global $hacklog_clean_archives_config;
		$atts = shortcode_atts(array(
			'usejs'        => $hacklog_clean_archives_config['usejs'],
			'monthorder'   => $hacklog_clean_archives_config['monthorder'],
			'postorder'    => $hacklog_clean_archives_config['postorder'],
			'postcount'    => '1',
			'commentcount' => '1',
		), $atts);
		$atts=array_merge(array('usejs'=>1,'monthorder'   =>'new','postorder'    =>'new'),$atts);
		$posts = $this->GetPosts();
		( 'new' == $atts['monthorder'] ) ? krsort( $posts ) : ksort( $posts );
		foreach( $posts as $key => $month ) {
			$sorter = array();
			foreach ( $month as $post )
				$sorter[] = $post->post_date_gmt;
			$sortorder = ( 'new' == $atts['postorder'] ) ? SORT_DESC : SORT_ASC;
			array_multisort( $sorter, $sortorder, $month );
			$posts[$key] = $month;
			unset($month);
		}
		$html = '<div class="car-container';
		if ( 1 == $atts['usejs'] ) $html .= ' car-collapse';
		$html .= '">'. "\n";
		if ( 1 == $atts['usejs'] ) $html .= '<a href="#" class="car-toggler">展开所有月份'."</a>\n\n";
		$html .= '<ul class="car-list">' . "\n";
		$firstmonth = TRUE;
		foreach( $posts as $yearmonth => $posts ) {
			list( $year, $month ) = explode( '.', $yearmonth );
			$firstpost = TRUE;
			foreach( $posts as $post ) {
				if ( TRUE == $firstpost ) {
                    $spchar = $firstmonth ? '<span class="car-toggle-icon car-minus">-</span>' : '<span class="car-toggle-icon car-plus">+</span>';
					$html .= '	<li><span class="car-yearmonth" style="cursor:pointer;">'.$spchar.' ' . sprintf( __('%1$s %2$d'), $wp_locale->get_month($month), $year );
					if ( '0' != $atts['postcount'] ) 
					{
						$html .= ' <span title="文章数量">(共' . count($posts) . '篇文章)</span>';
					}
                    if ($firstmonth == FALSE) {
					$html .= "</span>\n		<ul class='car-monthlisting' style='display:none;'>\n";
                    } else {
                    $html .= "</span>\n		<ul class='car-monthlisting'>\n";
                    }
					$firstpost = FALSE;
                     $firstmonth = FALSE;
				}
				$html .= '			<li>' .  mysql2date( 'd', $post->post_date ) . '日: <a target="_blank" href="' . get_permalink( $post->ID ) . '">' . get_the_title( $post->ID ) . '</a>';
				if ( '0' != $atts['commentcount'] && ( 0 != $post->comment_count || 'closed' != $post->comment_status ) && empty($post->post_password) )
					$html .= ' <span title="评论数量">(' . $post->comment_count . '条评论)</span>';
				$html .= "</li>\n";
			}
			$html .= "		</ul>\n	</li>\n";
		}
		$html .= "</ul>\n</div>\n";
		return $html;
	}
	function PostCount() 
	{
		$num_posts = wp_count_posts( 'post' );
		return number_format_i18n( $num_posts->publish );
	}
}
if(!empty($post->post_content))
{
	$all_config=explode(';',$post->post_content);
	foreach($all_config as $item)
	{
		$temp=explode('=',$item);
		$hacklog_clean_archives_config[trim($temp[0])]=htmlspecialchars(strip_tags(trim($temp[1])));
	}
}
else
{
	$hacklog_clean_archives_config=array('usejs'=>1,'monthorder'   =>'new','postorder'    =>'new');	
}
$hacklog_archives=new hacklog_archives();

//连接数量
$match_num_from = 1;  //一个关键字少于多少不替换
$match_num_to = 10; //一个关键字最多替换
//连接到WordPress的模块
add_filter('the_content','tag_link',1);
//按长度排序
function tag_sort($a, $b){
	if ( $a->name == $b->name ) return 0;
	return ( strlen($a->name) > strlen($b->name) ) ? -1 : 1;
}
//改变标签关键字
function tag_link($content) {
global $match_num_from,$match_num_to;
	 $posttags = get_the_tags();
	 if ($posttags) {
		 usort($posttags, "tag_sort");
		 foreach($posttags as $tag) {
			 $link = get_tag_link($tag->term_id);
			 $keyword = $tag->name;
			 //连接代码
			 $cleankeyword = stripslashes($keyword);
			 $url = "<a href=\"$link\" title=\"".str_replace('%s',addcslashes($cleankeyword, '$'),__('View all posts in %s'))."\"";
			 $url .= ' target="_blank" class="tag_link"';
			 $url .= ">".addcslashes($cleankeyword, '$')."</a>";
			 $limit = rand($match_num_from,$match_num_to);

			//不连接的代码
             $content = preg_replace( '|(<a[^>]+>)(.*)('.$ex_word.')(.*)(</a[^>]*>)|U'.$case, '$1$2%&&&&&%$4$5', $content);
			 $content = preg_replace( '|(<img)(.*?)('.$ex_word.')(.*?)(>)|U'.$case, '$1$2%&&&&&%$4$5', $content);
				$cleankeyword = preg_quote($cleankeyword,'\'');
					$regEx = '\'(?!((<.*?)|(<a.*?)))('. $cleankeyword . ')(?!(([^<>]*?)>)|([^>]*?</a>))\'s' . $case;
				$content = preg_replace($regEx,$url,$content,$limit);
	$content = str_replace( '%&&&&&%', stripslashes($ex_word), $content);
		 }
	 }
    return $content;
}

//404页面
function wcs_error_currentPageURL()
{
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80")
	{
		$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
	}
	else
	{
		$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

class wcs_error_Walker_PageDropdownEx extends Walker
{
	// returns options list with pages; option value is URL (not page ID)
	var $db_fields = array ('parent' => 'post_parent', 'id' => 'ID');

	function start_el(&$output, $page, $depth, $args)
	{
		$pad = str_repeat('&nbsp;', $depth * 3);

		$output .= "\t<option class=\"level-$depth\" value=\"" . get_permalink($page->ID) . "\"";
		if ($page->ID == $args['selected'])
		{
			$output .= ' selected="selected"';
		}
	$output .= '>';
	$title = esc_html($page->post_title);
	$output .= "$pad$title";
	$output .= "</option>\n";
	}
}
function wcs_error_pulldown_pages()
{
	// init
	$walker = new wcs_error_Walker_PageDropdownEx();
	$args = array();
	$args['echo'] = '0';
	$args['walker'] = $walker;

	// build the pulldown menu
	$options_html = "\t<option>(选择页面)</option>";
	$options_html .= wp_list_pages($args);
	$select_html = '<select name="page_url" onChange="document.location.href=this.options[this.selectedIndex].value;">';
	$select_html .= $options_html . '</select>';

	// exit
	return $select_html;
}
function wcs_error_pulldown_categories()
{
	// build the pulldown menu
	$options_html = "\t<option>(选择分类)</option>";
	$args['echo'] = '0';
	$args['show_count'] = '1';
	$args['hierarchical'] = '1';

	$wp_post_count = wp_count_posts();
	if ($wp_post_count->publish <= 1)
	{
		$select_html = '(none)';
	}
	else
	{
		$options_html .= wp_dropdown_categories($args);
		$options_html = str_replace("<select name='cat' id='cat' class='postform' >", '', $options_html);
		$options_html = str_replace('</select>', '', $options_html);

		$select_html = '<form method="get" action="' . get_bloginfo('url') . '">';
		$select_html .= '<select name="cat" id="cat" onChange="this.form.submit()">';
		$select_html .= $options_html . '</select></form>';
	}

	// exit
	return $select_html;
}
function wcs_error_pulldown_archives()
{
	// build the pulldown menu
	$options_html = "\t<option>(选择月份)</option>";
	$args['echo'] = '0';
	$args['format'] = 'option';
	$args['type'] = 'monthly';
	$args['show_post_count'] = 'true';

	$wp_post_count = wp_count_posts();
	if ($wp_post_count->publish <= 1)
	{
		$select_html = '(none)';
	}
	else
	{
		$options_html .= wp_get_archives($args);
		$select_html = '<select name="archive_url" onChange="document.location.href=this.options[this.selectedIndex].value;">';
		$select_html .= $options_html . '</select>';
	}

	// exit
	return $select_html;
}

//密码保护提示
add_filter('the_content', 'ATheme_password_hint');
function ATheme_password_hint( $c ) {
global $post, $user_ID, $user_identity;
if ( empty($post->post_password) )
return $c;
if ( isset($_COOKIE['wp-postpass_'.COOKIEHASH]) && stripslashes($_COOKIE['wp-postpass_'.COOKIEHASH]) == $post->post_password )
return $c;
if($hint = get_post_meta($post->ID, 'password_hint', true)){
$url = get_option('siteurl').'/wp-pass.php';
if($hint)
$hint = '密码提示：'.$hint;
else
$hint = "请输入您的密码";
if($user_ID)
$hint .= sprintf('欢迎进入，您的密码是：', $user_identity, $post->post_password);
$out = <<<END
<form method="post" action="$url">
<p>这篇文章是受保护的文章，请输入密码继续阅读：</p>
<div>
<label>$hint<br/>
<input type="password" name="post_password"/></label>
<input type="submit" value="输入密码" name="Submit"/>
</div>
</form>
END;
return $out;
}else{
return $c;
}
}

//支持外链缩略图
if ( function_exists('add_theme_support') )
add_theme_support('post-thumbnails');
function ATheme_catch_first_image() {
  global $post, $posts;
  $first_img = '';
  ob_start();
  ob_end_clean();
  $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
  $first_img = $matches [1] [0];
  if(empty($first_img)){
		$random = mt_rand(1, 20);
		echo get_bloginfo ( 'stylesheet_directory' );
		echo '/images/random/ATheme'.$random.'.jpg';
  }
  return $first_img;
}

//登陆显示头像
function ATheme_get_avatar($email, $size = 48) {
return get_avatar($email, $size);
}

//自定义头像
add_filter( 'avatar_defaults', 'ATheme_addgravatar' );
function ATheme_addgravatar( $avatar_defaults ) {
$myavatar = get_bloginfo('template_directory') . '/images/gravatar.png';
  $avatar_defaults[$myavatar] = '自定义头像';
  return $avatar_defaults;
}

//自动生成版权时间
function ATheme_comicpress_copyright() {
    global $wpdb;
    $copyright_dates = $wpdb->get_results("
    SELECT
    YEAR(min(post_date_gmt)) AS firstdate,
    YEAR(max(post_date_gmt)) AS lastdate
    FROM
    $wpdb->posts
    WHERE
    post_status = 'publish'
    ");
    $output = '';
    if($copyright_dates) {
    $copyright = "&copy; " . $copyright_dates[0]->firstdate;
    if($copyright_dates[0]->firstdate != $copyright_dates[0]->lastdate) {
    $copyright .= '-' . $copyright_dates[0]->lastdate;
    }
    $output = $copyright;
    }
    return $output;
}

//标题文字截断
function ATheme_cut_str($src_str,$cut_length) {
    $return_str='';
    $i=0;
    $n=0;
    $str_length=strlen($src_str);
    while (($n<$cut_length) && ($i<=$str_length))
    {
        $tmp_str=substr($src_str,$i,1);
        $ascnum=ord($tmp_str);
        if ($ascnum>=224)
        {
            $return_str=$return_str.substr($src_str,$i,3);
            $i=$i+3;
            $n=$n+2;
        }
        elseif ($ascnum>=192)
        {
            $return_str=$return_str.substr($src_str,$i,2);
            $i=$i+2;
            $n=$n+2;
        }
        elseif ($ascnum>=65 && $ascnum<=90)
        {
            $return_str=$return_str.substr($src_str,$i,1);
            $i=$i+1;
            $n=$n+2;
        }
        else 
        {
            $return_str=$return_str.substr($src_str,$i,1);
            $i=$i+1;
            $n=$n+1;
        }
    }
    if ($i<$str_length)
    {
        $return_str = $return_str . '';
    }
    if (get_post_status() == 'private')
    {
        $return_str = $return_str . '（private）';
    }
    return $return_str;
}

//分页功能
function ATheme_pagination($query_string) {
global $posts_per_page, $paged;
$my_query = new WP_Query($query_string ."&posts_per_page=-1");
$total_posts = $my_query->post_count;
if(empty($paged))$paged = 1;
$prev = $paged - 1;							
$next = $paged + 1;	
$range = 5; // 修改数字,可以显示更多的分页链接
$showitems = ($range * 2)+1;
$pages = ceil($total_posts/$posts_per_page);
if(1 != $pages){
	echo "<div class='pagination'>";
	echo ($paged > 2 && $paged+$range+1 > $pages && $showitems < $pages)? "<a href='".get_pagenum_link(1)."' class='fir_las'>最前</a>":"";
	echo ($paged > 1 && $showitems < $pages)? "<a href='".get_pagenum_link($prev)."' class='page_previous'>« 上一页</a>":"";		
	for ($i=1; $i <= $pages; $i++){
	if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems )){
	echo ($paged == $i)? "<span class='current'>".$i."</span>":"<a href='".get_pagenum_link($i)."' class='inactive' >".$i."</a>"; 
	}
	}
	echo ($paged < $pages && $showitems < $pages) ? "<a href='".get_pagenum_link($next)."' class='page_next'>下一页 »</a>" :"";
	echo ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) ? "<a href='".get_pagenum_link($pages)."' class='fir_las'>最后</a>":"";
	echo "</div>\n";
	}
}

// 评论回复&头像缓存
function ATheme_comment($comment, $args, $depth) {
   $GLOBALS['comment'] = $comment;
global $commentcount,$wpdb, $post;
     if(!$commentcount) { //初始化楼层计数器
          $comments = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_post_ID = $post->ID AND comment_type = '' AND comment_approved = '1' AND !comment_parent");
          $cnt = count($comments);//获取主评论总数量
          $page = get_query_var('cpage');//获取当前评论列表页码
          $cpp=get_option('comments_per_page');//获取每页评论显示数量
         if (ceil($cnt / $cpp) == 1 || ($page > 1 && $page  == ceil($cnt / $cpp))) {
             $commentcount = $cnt + 1;//如果评论只有1页或者是最后一页，初始值为主评论总数
         } else {
             $commentcount = $cpp * $page + 1;
         }
     }
?>
<li <?php comment_class(); ?> id="comment-<?php comment_ID() ?>">
   <div id="div-comment-<?php comment_ID() ?>" class="comment-body">
      <?php $add_below = 'div-comment'; ?>
		<div class="comment-author vcard"><?php if (get_option('swt_type') == 'Display') { ?>
			<?php
				$p = 'avatar/';
				$f = md5(strtolower($comment->comment_author_email));
				$a = $p . $f .'.jpg';
				$e = ABSPATH . $a;
				if (!is_file($e)){ //当头像不存在就更新
				$d = get_bloginfo('wpurl'). '/avatar/default.jpg';
				$s = '40'; //头像大小 自行根据自己模板设置
				$r = get_option('avatar_rating');
				$g = 'http://www.gravatar.com/avatar/'.$f.'.jpg?s='.$s.'&d='.$d.'&r='.$r;
                $avatarContent = file_get_contents($g);
                file_put_contents($e, $avatarContent);
				if ( filesize($e) == 0 ){ copy($d, $e); }
				};
			?>
			<img src='<?php bloginfo('wpurl'); ?>/<?php echo $a ?>' alt='' class='avatar' />
                <?php { echo ''; } ?>
			<?php } else { echo get_avatar( $comment, 40 );} ?>
					<div class="floor"><?php
 if(!$parent_id = $comment->comment_parent){
   switch ($commentcount){
     case 2 :echo "沙发";--$commentcount;break;
     case 3 :echo "板凳";--$commentcount;break;
     case 4 :echo "地板";--$commentcount;break;
     default:printf('%1$s楼', --$commentcount);
   }
 }
 ?>
         </div><strong><?php comment_author_link() ?></strong>
<?php if(user_can($comment->user_id, 1)){echo "<img src='", bloginfo('template_directory') ,"/images/vip_mark.png' title='博主认证' alt='（博主认证）' style='width:16px;height:16px;margin-bottom:-2px;display:inline;' />";
}; ?>&nbsp;&nbsp;<span class="edit_comment"><?php edit_comment_link('[编辑]','&nbsp;&nbsp;',''); ?></span></div>
		<?php if ( $comment->comment_approved == '0' ) : ?>
			<span style="color:#f00; font-style:inherit">您的评论正在等待审核中...</span>
			<br />			
		<?php endif; ?>
		<?php comment_text() ?>
		<div class="clear"></div><span class="datetime"><?php ATheme_time_diff( $time_type = 'comment' ); ?></span><span class="reply">&nbsp;<?php comment_reply_link(array_merge( $args, array('reply_text' => '@回复', 'add_below' =>$add_below, 'depth' => $depth, 'max_depth' => $args['max_depth']))); ?></span>
  </div>
<?php
}
function ATheme_end_comment() {
		echo '</li>';
}

//向来自其他域的访客致欢迎词
function ATheme_set_notify_cookie() {
    if(empty($_COOKIE['notify_cookie']))
    setcookie('notify_cookie',md5($_SERVER['REMOTE_ADDR'].$_SERVER['USER_AGENT']),time()+3600*24*30);
}
add_action('init','ATheme_set_notify_cookie');
function ATheme_show_notify() {
    $show=0;
    $extra_msg='';
    if( isset($_SERVER['HTTP_REFERER']) )
    {
         $url = parse_url($_SERVER['HTTP_REFERER']);
         if(isset($url['port']))
            $ref_host=$url['host'] .':' . $url['port'];
         else
             $ref_host=$url['host'];
         if( $ref_host != $_SERVER['HTTP_HOST'])
         {
             $show=1;
             $ref_url=$url['scheme']. '://' .$ref_host;
             $extra_msg='<a href="'.$ref_url.'"  target="_blank">'.$ref_host.'</a>';
         }
    }
    if(empty($_COOKIE['notify_cookie']) || $show)
	if (get_option('swt_welcome') == 'Display') {
    echo "<div id=\"hellovisitor\">来自" . $extra_msg ."的朋友,欢迎您 <b><a href=\"";
    echo stripslashes(get_option('swt_feedurl'));
	echo "\" target=\"_blank\">点击这里</a></b> 订阅我的博客 o(∩_∩)o~~~<div class=\"closebox\"><a href=\"javascript:void(0)\" onclick=\"$('#hellovisitor').slideUp('slow');$('.closebox').css('display','none');\" title=\"关闭\">×</a></div></div>";
	}
}

//wordpress自带编辑器增强代码
add_filter("mce_buttons_3", "ATheme_enable_more_buttons");
function ATheme_enable_more_buttons($buttons) { 
$buttons[] = 'hr'; 
$buttons[] = 'sub';
$buttons[] = 'sup';
$buttons[] = 'fontselect';
$buttons[] = 'fontsizeselect';
$buttons[] = 'styleselect';
$buttons[] = 'backcolor';
$buttons[] = 'cleanup';
$buttons[] = 'wp_page';
return $buttons; 
} 

// 获得热评文章
function ATheme_get_most_viewed($posts_num=10, $days=30) {
    global $wpdb;
    $sql = "SELECT ID , post_title , comment_count
            FROM $wpdb->posts
           WHERE post_type = 'post' AND TO_DAYS(now()) - TO_DAYS(post_date) < $days
		   AND ($wpdb->posts.`post_status` = 'publish' OR $wpdb->posts.`post_status` = 'inherit')
           ORDER BY comment_count DESC LIMIT 0 , $posts_num ";
    $posts = $wpdb->get_results($sql);
    $output = "";
    foreach ($posts as $post){
        $output .= "\n<li><a href= \"".get_permalink($post->ID)."\" rel=\"bookmark\" title=\"".$post->post_title." (".$post->comment_count."条评论)\" >". mb_strimwidth($post->post_title,0,36)."</a></li>";
    }
    echo $output;
}

//Feed中添加版权信息
add_filter('the_content', 'ATheme_feed_copyright');
function ATheme_feed_copyright($content) {    
        if(is_feed()) {                    
                $content.= '<div>声明: 本文采用 <a rel="external" href="http://creativecommons.org/licenses/by-nc-sa/3.0/deed.zh" title="署名-非商业性使用-相同方式共享 3.0 Unported">CC BY-NC-SA 3.0</a> 协议进行授权</div>';
                $content.= '<div>转载请注明来源：<a rel="external" title="'.get_bloginfo('name').'" href="'.get_permalink().'">'.get_bloginfo('name').'</a></div>';    
                $content.= '<div>本文链接地址：<a rel="external" title="'.get_the_title().'" href="'.get_permalink().'">'.get_permalink().'</a></div>';                    
        }
        return $content;    
}    

//评论表情路径
add_filter('smilies_src','ATheme_custom_smilies_src',1,10);
function ATheme_custom_smilies_src ($img_src, $img, $siteurl) {
     return get_bloginfo('template_directory').'/images/smiley/'.$img;
}

//评论者链接重定向
add_filter('get_comment_author_link', 'ATheme_add_redirect_comment_link', 5);
add_filter('comment_text', 'ATheme_add_redirect_comment_link', 99);
function ATheme_add_redirect_comment_link($text = '') {
    $text=str_replace('href="', 'href="'.get_option('home').'/?r=', $text);
    $text=str_replace("href='", "href='".get_option('home')."/?r=", $text);
    return $text;
}
add_action('init', 'ATheme_redirect_comment_link');
function ATheme_redirect_comment_link() {
    $redirect = $_GET['r'];
    if($redirect){
        if(strpos($_SERVER['HTTP_REFERER'],get_option('home')) !== false){
            header("Location: $redirect");
            exit();
        }
        else {
            header ( "Location: " . bloginfo ( 'url' ) . "/" );
            exit();
        }
    }
}

//暗箱效果自动添加标签属性 
add_filter('the_content', 'ATheme_pirobox_gall_replace');    
function ATheme_pirobox_gall_replace ($content) {
	global $post;    
    $pattern = "/<a(.*?)href=('|\")([^>]*).(bmp|gif|jpeg|jpg|png)('|\")(.*?)>(.*?)<\/a>/i";    
    $replacement = '<a$1href=$2$3.$4$5 class="pirobox_gall"$6>$7</a>';    
    $content = preg_replace($pattern, $replacement, $content);    
    return $content;    
}

//显示最近评论次数
function ATheme_WelcomeCommentAuthorBack($email = ''){
	if(empty($email)){
		return;
	}
	global $wpdb;
	$past_30days = gmdate('Y-m-d H:i:s',((time()-(24*60*60*30))+(get_option('gmt_offset')*3600)));
	$sql = "SELECT count(comment_author_email) AS times FROM $wpdb->comments
					WHERE comment_approved = '1'
					AND comment_author_email = '$email'
					AND comment_date >= '$past_30days'";
	$times = $wpdb->get_results($sql);
	$times = ($times[0]->times) ? $times[0]->times : 0;
	$message = $times ? sprintf(__('过去30天内您评论了<strong>%1$s</strong>次，感谢关注~' ), $times) : '您很久都没有留言了，这次想说点什么吗？';
	return $message;
}

//日志与评论的相对时间显示
function ATheme_time_diff( $time_type ) {
    switch( $time_type ){
        case 'comment':    //如果是评论的时间
            $time_diff = current_time('timestamp') - get_comment_time('U');
            if( $time_diff <= 86400 )    //24 小时之内
                echo human_time_diff(get_comment_time('U'), current_time('timestamp')).' 之前';    //显示格式 OOXX 之前
            else
                printf(__('%1$s at %2$s'), get_comment_date(),  get_comment_time());    //显示格式 X年X月X日 OOXX 时
            break;
        case 'post';    //如果是日志的时间
            $time_diff = current_time('timestamp') - get_the_time('U');
            if( $time_diff <= 86400 )
                echo human_time_diff(get_the_time('U'), current_time('timestamp')).'前';
            else
                the_time('Y-m-d H:i');
            break;
    }
}

//回复内容可见				
function ATheme_reply_to_read($atts, $content=null) {
        extract(shortcode_atts(array("notice" => '<p class="reply-to-read"><strong style="color:#f00;">温馨提示:</strong> 此处内容需要您<a href="#respond" title="评论本文">评论本文</a>后才能查看!</p>'), $atts));   
        $email = null;   
        $user_ID = (int) wp_get_current_user()->ID;   
        if ($user_ID > 0) {   
            $email = get_userdata($user_ID)->user_email;   
            //对博主直接显示内容   
            $admin_email = get_bloginfo ('admin_email');  
            if ($email == $admin_email) {   
                return $content;   
            }   
        } else if (isset($_COOKIE['comment_author_email_' . COOKIEHASH])) {   
            $email = str_replace('%40', '@', $_COOKIE['comment_author_email_' . COOKIEHASH]);   
        } else {   
            return $notice;   
        }   
        if (empty($email)) {   
            return $notice;   
        }   
        global $wpdb;   
        $post_id = get_the_ID();   
        $query = "SELECT `comment_ID` FROM {$wpdb->comments} WHERE `comment_post_ID`={$post_id} and `comment_approved`='1' and `comment_author_email`='{$email}' LIMIT 1";   
        if ($wpdb->get_results($query)) {   
            return do_shortcode($content);   
        } else {   
            return $notice;   
        }   
    }   
add_shortcode('reply', 'ATheme_reply_to_read');

//移除头部多余信息
remove_action('wp_head','wp_generator');//禁止在head泄露wordpress版本号
remove_action('wp_head','rsd_link');//移除head中的rel="EditURI"
remove_action('wp_head','wlwmanifest_link');//移除head中的rel="wlwmanifest"
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );//rel=pre
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0 );//rel=shortlink 
remove_action('wp_head', 'rel_canonical' );

//禁用半角符号自动转换为全角
remove_filter('the_content', 'wptexturize');

//评论跳转链接添加nofollow
function ATheme_nofollow_compopup_link() {
  return' rel="nofollow"';
}
add_filter('comments_popup_link_attributes','ATheme_nofollow_compopup_link');

///*回复某人链接添加nofollow 这个理应是原生的, 可是在wp某次改版后被改动了,现在是仅当开启注册回复时才有nofollow,否则需要自己手动了*/
function ATheme_nofollow_comreply_link($link) {
  return str_replace('&amp;amp;lt;a','&amp;amp;lt;a rel="nofollow"',$link);
}
get_option('comment_registration')||
add_filter('comment_reply_link','ATheme_nofollow_comreply_link');

//阻止站内文章pingback
function ATheme_no_self_ping( &$links ) {
$home = get_option( 'home' );
foreach ( $links as $l => $link )
if ( 0 === strpos( $link, $home ) )
unset($links[$l]);
}
add_action( 'pre_ping', 'ATheme_no_self_ping' );

//wordpress文章里url生成超链接
add_filter('the_content', 'make_clickable');

//去除评论url超链接
remove_filter('comment_text', 'make_clickable', 9); 

//禁止自动保存和修改历史记录
add_action('wp_print_scripts', 'no_autosave');
remove_action('pre_post_update','wp_save_post_revision');
function no_autosave() {
  wp_deregister_script('autosave');
}

//添加编辑器快捷按钮
add_action('admin_print_scripts', 'ATheme_my_quicktags');
function ATheme_my_quicktags() {
    wp_enqueue_script(
        'my_quicktags',
        get_stylesheet_directory_uri().'/js/my_quicktags.js',
        array('quicktags')
    );
}

//文字分享
function ATheme_txt_share() {
	echo '<div class="share-txt share-home">分享到：<a class="Ashare A_qzone">QQ空间</a><a class="Ashare A_tqq">腾讯微博</a><a class="Ashare A_sina">新浪微博</a><a class="Ashare A_wangyi">网易微博</a><a class="Ashare A_renren">人人网</a><a class="Ashare A_kaixin">开心网</a><a class="Ashare A_xiaoyou">腾讯朋友</a><a class="Ashare A_baidu">百度搜藏</a></div>';
}

// 图标分享
function ATheme_pic_share() {
    echo '<!-- JiaThis Button BEGIN -->
<div id="ckepop">
<a class="jiathis_button_qzone"></a>
<a class="jiathis_button_tqq"></a>
<a class="jiathis_button_tsina"></a>
<a class="jiathis_button_t163"></a>
<a class="jiathis_button_renren"></a>
<a class="jiathis_button_kaixin001"></a>
<a class="jiathis_button_xiaoyou"></a>
<a class="jiathis_button_baidu"></a>
<a class="jiathis_button_xianguo"></a>
<a class="jiathis_button_msn"></a>
<a class="jiathis_button_fb"></a>
<a class="jiathis_button_twitter"></a>
<a class="jiathis_counter_style"></a>
</div>
<script type="text/javascript" >
var jiathis_config={
	summary:"",
	hideMore:false
}
</script>
<script type="text/javascript" src="http://v3.jiathis.com/code/jia.js" charset="utf-8"></script>
<!-- JiaThis Button END -->';
}

//菜单
function ATheme_menu($type) {
	echo '<ul class="' . $type . '">' . str_replace ( "</ul></div>", "", ereg_replace ( "<div[^>]*><ul[^>]*>", "", wp_nav_menu ( array (
			'theme_location' => $type,
			'echo' => false 
	) ) ) ) . '</ul>';
}

//标签
function ATheme_tags() {
    $posttags = get_the_tags();
    if ($posttags){
    foreach($posttags as $tag)
    echo '<a class="tag-link tag-link-' . $tag->term_id . '" href="'.get_tag_link($tag).'">'. $tag->name .'</a>';
    }
}

//logo
function ATheme_logo() {
    if (get_option('swt_logo') == 'Display'){
    echo '<a href="' . get_bloginfo ( 'url' ) . '" title="' . get_bloginfo ( 'name' ) . ' - ' . get_bloginfo ( 'description' ) . '"><span class="logo"></span></a>';
	{echo '';}
	} else {
	echo '<h1 id="blogname"><a href="' . get_bloginfo ( 'url' ) . '" title="' . get_bloginfo ( 'description' ) . '">' . get_bloginfo ( 'name' ) . '</a></h1>';
	}	
}

//公告栏
function ATheme_bulletin() {
    if (get_option('swt_bulletin') == 'Display'){
		echo '<div id="bulletin"><ul><li>';
		echo get_option('swt_gg1');
		echo '</li>';
		echo '<li>';
		echo get_option('swt_gg2');
		echo '</li>';
		echo '<li>';
		echo get_option('swt_gg3');
		echo '</li></ul></div>';
	{echo '';}
	} else {}	
}

//面包屑导航
function ATheme_breadcrunbs() {
    if (is_single()) {
        echo '<div id="breadcrumbs"><a href="' . get_bloginfo ( 'url' ) . '" title="' . get_bloginfo ( 'name' ) . '">' . get_bloginfo ( 'name' ) . '</a> » ';
		$category = get_the_category();
		if ($category) {
            echo '<a href="' . get_category_link( $category[0]->term_id ) . '" title="' . sprintf( __( "View all posts in %s" ), $category[0]->name ) . '" ' . '>' . $category[0]->name.'</a> » ';
		}
		echo the_title();
        echo '</div>';
	} else if (is_home()) {
        echo '<div id="breadcrumbs">当前位置 : <a href="' . get_bloginfo ( 'url' ) . '" title="' . get_bloginfo ( 'name' ) . '">首页</a></div>';
	} else if (is_category()) {
        echo '<div id="breadcrumbs"><a href="' . get_bloginfo ( 'url' ) . '" title="' . get_bloginfo ( 'name' ) . '">' . get_bloginfo ( 'name' ) . '</a> » 所有属于 "';
		echo single_cat_title();
        echo '" 分类的文章</div>';
	} else if (is_tag()) {
        echo '<div id="breadcrumbs"><a href="' . get_bloginfo ( 'url' ) . '" title="' . get_bloginfo ( 'name' ) . '">' . get_bloginfo ( 'name' ) . '</a> » 所有属于 "';
		echo single_cat_title();
        echo '" 标签的文章</div>';
	} else if (is_page()) {
        echo '<div id="breadcrumbs"><a href="' . get_bloginfo ( 'url' ) . '" title="' . get_bloginfo ( 'name' ) . '">' . get_bloginfo ( 'name' ) . '</a> » ';
        echo the_title();
		echo '</div>';
	} else if (is_404()) {
        echo '<div id="breadcrumbs"><a href="' . get_bloginfo ( 'url' ) . '" title="' . get_bloginfo ( 'name' ) . '">' . get_bloginfo ( 'name' ) . '</a> » ';
        echo _e('未找到指定的页面( ERROR 404 )');
		echo '</div>';
	} else if (is_archive()) {
		echo '<div id="breadcrumbs"><a href="' . get_bloginfo ( 'url' ) . '" title="' . get_bloginfo ( 'name' ) . '">' . get_bloginfo ( 'name' ) . '</a> » ';
		$post = $posts[0];
		if (is_day()) {
            echo '所有 "';
			echo the_time('Y年m月d日');
			echo '" 的文章';
		} elseif (is_month()) {
		    echo '所有 "';
			echo the_time('Y年m月');
			echo '" 的文章';	
		} elseif (is_year()) {
		    echo '所有 "';
			echo the_time('Y年');
			echo '" 的文章';}
		echo '</div>';
	} if (is_search()) {
		echo '<div id="breadcrumbs"><a href="' . get_bloginfo ( 'url' ) . '" title="' . get_bloginfo ( 'name' ) . '">' . get_bloginfo ( 'name' ) . '</a> » 关键词 "';
		echo the_search_query();
		echo '" 的搜索结果';
		echo '</div>';
	} else {
	}
}


//全部设置结束
?>