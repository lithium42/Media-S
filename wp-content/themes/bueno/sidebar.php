<div id="sidebar" class="col-right">
		
	<!-- Widgetized Sidebar -->	
	<?php woo_sidebar(1); ?>	
	<!-- Contact info -->
	<div class="widget">
		<h3>Informations</h3>
		Tel: 
		<?php if (function_exists('contact_detail')) { 
			contact_detail('phone'); 
		}?><br/>
		Fax: 
		<?php if (function_exists('contact_detail')) { 
			contact_detail('fax'); 
		}?><br/>		
		<br/>
		<a href='<?php bloginfo('url'); ?>/contact-2'>Contactez-nous ici</a>
	</div>
</div><!-- /#sidebar -->


<div id="center_block">
	<div class="block left">
		<div class="widget widget_tag_cloud">
			<h3>Actualité</h3>
		</div>			
			<?php lastPosts(1,’extrait’); ?>
			
		<?php woo_sidebar('center-1'); ?>

	</div><!-- /.block -->
	<div class="block right">
		
		<?php woo_sidebar('center-2'); ?>

	</div><!-- /.block -->
</div><!-- /#center_block-->