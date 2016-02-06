<?php
/*
Template Name: Full Width Resource Listing Page
*/
?>
<?php get_header(); ?>

<div id="content">
	<div class="container clearfix fullwidth">
		<div id="left-area">
		<?php
		$resources = get_posts( array(
			'numberposts' => -1, // we want to retrieve all of the posts
			'post_type' => 'resources',
			'suppress_filters' => false, // this argument is required for CPT-onomies
			'tax_query' => array(
				array(
					'taxonomy' => 'resource_categories',
					'field' => 'id', // can be slug or id - a CPT-onomy term's ID is the same as its post ID
					'terms' => 57,
				)
			)
		) );

		if( $resources ) {
			?><h3>Resources</h3>
			<ul><?php
			foreach ( $resources as $resource ) {
				?><li><a href="<?php echo get_permalink( $resource->ID ) ?>" title="<?php echo get_the_title( $resource->ID ); ?>"><?php echo get_the_title( $resource->ID ); ?></a></li><?php
			}
			?></ul><?php
		}
		?>
		</div> <!-- end #left-area -->
	</div> <!-- .container -->
</div> <!-- #content -->

<?php get_footer(); ?>