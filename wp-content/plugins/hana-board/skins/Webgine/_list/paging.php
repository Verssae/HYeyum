
<div class="row">
	<nav class="hana-pagination-nav clearfix" role="navigation">
		<ul class="hanaboard-pagination">
				<?php if (is_array($pagination)) { ?>
				<?php foreach ($pagination as $v) { ?>
					<li><?php echo $v; ?></li>
				<?php } ?>
				<?php } ?>
		</ul>
	</nav>
</div>