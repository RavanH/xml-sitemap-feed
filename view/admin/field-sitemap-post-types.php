<fieldset id="xmlsf_post_types">
    <legend class="screen-reader-text">'<?php _e('XML Sitemaps for post types','xml-sitemap-feed'); ?></legend>

    <?php 
    foreach ( $post_types as $post_type ) :
    // skip unallowed post types
    if (in_array($post_type->name,$this->disabled_post_types()))
        continue;

    $count = wp_count_posts( $post_type->name );
    ?>

    <input type="hidden" name="xmlsf_post_types[<?php echo $post_type->name; ?>][name]" value="<?php echo $post_type->name; ?>" />
    <label>
        <input type="checkbox" name="xmlsf_post_types[<?php echo $post_type->name; ?>][active]" id="xmlsf_post_types_<?php echo $post_type->name; ?>" value="1"<?php echo checked( !empty($options[$post_type->name]["active"]), true, false); ?> />
        <?php echo $post_type->label; ?> (<?php echo $count->publish; ?>)
    </label>

    <?php 
    if ( empty($options[$post_type->name]['active']) ) { 
        echo '<br/>';
        continue;
    }
    ?>

    &nbsp;&ndash;&nbsp; 

    <span class="description">
        <a id="xmlsf_post_types_<?php echo $post_type->name; ?>_link" href="#xmlsf_post_types_<?php echo $post_type->name; ?>_settings">
            <?php echo translate('Settings'); ?>
        </a>
    </span>

    <br/>
    
    <script type="text/javascript">
    jQuery( document ).ready( function() {
        jQuery("#xmlsf_post_types_<?php echo $post_type->name; ?>_settings").hide();
        jQuery("#xmlsf_post_types_<?php echo $post_type->name; ?>_link").click( function(event) {
                event.preventDefault();
            jQuery("#xmlsf_post_types_<?php echo $post_type->name; ?>_settings").toggle("fast");
        });
    });
    </script>

    <ul class="xmlsf_settings" id="xmlsf_post_types_<?php echo $post_type->name; ?>_settings">

        <?php 
        if ( isset($defaults[$post_type->name]['archive']) ) {
        $archive = !empty($options[$post_type->name]['archive']) ? $options[$post_type->name]['archive'] : $defaults[$post_type->name]['archive'];
        ?>
 
        <li>
            <label><?php _e('Split by','xml-sitemap-feed'); ?> 
                <select name="xmlsf_post_types[<?php echo $post_type->name; ?>][archive]" id="xmlsf_post_types_'<?php echo $post_type->name; ?>_archive">
                    <option value="">
                        <?php echo translate('None'); ?>
                    </option>
                    <option value="yearly"<?php echo selected( $archive == 'yearly', true, false); ?>>
                        <?php echo __('Year','xml-sitemap-feed'); ?>
                    </option>
                    <option value="monthly"<?php echo selected( $archive == 'monthly', true, false); ?>>
                        <?php echo __('Month','xml-sitemap-feed'); ?>
                    </option>

                </select>
            </label> 
            <span class="description"> 
                <?php _e('Choose split by month if you experience errors or slow sitemaps.','xml-sitemap-feed'); ?>
            </span>
        </li>

        <?php 
        } 

        $priority_val = !empty($options[$post_type->name]['priority']) ? $options[$post_type->name]['priority'] : $defaults[$post_type->name]['priority'];
        $image = isset($options[$post_type->name]['tags']['image']) ? $options[$post_type->name]['tags']['image'] : $defaults[$post_type->name]['tags']['image'];
        $context = ( $post_type->name === 'page' ) ? 'page' : 'post';
        ?>

        <li>
            <label><?php echo __('Priority','xml-sitemap-feed'); ?> 
                <input type="number" step="0.1" min="0.1" max="0.9" name="xmlsf_post_types[<?php echo $post_type->name; ?>][priority]" id="xmlsf_post_types_<?php echo $post_type->name; ?>_priority" value="<?php echo $priority_val; ?>" class="small-text" />
            </label> 
            <span class="description"><?php echo __('Priority can be overridden on individual posts.','xml-sitemap-feed'); ?></span>
        </li>

        <li>
            <label>
                <input type="checkbox" name="xmlsf_post_types[<?php echo $post_type->name; ?>][dynamic_priority]" value="1"<?php echo checked( !empty($options[$post_type->name]['dynamic_priority']), true, false); ?> /> 
                <?php echo __('Automatic Priority calculation.','xml-sitemap-feed'); ?>
            </label> 
            <span class="description"><?php echo __('Adjusts the Priority based on factors like age, comments, sticky post or blog page.','xml-sitemap-feed'); ?></span>
        </li>

        <li>
            <label>
                <input type="checkbox" name="xmlsf_post_types[<?php echo $post_type->name; ?>][update_lastmod_on_comments]" value="1"<?php echo checked( !empty($options[$post_type->name]["update_lastmod_on_comments"]), true, false); ?> /> 
                <?php echo __('Update the Last Changed date on each new comment.','xml-sitemap-feed'); ?>
            </label>
        </li>

        <li>
            <label>
                <?php echo __('Add image tags for','xml-sitemap-feed'); ?> 
                <select name="xmlsf_post_types[<?php echo $post_type->name; ?>][tags][image]">
                    <option value="">
                        <?php echo translate('None'); ?>
                    </option>
                    <option value="featured"<?php echo selected( $image == "featured", true, false); ?>>
                        <?php echo translate_with_gettext_context('Featured Image',$context); ?>
                    </option>
                    <option value="attached"<?php echo selected( $image == "attached", true, false); ?>>
                        <?php echo __('Attached images','xml-sitemap-feed'); ?>
                    </option>
                </select>
            </label>
        </li>

    </ul>
    <?php 
    endforeach;
    ?>
</fieldset>