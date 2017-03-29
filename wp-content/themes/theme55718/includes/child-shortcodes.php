<?php
//Shortcodes

add_shortcode( 'advanced_categories', 'tm_advanced_categories_shortcode' );

function tm_advanced_categories_shortcode( $atts ) {

	extract( shortcode_atts( array(
		'from_cat' => '',
		'select_only_with_images' => true,
		'show_image' => true,
		'show_name' => false,
		'show_description' => false,
		'columns' => '4'
	), $atts ) );

	//global $tm_theme_texdomain;

	if ( '' != $from_cat) {
		$parent_cat = get_term_by( 'slug', $from_cat, 'product_cat' );
		$args = array(
		    'hide_empty'    => false, 
		    'parent'         => $parent_cat->term_id
		); 
	} else {
		$args = array(
		    'hide_empty'    => false
		);
	}
	$prod_cats = get_terms( 'product_cat', $args );
	if ( $prod_cats ) {
		$container_class = '';
		switch ($columns) {
			case '1':
				$container_class = 'cols_1';
				$col_num = 1;
				break;
			case '2':
				$container_class = 'cols_2';
				$col_num = 2;
				break;
			case '3':
				$container_class = 'cols_3';
				$col_num = 3;
				break;
			case '4':
				$container_class = 'cols_4';
				$col_num = 4;
				break;
			case '5':
				$container_class = 'cols_5';
				$col_num = 5;
				break;
			case '6':
				$container_class = 'cols_6';
				$col_num = 6;
				break;
			default:
				$container_class = 'cols_6';
				$col_num = 6;
				break;
		}
		
		$output = "<ul class='advanced_categories " . $container_class . "'>\n";
		$cat_iterator = 0;
		foreach ( $prod_cats as $cat ) {

			$cat_link = get_term_link( $cat, 'product_cat' );

			$visible_trigger = true;
			if ( true == $select_only_with_images ) {
				$thumbnail_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );
				$image = wp_get_attachment_url( $thumbnail_id );
				if (!$image) {
					$visible_trigger = false;
				}
			}
			if ( true == $visible_trigger ) {
				$cat_iterator++;
				$item_class = '';
				if ( 1 == $cat_iterator % $col_num ) {
					$item_class = ' first';
				} elseif ( 0 == $cat_iterator % $col_num ) {
					$item_class = ' last';
				}
				$output .= "<li class='advanced_categories_item" . $item_class . "'>\n";
					$output .= "<div class='advanced_categories_item_inner'>\n";
						if ( true == $show_image ) {
							$thumbnail_id = get_woocommerce_term_meta( $cat->term_id, 'thumbnail_id', true );
							$image = wp_get_attachment_image_src( $thumbnail_id, 'thumbnail' );
							$output .= "<figure>\n";
								$output .= "<a href='" . $cat_link . "'><img src='" . $image[0] . "' alt='" . $cat->name . "'></a>\n";
							$output .= "</figure>\n";
						}
						if ( true == $show_name ) {
							$output .= "<h4><a href='" . $cat_link . "'>" . $cat->name . "</a></h4>\n";
						}
						if ( true == $show_description && $cat->description != '' ) {
							$output .= "<div class='cat_desc'>" . $cat->description . "</div>\n";
						}
					$output .= "</div>\n";
				$output .= "</li>\n";
			}

		}
		$output .= "</ul>\n";
	} else {
		$output = __( 'There is no categories has been found', CURRENT_THEME );
	}

	return $output;
	
}

//Custom element
function shortcode_custom_element($atts, $content = null) {
	extract(shortcode_atts(array(
			'element' => 'div',
			'css_class' => 'my_class',
			'inner_wrapper' => false 
	), $atts));

	$output = '<'.$element.' class="'.esc_attr( $css_class ).'">';
	if (true == $inner_wrapper) {
		$output .= '<div class="'.esc_attr( $css_class ).'_wrap_inner">';
	}
		$output .= do_shortcode($content);
	if (true == $inner_wrapper) {
		$output .= '</div>';
	}
	$output .= '</'.$element.'>';

	return $output;
}
add_shortcode('custom_element', 'shortcode_custom_element');
/**
 * Banner
 *
 */
