<div id="ap-lists" class="clearfix">
	<div class="ap-taxo-detail">
		<h2 class="entry-title"><?php echo $question_category->name; ?> <span class="ap-tax-item-count"><?php printf( _n('1 Question', '%s Questions', $question_category->count, 'ap'),  $question_category->count); ?></span></h2>
		<?php if($question_category->description !=''): ?>
			<p class="ap-taxo-description"><?php echo $question_category->description; ?></p>
		<?php else: ?>
			<p class="ap-taxo-description"><?php _e('-- No description --', 'ap'); ?></p>
		<?php endif; ?>
	</div>

	<?php
		$sub_cat_count = count(get_term_children( $question_category->term_id, 'question_category' ));
		
		if($sub_cat_count >0){
			echo '<div class="ap-term-sub">';
			echo '<div class="sub-taxo-label">' .$sub_cat_count.' '.__('Sub Categories', 'ap') .'</div>';
			ap_sub_category_list($question_category->term_id);
			echo '</div>';
		}
	?>

	<?php if ( $questions->have_posts() ) : ?>
		<?php ap_questions_tab(); ?>
		<div class="questions-list">
			<?php				
				/* Start the Loop */
				while ( $questions->have_posts() ) : $questions->the_post();
					include(ap_get_theme_location('content-list.php'));
				endwhile;
			?>
		</div>
		<?php ap_pagination(); ?>
	<?php
		else : 
			include(ap_get_theme_location('content-none.php'));
		endif; 
	?>	
</div>
