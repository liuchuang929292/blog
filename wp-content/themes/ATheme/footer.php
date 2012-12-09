<div id="footer">
    <div id="footer_inner">
        <div class="copyright">
            <?php ATheme_menu(bottom_bar); ?>
            版权所有，保留一切权利！ <?php echo ATheme_comicpress_copyright(); ?> <?php bloginfo('name'); ?>. Powered by <a href="http://wordpress.org/" rel="external">WordPress</a>. Theme by <a href="http://andyshare.com/" rel="external">Andy</a>.
            <?php if (get_option('swt_statistics') == 'Display') { ?><?php echo stripslashes(get_option('swt_statisticscode')); ?>
            <?php { echo ''; } ?><?php } else { } ?>
        </div>
    </div>
</div>
<?php if (get_option('swt_wpshare') == 'Display') { ?>
<script type="text/javascript" src="<?php bloginfo('stylesheet_directory'); ?>/js/wpshare.js"></script>
<?php { echo ''; } ?><?php } else { } ?>
<?php ATheme_show_notify(); ?>
</body>
</html>