if ( !function_exists( 'custom_banner_shortcode' ) ) {

	function custom_banner_shortcode( $atts, $content = null, $shortcodename = '' ) {
		extract( shortcode_atts(
			array(
				'img'          => '',
				'banner_link'  => '',
				'title'        => '',
				'text'         => '',
				'btn_text'     => '',
				'target'       => '',
				'custom_class' => ''
		), $atts));

		$uploads          = wp_upload_dir();
		$uploads_dir_name = end( ( explode( '/', $uploads['baseurl'] ) ) );

		$img_path = explode( 'uploads', $img );
		if ( 1 == count( $img_path ) ) {
			$img_path = explode( $uploads_dir_name, $img );
		}
		$_img = end( $img_path );

		if ( 1 < count( $img_path ) ) {
			$img = $uploads['baseurl'] . $_img;
		}

		$output =  '<div class="banner-wrap '.$custom_class.'">';
		if ($img !="") {
			if ($banner_link != "") {
                $output .= '<a href="'. $banner_link .'" title="'. $title .'">';
                    $output .= '<img src="' . $img .'" title="'. $title .'" alt="" />';
                    //caption
                    $output .= '<div class="caption">';
                		if ($title!="") {
                			$output .= '<h5>';
                			$output .= $title;
                			$output .= '</h5>';
                		}
                		if ($text!="") {
                			$output .= '<p>';
                			$output .= $text;
                			$output .= '</p>';
                		}
                		if ($btn_text!="") {
                			$output .=  '<div class="link-align banner-btn"><span title="'.$btn_text.'" class="btn btn-link" target="'.$target.'">';
                			$output .= $btn_text;
                			$output .= '</span></div>';
                		}
                    $output .= '</div>';
                    //end caption
                    
                $output .= '</a>';
			} else {
				$output .= '<img src="' . $img .'" title="'. $title .'" alt="" />';
			}
			$output .= '</figure>';
		}
        //------
		$output .= '</div><!-- .banner-wrap (end) -->';

		$output = apply_filters( 'cherry_plugin_shortcode_output', $output, $atts, $shortcodename );

		return $output;
	}
	add_shortcode('custom_banner', 'custom_banner_shortcode');

}
/**
 * Post Grid
 *
 */
