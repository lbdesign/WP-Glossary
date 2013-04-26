<?php
class WPG_RandomTerm extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'wpg-random-term',
			'Random Term [WPGlossary]',
			array( 
				'classname'   => 'wpg_widget_random_term',
				'description' => 'Add a random glossary term to your sidebar',
			)
		); // parent::__construct
	} // __construct

	// Admin form
 	public function form( $instance=array() ) {
		$instance =  wp_parse_args( $instance, array(
			'title' => 'Random Glossary term',
			'group' => '',
		) );

		// Title
		echo '<p><label for="' . $this->get_field_id('title') . '">' . __('Title:') . ' </label>';
		echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . esc_attr( $instance['title'] ) . '" />';
		echo '</p>';

		// Group
		$groupsraw = get_terms( 'wpglossarygroup', array('hide_empty'=>false) );
		$groups    = array();
		foreach( $groupsraw as $group ): $groups[$group->slug] = $group->name; endforeach;

		$groupdd = tcb_wpg_build_dropdown( $this->get_field_id('group'), array(
			'selected'    => $instance['group'],
			'options'     => $groups,
			'allow_blank' => array('value'=>'','title'=>'Any'),
			'name'        => $this->get_field_name('group')
		) );
		echo '<p><label for="' . $this->get_field_id('group') . '"> ' . __('Group:'). ' </label>';
		echo $groupdd . '</p>';

		// Display
		$displaydd = tcb_wpg_build_dropdown( $this->get_field_id('display'), array(
			'selected'   => $instance['display'],
			'options'    => array( 'title'=>'Title Only', 'excerpt'=>'Excerpt', 'full'=>'Full' ),
			'name'       => $this->get_field_name('display'),
		) );
		echo '<p><label for="' . $this->get_field_id('display') . '"> ' . __('Display:'). ' </label>';
    echo $displaydd . '</p>';
	} // form

	public function update( $new_instance, $old_instance ) {
		$instance            = $old_instance;
		$instance['title']   = strip_tags( $new_instance['title'] );
		$instance['group']   = $new_instance['group'];
		$instance['display'] = $new_instance['display'];

		return $instance;
	} // update

	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if( !empty($title) ):
			echo $before_title . $title . $after_title;
		endif;

		$numberposts = isset($instance['numberposts']) ? $instance['numberposts'] : 1;

		$termargs = array(
			'post_type'   => 'glossary',
			'post_status' => 'publish',
			'numberposts' => $numberposts,
			'orderby'     => 'rand',
		);
		if( $group = $instance['group'] ):
			$termargs['tax_query'] = array( array(
				'taxonomy' => 'wpglossarygroup',
				'field'    => 'slug',
				'terms'    => $group
			) );
		endif;

		$terms = get_posts( $termargs );
		if( $terms && count($terms) ):
			echo '<ul class="wpglossary widget-list">';
			foreach( $terms as $term ): setup_postdata( $term );
				$title   = '<a href="' . get_permalink($term->ID) . '">' . get_the_title($term->ID) . '</a>';
				$desc    = '';
				$display = $instance['display'];
				if( $display && $display != 'title' ):
					$desc = $display == 'full' ? apply_filters('the_content',get_the_content()) : wpautop(get_the_excerpt());
					$desc = '<br>' . $desc;
				endif;
				echo '<li>' . $title . $desc . '</li>';
			endforeach; wp_reset_postdata();
			echo '</ul>';
		else :
			echo '<em>No terms available</em>';
		endif;

		echo $after_widget;
	} //widget

} // WPG_RandomTerm
