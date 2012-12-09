<?php get_header(); ?>
<div id="wrapper">
    <div id="nav"><?php ATheme_menu('menu'); ?></div>
    <?php ATheme_bulletin(); ?>
    <div id="content_wrap">
    <div id="content">
            <div class="excerpt">
                <div class="context">
<div id="page_error"><center>
    <h3>Error 404 页面: <?php echo wcs_error_currentPageURL(); ?></h3>
    <p><b>糟糕</b> ... 搞砸了，感谢您发现了<?php bloginfo('name')?>的一个设计缺陷，<b>相信<?php bloginfo('name')?></b>...他一直在努力！</p>
    <h2>下一步该怎么做呢？下边整理的 9 条提示，希望可以帮到您：</h2>
        <ol>
            <li>
            发送邮件<b><a href="mailto:admin@andyshare.com" >通知</a></b><?php bloginfo('name')?>。
            </li>
            <li>
            返回 <b><a href="javascript:history.back()">上一页</a></b>。
            </li>
            <li>
            回到网站<b><a href="<?php bloginfo('siteurl');?>">首页</a></b>。
            </li>
            <li>
            尝试刷新页面 <b>（按F5）</b>。
            </li>
            <li>
            从顶部导航栏中选择<b>分类菜单</b>进行浏览。
            </li>
            <li>
            自定义搜索或按照页面、分类、页面进行<b>搜索</b>:
            <div class="error_box">
                <span>搜 索:</span>
                    <div class="error_extends">
                        <form style="margin-left: 0; margin-bottom: 5px;">
                        <input type="text" name="s" id="searchbox" value="输入关键字..." onfocus="if (this.value == '输入关键字...') {this.value = '';}" onblur="if (this.value == '') {this.value = '输入关键字...'}" />
                        <input type="submit" id="searchsubmits" value="搜 索"/>
                        </form>
                    </div>
                <div class="clear"></div>
                <span>按页面:</span>
                    <div class="error_extends">
                        <?php echo wcs_error_pulldown_pages(); ?>
                    </div>
                <div class="clear"></div>
                <span>按分类:</span>
                    <div class="error_extends">
                        <?php echo wcs_error_pulldown_categories(); ?>
                    </div>
                <div class="clear"></div>
                <span>按月份:</span>
                    <div class="error_extends">
                        <?php echo wcs_error_pulldown_archives(); ?>
                    </div>
                <div class="clear"></div>
            </div>
            </li>
            <li>
            等待一段时间，如果错误页面奇迹般地重定向到一个不同的页面，您便可以浏览了。<br/>（<i>提示</i>：事件发生的可能性为无限趋近于0. O(∩_∩)O~~ ）
            </li>
            <li>
            因为Andy无法满足您的期望，请访问别的网站。
            </li>
            <li>
            While you're here anyway, why not <b>buy the T-shirt</b>?
                <div style="background-image:url('<?php bloginfo('template_directory');?>/images/error_t-shirt.png');text-align:center; width:300px; min-height:297px !important; margin-top:10px; color:white;font-size:16px; line-height:150%;">
                <br/><br/>I Visited<br/>
                <b><?php bloginfo('name')?></b><br/>
                And All I Got<br/>
                Was THIS T-shirt !!!<br/>
                </div>
                </li>
        </ol>
</center></div>
                    </div>
            </div>
    </div>
    </div>
<?php get_sidebar(); ?>
</div>
<?php get_footer(); ?>