if (!function_exists('custom_posts_grid_shortcode')) {

	function custom_posts_grid_shortcode( $atts, $content = null, $shortcodename = '' ) {
		extract(shortcode_atts(array(
			'type'            => 'post',
			'category'        => '',
			'custom_category' => '',
			'tag'             => '',
			'columns'         => '3',
			'rows'            => '3',
			'order_by'        => 'date',
			'order'           => 'DESC',
			'thumb_width'     => '370',
			'thumb_height'    => '250',
			'lightbox'  	  => 'yes',
			'meta'            => '',
			'excerpt_count'   => '15',
			'link'            => 'yes',
			'link_text'       => __('Read more', CHERRY_PLUGIN_DOMAIN),
			'custom_class'    => ''
		), $atts));

		$spans = $columns;
		$rand  = rand();

		// columns
		switch ($spans) {
			case '1':
				$spans = 'span12';
				break;
			case '2':
				$spans = 'span6';
				break;
			case '3':
				$spans = 'span4';
				break;
			case '4':
				$spans = 'span3';
				break;
			case '6':
				$spans = 'span2';
				break;
		}

		// check what order by method user selected
		switch ($order_by) {
			case 'date':
				$order_by = 'post_date';
				break;
			case 'title':
				$order_by = 'title';
				break;
			case 'popular':
				$order_by = 'comment_count';
				break;
			case 'random':
				$order_by = 'rand';
				break;
		}

		// check what order method user selected (DESC or ASC)
		switch ($order) {
			case 'DESC':
				$order = 'DESC';
				break;
			case 'ASC':
				$order = 'ASC';
				break;
		}

		// show link after posts?
		switch ($link) {
			case 'yes':
				$link = true;
				break;
			case 'no':
				$link = false;
				break;
		}

			global $post;
			global $my_string_limit_words;

			$numb = $columns * $rows;

			// WPML filter
			$suppress_filters = get_option('suppress_filters');

			$args = array(
				'post_type'         => $type,
				'category_name'     => $category,
				$type . '_category' => $custom_category,
				'tag'               => $tag,
				'numberposts'       => $numb,
				'orderby'           => $order_by,
				'order'             => $order,
				'suppress_filters'  => $suppress_filters
			);

			$posts = get_posts( $args );

			if ( empty( $posts ) ) {
				wp_reset_postdata();
				return;
			}

			$i          = 0;
			$count      = 1;
			$output_end = '';
			$countul    = 0;

			if ($numb > count($posts)) {
				$output_end = '</ul>';
			}

			$output = '<ul class="posts-grid row-fluid unstyled '. $custom_class .' ul-item-'.$countul.'">';


			foreach ( $posts as $j => $post ) {
				$post_id = $posts[$j]->ID;
				//Check if WPML is activated
				if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
					global $sitepress;

					$post_lang = $sitepress->get_language_for_element( $post_id, 'post_' . $type );
					$curr_lang = $sitepress->get_current_language();
					// Unset not translated posts
					if ( $post_lang != $curr_lang ) {
						unset( $posts[$j] );
					}
					// Post ID is different in a second language Solution
					if ( function_exists( 'icl_object_id' ) ) {
						$posts[$j] = get_post( icl_object_id( $posts[$j]->ID, $type, true ) );
					}
				}

				setup_postdata($posts[$j]);
				$excerpt        = get_the_excerpt();
				$attachment_url = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'full' );
				$url            = $attachment_url['0'];
				$image          = aq_resize($url, $thumb_width, $thumb_height, true);
				$mediaType      = get_post_meta($post_id, 'tz_portfolio_type', true);
				$prettyType     = 0;

				if ($count > $columns) {
					$count = 1;
					$countul ++;
					$output .= '<ul class="posts-grid row-fluid unstyled '. $custom_class .' ul-item-'.$countul.'">';
				}

				$output .= '<li class="'. $spans .' list-item-'.$count.'">';

					if($lightbox == 'yes') {
						if(has_post_thumbnail($post_id) && $mediaType == 'Image') {

							$prettyType = 'prettyPhoto-'.$rand;

							$output .= '<figure class="featured-thumbnail thumbnail">';
							$output .= '<a href="'.$url.'" title="'.get_the_title($post_id).'" rel="' .$prettyType.'">';
							$output .= '<img  src="'.$image.'" alt="'.get_the_title($post_id).'" />';
							$output .= '<span class="zoom-icon"></span></a></figure>';
						} elseif ($mediaType != 'Video' && $mediaType != 'Audio') {

							$thumbid = 0;
							$thumbid = get_post_thumbnail_id($post_id);

							$images = get_children( array(
								'orderby'        => 'menu_order',
								'order'          => 'ASC',
								'post_type'      => 'attachment',
								'post_parent'    => $post_id,
								'post_mime_type' => 'image',
								'post_status'    => null,
								'numberposts'    => -1
							) );

							if ( $images ) {

								$k = 0;
								//looping through the images
								foreach ( $images as $attachment_id => $attachment ) {
									$prettyType = "prettyPhoto-".$rand ."[gallery".$i."]";
									//if( $attachment->ID == $thumbid ) continue;

									$image_attributes = wp_get_attachment_image_src( $attachment_id, 'full' ); // returns an array
									$img = aq_resize( $image_attributes[0], $thumb_width, $thumb_height, true ); //resize & crop img
									$alt = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
									$image_title = $attachment->post_title;

									if ( $k == 0 ) {
										if (has_post_thumbnail($post_id)) {
											$output .= '<figure class="featured-thumbnail thumbnail">';
											$output .= '<a href="'.$image_attributes[0].'" title="'.get_the_title($post_id).'" rel="' .$prettyType.'">';
											$output .= '<img src="'.$image.'" alt="'.get_the_title($post_id).'" />';
										} else {
											$output .= '<figure class="featured-thumbnail thumbnail">';
											$output .= '<a href="'.$image_attributes[0].'" title="'.get_the_title($post_id).'" rel="' .$prettyType.'">';
											$output .= '<img  src="'.$img.'" alt="'.get_the_title($post_id).'" />';
										}
									} else {
										$output .= '<figure class="featured-thumbnail thumbnail" style="display:none;">';
										$output .= '<a href="'.$image_attributes[0].'" title="'.get_the_title($post_id).'" rel="' .$prettyType.'">';
									}
									$output .= '<span class="zoom-icon"></span></a></figure>';
									$k++;
								}
							} elseif (has_post_thumbnail($post_id)) {
								$prettyType = 'prettyPhoto-'.$rand;
								$output .= '<figure class="featured-thumbnail thumbnail">';
								$output .= '<a href="'.$url.'" title="'.get_the_title($post_id).'" rel="' .$prettyType.'">';
								$output .= '<img  src="'.$image.'" alt="'.get_the_title($post_id).'" />';
								$output .= '<span class="zoom-icon"></span></a></figure>';
							}
						} else {

							// for Video and Audio post format - no lightbox
							$output .= '<figure class="featured-thumbnail thumbnail"><a href="'.get_permalink($post_id).'" title="'.get_the_title($post_id).'">';
							$output .= '<img  src="'.$image.'" alt="'.get_the_title($post_id).'" />';
							$output .= '</a></figure>';
						}
					} else {
						if(has_post_thumbnail($post_id)) {
							$output .= '<figure class="featured-thumbnail thumbnail">';
							$output .= '<a href="'.get_permalink($post_id).'" title="'.get_the_title($post_id).'">';
							$output .= '<img  src="'.$image.'" alt="'.get_the_title($post_id).'" />';
							$output .= '</a></figure>';
						} 
					}

				
                    $output .= '<div class="caption">';
					$output .= '<h5><a href="'.get_permalink($post_id).'" title="'.get_the_title($post_id).'">';
						$output .= get_the_title($post_id);
					$output .= '</a></h5>';

					if ($meta == 'yes') {
						// begin post meta
						$output .= '<div class="post_meta">';
                            // post author
							$output .= '<span class="post_author">';
							$output .= '<em> by </em>';
							$output .= '<a href="'.get_author_posts_url(get_the_author_meta( 'ID' )).'">'.get_the_author_meta('display_name').'</a>';
							$output .= '</span>';
                            
                            
							// post date
							$output .= '<span class="post_date">';
							$output .= '<time datetime="'.get_the_time('Y-m-d\TH:i:s', $post_id).'">' .get_the_date('d M'). '</time>';
							$output .= '</span>';
                            
                            

							
						$output .= '</div>';
						// end post meta
					}
					$output .= cherry_get_post_networks(array('post_id' => $post_id, 'display_title' => false, 'output_type' => 'return'));
					if($excerpt_count >= 1){
						$output .= '<p class="excerpt">';
							$output .= wp_trim_words($excerpt,$excerpt_count);
						$output .= '</p>';
					}
					
                    // post comment count
						$num = 0;
						$queried_post = get_post($post_id);
						$cc = $queried_post->comment_count;
						if( $cc == $num || $cc > 1 ) : $cc = $cc.' Comments';
						else : $cc = $cc.' Comment';
						endif;
						$permalink = get_permalink($post_id);
						$output .= '<span class="post_comment">';
						$output .= '<a href="'. $permalink . '" class="comments_link">' . $cc . '</a>';
						$output .= '</span>';
                        
                        if($link){
						$output .= '<a href="'.get_permalink($post_id).'" class="btn btn-primary" title="'.get_the_title($post_id).'">';
						$output .= $link_text;
						$output .= '</a>';
					}
                    $output .= '</div>';
					$output .= '</li>';
					if ($j == count($posts)-1) {
						$output .= $output_end;
					}
				if ($count % $columns == 0) {
					$output .= '</ul><!-- .posts-grid (end) -->';
				}
			$count++;
			$i++;

		} // end for
		wp_reset_postdata(); // restore the global $post variable

		$output = apply_filters( 'cherry_plugin_shortcode_output', $output, $atts, $shortcodename );

		return $output;
	}
	add_shortcode('custom_posts_grid', 'custom_posts_grid_shortcode');
}
/**
 * Recent Testimonials
 *
 */
