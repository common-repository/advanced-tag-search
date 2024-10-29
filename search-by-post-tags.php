<?php
/*
Plugin Name: Advanced tag search
Plugin URI: http://online-source.net/2011/07/31/advanced-tag-search/
Description: Advanced search plugin for post tags
Author: Laurens ten Ham (MrXHellboy)
Version: 1.2
Author URI: http://online-source.net
*/


/**
 * os_TagsArchive()
 * Returns the tags archive page / which can be called by the shortcode "tagsarchive"
 * @return string $str
 */
function os_TagsArchive(){
if ($_POST['search'] == 'ok' && $_SERVER['REQUEST_METHOD'] == 'POST'){
    # Declare global variable wpdb and and if no error, perform query
    global $wpdb;
    
    # Set empty variables
    $str = '';
    $error = '';
    
    # If the tagname is empty
    if (trim($_POST['tagname_like']) == '' && trim($_POST['tagname_is']) == '')
        $error = '<span class="error">Empty searchstring</span>';
    elseif ($_POST['tagname_like'] != '' && $_POST['tagname_is'] != '')
        $error = '<span class="error">Only one field should be used for the search</span>';
    
    # How to look in the column
    $search = (empty($_POST['tagname_like'])) ? ' = \''.$wpdb->escape($_POST['tagname_is']).'\'' : ' LIKE \'%'.$wpdb->escape($_POST['tagname_like']).'%\'';

        if (trim($error) == '')
            $tags = $wpdb->get_results(
                                        "SELECT terms.name, terms.slug, taxonomy.count
                                            FROM $wpdb->terms AS terms
                                                INNER JOIN $wpdb->term_taxonomy AS taxonomy
                                                ON terms.term_id = taxonomy.term_id
                                                    WHERE taxonomy.taxonomy = 'post_tag'
                                                    AND terms.name {$search}
                                                    AND taxonomy.count <> 0
                                                        ORDER BY ".$_POST['order_by'].' '.$_POST['tag_sort'], 
                                                        OBJECT);

}

# Default form
$str .= '
<form method="POST" action="" id="form-search-tags">
    <fieldset>
        <legend><h3>Search tag name</h3></legend>
            <table>
                <tr>
                    <td>
                        Tag name like
                    </td>
                    <td>
                        <input type="text" name="tagname_like" />'.$error.'
                    </td>
                </tr>

                <tr>
                    <td>
                        
                    </td>
                    <td>
                        OR
                    </td>
                </tr>

                <tr>
                    <td>
                        Tag name is
                    </td>
                    <td>
                        <input type="text" name="tagname_is" />'.$error.'
                    </td>
                </tr>
            </table>
    </fieldset>

    <fieldset>    
        <legend><h3>Result</h3></legend>
            <table>
                <tr>
                    <td>
                        Show how many times used
                    </td>
                    <td colspan="2">
                        <input type="checkbox" name="count_times" checked="checked" />
                    </td>
                </tr>

                <tr>
                    <td>
                        Order by
                    </td>
                    <td colspan="2">
                        <select name="order_by">
                            <option value="taxonomy.count">Times used</value>
                            <option value="terms.name">Tag name</value>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td>
                        Sort
                    </td>
                    <td>
                        <input type="radio" name="tag_sort" value="ASC" /> ASC
                    </td>
                    <td>
                        <input type="radio" name="tag_sort" value="DESC" checked="checked" /> DESC
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <input type="reset" value="reset fields" />
                    </td>
                    <td>
                        <input type="submit" value="Search tags" />
                    </td>
                </tr>
            </table>
    </fieldset>
<input type="hidden" name="search" value="ok" />
</form>
';

# If the submit button is hit
if (@isset($tags)){
    # Show results (count)
    $str .= '<span class="headings">'. count($tags) .' tags found</span>';
    
    # Ordered list (numbered)
    $str .= '<ol>';
        foreach ($tags as $tag){
            # Get the current itterated term object
            $term_obj = @get_term_by('slug', $tag->slug, 'post_tag', OBJECT);
                # If the count checkbox has been set - show usage count
                $show_count = (@isset($_POST['count_times'])) ? '('.$tag->count.')' : '';
                # The list item
                $str .= '<li class="top-tags"><a href="'.get_tag_link($term_obj->term_id).'" class="top-tags-list">'.strtolower($tag->name).' '.$show_count.' </a></li>';
        }
    $str .= '</ol>';
}
# Return the page
return $str;
}
# Add shortcode | used in page
add_shortcode('tagsarchive', 'os_TagsArchive');
?>