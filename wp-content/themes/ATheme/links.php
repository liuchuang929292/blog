<?php
/*
Template Name: Links(友情链接)
*/
?>
<?php get_header(); ?>
<script type="text/javascript">
jQuery(document).ready(function($){
$(".Alinks a").each(function(e){
	$(this).prepend("<img src=http://www.google.com/s2/favicons?domain="+this.href.replace(/^(http:\/\/[^\/]+).*$/, '$1').replace( 'http://', '' )+" style=float:left;padding:5px;>");
}); 
});
</script>
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
<h2 style="color:#f00">友情链接</h2>
<div class="Alinks">
<ul><?php wp_list_bookmarks('orderby=id&category_orderby=id'); ?></ul>
</div>
<div class="clear"></div>
<div class="linkstandard">
<h2 style="color:#f00">申请友情链接前请看：</h2>
<ul>
<li>一、<span style="color:#f06">谢绝第一次来博客就申请友情链接</span>，在做链接前我希望的是交流，先友情后链接;</li>
<li>二、您的站点没有违反中华人民共和国各项法律、法规的内容,没有违背社会道德之内容;</li>
<li>三、在您申请本博友情链接之前请先做好本博链接，否则不会通过，谢谢;</li>
<li>四、您的站点在baidu 和 google有正常收录，且内容经常更新;</li>
<li>五、本博会定期检查失效链接，请更换域名的朋友及时留言说明;</li>
<li>六、未经通知私自删除本博链接者，永久不再友链;</li>
<li>七、本博客链接信息：</li>
<li>『博客名称』<?php bloginfo('name'); ?></li>
<li>『博客地址』<?php bloginfo('siteurl'); ?></li>
<li>『博客简介』<?php bloginfo('description'); ?></li>
</ul>
</div>
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