if ( ! function_exists( 'shortcode_recenttesti' ) ) {

 function shortcode_recenttesti( $atts, $content = null, $shortcodename = '' ) {
  extract( shortcode_atts( array(
   'num'           => '5',
   'thumb'         => 'true',
   'excerpt_count' => '30',
   'custom_class'  => '',
  ), $atts ) );

  // WPML filter
  $suppress_filters = get_option( 'suppress_filters' );

  $args  = array(
   'post_type'        => 'testi',
   'numberposts'      => $num,
   'orderby'          => 'post_date',
   'suppress_filters' => $suppress_filters
  );
  $testi = get_posts( $args );

  $itemcounter = 0;

  $output = '<div class="testimonials ' . $custom_class . '">';

  global $post;
  global $my_string_limit_words;

  foreach ( $testi as $k => $post ) {
   //Check if WPML is activated
   if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
    global $sitepress;

    $post_lang = $sitepress->get_language_for_element( $post->ID, 'post_testi' );
    $curr_lang = $sitepress->get_current_language();
    // Unset not translated posts
    if ( $post_lang != $curr_lang ) {
     unset( $testi[ $k ] );
    }
    // Post ID is different in a second language Solution
    if ( function_exists( 'icl_object_id' ) ) {
     $post = get_post( icl_object_id( $post->ID, 'testi', true ) );
    }
   }
   setup_postdata( $post );
   $post_id = $post->ID;
   $excerpt = get_the_excerpt();

   // Get custom metabox value.
   $testiname  = get_post_meta( $post_id, 'my_testi_caption', true );
   $testiurl   = esc_url( get_post_meta( $post_id, 'my_testi_url', true ) );
   $testiinfo  = get_post_meta( $post_id, 'my_testi_info', true );
   $testiemail = sanitize_email( get_post_meta( $post_id, 'my_testi_email', true ) );

   $attachment_url = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'full' );
   $url            = $attachment_url['0'];
   $image          = aq_resize( $url, 378, 378, true );

   $output .= '<div class="testi-item list-item-' . $itemcounter . '">';
   $output .= '<blockquote class="testi-item_blockquote">';
   if ( $thumb == 'true' ) {
    if ( has_post_thumbnail( $post_id ) ) {
     $output .= '<figure class="featured-thumbnail">';
     $output .= '<img src="' . $image . '" alt="" />';
     $output .= '</figure>';
    }
   }
   $output .= '<div class="caption">';
   
   
   $output .= '<a href="' . get_permalink( $post_id ) . '">';
   $output .= wp_trim_words( $excerpt, $excerpt_count );
   $output .= '</a>';
   
   $output .= '<small class="testi-meta">';
   if ( ! empty( $testiname ) ) {
    $output .= '<h3>';
    $output .= $testiname;
    $output .= '</h3>';
   }

   if ( ! empty( $testiinfo ) ) {
    $output .= ' <span class="info">';
    $output .= $testiinfo;
    $output .= '</span><br>';
   }

   if ( ! empty( $testiurl ) ) {
    $output .= '<a class="testi-url" href="' . $testiurl . '">';
    $output .= $testiurl;
    $output .= '</a><br>';
   }
   
   

   if ( ! empty( $testiemail ) && is_email( $testiemail ) ) {
    $output .= '<a class="testi-email" href="mailto:' . antispambot( $testiemail, 1 ) . '" >' . antispambot( $testiemail ) . ' </a>';
   }

   $output .= '</small>';
   $output .= '</div>';

   $output .= '</blockquote>';
   
    
   $output .= '</div>';
   $itemcounter ++;

  }
  wp_reset_postdata(); // restore the global $post variable
  $output .= '</div>';

  $output = apply_filters( 'cherry_plugin_shortcode_output', $output, $atts, $shortcodename );

  return $output;
 }

 add_shortcode( 'recenttesti', 'shortcode_recenttesti' );

}
//------------------------------------------------------
//  Related Posts
//------------------------------------------------------
    if(!function_exists('cherry_related_posts')){
        function cherry_related_posts($args = array()){
            global $post;
            $default = array(
                'post_type' => get_post_type($post),
                'class' => 'related-posts',
                'class_list' => 'related-posts_list',
                'class_list_item' => 'related-posts_item',
                'display_title' => true,
                'display_link' => true,
                'display_thumbnail' => true,
                'width_thumbnail' => 195,
                'height_thumbnail' => 195,
                'before_title' => '<h3 class="related-posts_h">',
                'after_title' => '</h3>',
                'posts_count' => 4
            );
            extract(array_merge($default, $args));

            $post_tags = wp_get_post_terms($post->ID, $post_type.'_tag', array("fields" => "slugs"));
            $tags_type = $post_type=='post' ? 'tag' : $post_type.'_tag' ;
            $suppress_filters = get_option('suppress_filters');// WPML filter
            $blog_related = apply_filters( 'cherry_text_translate', of_get_option('blog_related'), 'blog_related' );
            if ($post_tags && !is_wp_error($post_tags)) {
                $args = array(
                    "$tags_type" => implode(',', $post_tags),
                    'post_status' => 'publish',
                    'posts_per_page' => $posts_count,
                    'ignore_sticky_posts' => 1,
                    'post__not_in' => array($post->ID),
                    'post_type' => $post_type,
                    'suppress_filters' => $suppress_filters
                    );
                query_posts($args);
                if ( have_posts() ) {
                    $output = '<div class="'.$class.'">';
                    $output .= $display_title ? $before_title.$blog_related.$after_title : '' ;
                    $output .= '<ul class="'.$class_list.' clearfix">';
                    while( have_posts() ) {
                        the_post();
                        $thumb   = has_post_thumbnail() ? get_post_thumbnail_id() : PARENT_URL.'/images/empty_thumb.gif';
                        $blank_img = stripos($thumb, 'empty_thumb.gif');
                        $img_url = $blank_img ? $thumb : wp_get_attachment_url( $thumb,'full');
                        $image   = $blank_img ? $thumb : aq_resize($img_url, $width_thumbnail, $height_thumbnail, true) or $img_url;
                        $excerpt        = get_the_excerpt();

                        $output .= '<li class="'.$class_list_item.'">';
                        $output .= $display_thumbnail ? '<figure class="thumbnail featured-thumbnail"><a href="'.get_permalink().'" title="'.get_the_title().'"><img data-src="'.$image.'" alt="'.get_the_title().'" /></a></figure>': '' ;
                        $output .= '<div class="related-posts_content">';
                        $output .= $display_link ? '<h4><a href="'.get_permalink().'" >'.my_string_limit_words(get_the_title(),7).'</a></h4>': '' ;
                        
                        $output .= '</div>';
                        $output .= '</li>';
                    }
                    $output .= '</ul></div>';
                    echo $output;
                }
                wp_reset_query();
            }
        }
    }
?>