<?php
/**
 * Plugin Name: blogsiread
 * Plugin URI: http://www.fusionsocket.org/plugins/wp/blogsiread/
 * Description: Displays user-definable content from other blogs (via wordpress Links [RSS]) as a widget in your blog in a highly customisable format.
 * Version: 0.1.2
 * Author: fusionstream
 * Author URI: http://www.fusionstream.org
 *
 * TODO:
 * - More customisable layout
 * - Limit total entries and more importantly, before loading their xml-s
 * - Complete or co-operative ajax to reduce server load and more importantly, page loading times
 * - Make the category selector look pretty
 * - Allow customisable Post Excerpt character limit
 * - Do that cool thing where the widget title in the admin page is "blogsiread: <widget title>"
 */

// Add function to widgets_init that'll load our widget.
add_action( 'widgets_init', 'blogsiread_load_widgets' );

// Register our widget.
function blogsiread_load_widgets() {
	register_widget( 'blogsiread' );
}

class blogsiread extends WP_Widget {
    public function blogsiread() {
        $widget_ops = array('classname' => 'widget_blogsiread', 'description' => 'A blogroll widget for your sidebar that displays excerpts from your favorite blogs');
        
        $control_ops = array('id-base' => 'blogsiread-widget');
        #$control_ops = array('id_base' => 'blogsiread-widget');
        
        $this->WP_Widget('blogsiread-widget', 'blogsiread', $widget_ops, $control_ops);
        #$this->WP_Widget('blogsiread-widget', __('blofgsiread', 'widget_blogsiread'), $widget_ops, $control_ops);
    }
    
    function widget($args, $inst) { //for when in use in page
        extract($args);
        echo $before_widget;
        echo $before_title . apply_filters('widget_title', $inst['widgettitle']) . $after_title;
        
        //setup
        $vararr = array();
        $vararr['titlesource'] = $inst['titlesource'];          #0 is from link name, 1 is from site title
        $vararr['showtitle'] = $inst['showtitle'];              #bool. blog title, either from link name or site title (meta tags)
        $vararr['showposttitle'] = $inst['showposttitle'];      #bool
        $vararr['showpostexcerpt'] = $inst['showpostexcerpt'];  #bool
        $vararr['orderby'] = $inst['orderby'];                  #0 = order in links, 1 = by date, 2 = by link name, 3 = by site title, 4 = by post title | negative values = inverse order (i.e. numeric=ASC, alpha=DESC)
        $vararr['target'] = $inst['target'];                    #_blank, _self, etc..
        $vararr['cssclass'] = $inst['cssclass'];                #css class to use
        $vararr['feedarray'] = array();                         #
        
        //get links by categories (IDs)
        #$categoryids = array();
        #foreach ($inst['categories'] as $category) {
        #    $categoryids[] = get_cat_ID($category);
        #}
        #$categoryids = implode(",", $categoryids);
        #$vararr['links'] = get_bookmarks('category=' . $categoryids);
        $vararr['links'] = get_bookmarks('category=' . implode(",", $inst['categories']));
        
        
        //foreachlink
        foreach ($vararr['links'] as $link) {
            if ($link->link_rss != '') {
                if ($feedxml = simplexml_load_file($link->link_rss, null, LIBXML_NOCDATA)) { //if no failure
                    $linkentry = array();
                    if (count($feedxml->entry)) { //atom
                        $linkentry['sitetitle'] = ($vararr['titlesource'] === 0) ? $link->link_name : $feedxml->title;
                        $linkentry['posttitle'] = strip_tags($feedxml->entry[0]->title);
                        foreach ($feedxml->entry[0]->link as $la) {
                            if ($la['rel'] == "alternate") {
                                $linkentry['link'] = $la['href'];
                                break;
                            }
                        }
                        $linkentry['date'] = strtotime($feedxml->entry[0]->published);
                        $linkentry['desc'] = substr(strip_tags($feedxml->entry[0]->content), 0, 250);
                        $linkentry['desc'] = (strlen($linkentry['desc']) >= 250) ? $linkentry['desc'] . "..." : $linkentry['desc'];
                    } else if (count($feedxml->channel)) { //rss
                        $linkentry['sitetitle'] = ($vararr['titlesource'] === 0) ? $link->link_name : $feedxml->channel->title;
                        $linkentry['posttitle'] = strip_tags($feedxml->channel->item[0]->title);
                        $linkentry['link'] = $feedxml->channel->item[0]->link;
                        $linkentry['date'] = strtotime($feedxml->channel->item[0]->pubDate);
                        $linkentry['desc'] = substr(strip_tags($feedxml->channel->item[0]->description), 0, 250);
                        $linkentry['desc'] = (strlen($linkentry['desc']) >= 250) ? $linkentry['desc'] . "..." : $linkentry['desc'];
                    }
                    if (!empty($linkentry) && !empty($linkentry['link'])) {
                        $linkentry['siteurl'] = $link->link_url;
                        switch (abs($vararr['orderby'])) { //need to find a way to simplify this
                            case 0:
                                $vararr['feedarray'][] = $linkentry;
                                break;
                            case 1:
                                $vararr['feedarray'][$linkentry['date']] = $linkentry;
                                break;
                            case 2:
                                $vararr['feedarray'][$link->link_name] = $linkentry;
                                break;
                            case 3:
                                $vararr['feedarray'][$linkentry['sitetitle']] = $linkentry;
                                break;
                            case 4:
                                $vararr['feedarray'][$linkentry['posttitle']] = $linkentry;
                                break;
                        
                        }
                    }
                }
            }
        }
        
        switch($vararr['orderby']) {
            case -4: 
            case -3:
            case -2:
                krsort($vararr['feedarray'], SORT_STRING);
                break;
            case -1:
                ksort($vararr['feedarray'], SORT_NUMERIC);
                break;
            case 1:
                krsort($vararr['feedarray'], SORT_NUMERIC);
                break;
            case 2:
            case 3:
            case 4:
                ksort($vararr['feedarray'], SORT_STRING);
                break;
            case 0:
            default:
                break;
        }
        
        $this->printcontent($vararr);
        
        echo $after_widget;
    }
    
    function update($new_inst, $old_inst) {
        $inst = $old_inst;
        $inst['widgettitle'] = strip_tags($new_inst['widgettitle']);
        $inst['titlesource'] = intval($new_inst['titlesource']);
        $inst['showtitle'] = (isset($new_inst['showtitle'])) ? 1 : 0;
        $inst['showposttitle'] = (isset($new_inst['showposttitle'])) ? 1 : 0;
        $inst['showpostexcerpt'] = (isset($new_inst['showpostexcerpt'])) ? 1 : 0;
        $inst['orderby'] = intval($new_inst['orderby']);
        $inst['target'] = $new_inst['target'];
        $inst['cssclass'] = strip_tags($new_inst['cssclass']);
        $inst['categories'] = $new_inst['categories'];
        
        return $inst;
    }
    
    function form ($inst) {
        $defaults = array('widgettitle' => 'blogsiread', 'titlesource' => '1', 'showtitle' => 1, 'showposttitle' => 1, 'showpostexcerpt' => 1, 'orderby' => 1, 'target' => '_blank', 'cssclass' => 'blogsiread-css');
        $inst = wp_parse_args((array)$inst, $defaults);
        
        echo "
        <p>
            <label for='" . $this->get_field_id('widgettitle') . "'>Widget Title:</label><br />
            <input id='" . $this->get_field_id('widgettitle') . "' type='text' name='" . $this->get_field_name('widgettitle') . "' value='" . $inst['widgettitle'] . "' style='width: 99%;'>
        </p>
        <p>
            <label for='" . $this->get_field_id('titlesource') . "'>Site Title Source:</label><br />
            <select id='" . $this->get_field_id('titlesource') . "' name='" . $this->get_field_name('titlesource') . "' style='width: 99%;'>
                <option value='0' " . (($inst['titlesource'] == 0) ? "selected" : "") . ">Link Name</option>
                <option value='1' " . (($inst['titlesource'] == 1) ? "selected" : "") . ">Site Title (Meta Tags)</option>
            </select>
        </p>
        <p>
            <input type='checkbox' id='" . $this->get_field_id('showtitle') . "' name='" . $this->get_field_name('showtitle') . "' " . (($inst['showtitle'] == 1) ? "checked" : "") . "> <label for='" . $this->get_field_id('showtitle') . "'>Show Site Title</label>
        </p>
        <p>
            <input type='checkbox' id='" . $this->get_field_id('showposttitle') . "' name='" . $this->get_field_name('showposttitle') . "' " . (($inst['showposttitle'] == 1) ? "checked" : "") . "> <label for='" . $this->get_field_id('showposttitle') . "'>Show Post Title</label>
        </p>
        <p>
            <input type='checkbox' id='" . $this->get_field_id('showpostexcerpt') . "' name='" . $this->get_field_name('showpostexcerpt') . "' " . (($inst['showpostexcerpt'] == 1) ? "checked" : "") . "> <label for='" . $this->get_field_id('showpostexcerpt') . "'>Show Post Excerpt</label>
        </p>
        <p>
            <label for='" . $this->get_field_id('orderby') . "'>Sort Method:</label><br />
            <select id='" . $this->get_field_id('orderby') . "'  name='" . $this->get_field_name('orderby') . "' style='width: 99%;'>
                <option value='0' " . (($inst['orderby'] == 0) ? "selected" : "") . ">As ordered in Links</option>
                <option value='1' " . (($inst['orderby'] == 1) ? "selected" : "") . ">Published Date (desc)</option>
                <option value='-1' " . (($inst['orderby'] == -1) ? "selected" : "") . ">Publish Date (asc)</option>
                <option value='2' " . (($inst['orderby'] == 2) ? "selected" : "") . ">Link Name (asc)</option>
                <option value='-2' " . (($inst['orderby'] == -2) ? "selected" : "") . ">Link Name (desc)</option>
                <option value='3' " . (($inst['orderby'] == 3) ? "selected" : "") . ">Site Title (asc)</option>
                <option value='-3' " . (($inst['orderby'] == -3) ? "selected" : "") . ">Site Title (desc)</option>
                <option value='4' " . (($inst['orderby'] == 4) ? "selected" : "") . ">Post Title (asc)</option>
                <option value='-4' " . (($inst['orderby'] == -4) ? "selected" : "") . ">Post Title (desc)</option>
            </select>
        </p>
        <p>
            <label for='" . $this->get_field_id('target') . "'>Target:</label><br />
            <select id='" . $this->get_field_id('target') . "' name='" . $this->get_field_name('target') . "' style='width: 99%;'>
                <option value='_blank' " . (($inst['target'] == "_blank") ? "selected" : "") . ">New Window/Tab</option>
                <option value='_top' " . (($inst['target'] == "_top") ? "selected" : "") . ">Same Window/Tab</option>
                <option value='_self' " . (($inst['target'] == "_self") ? "selected" : "") . ">Same Frame (rarely used)</option>
            </select>
        </p>
        <p>
            <label for='" . $this->get_field_id('cssclass') . "'>CSS Class:</label><br />
            <input type='text' id='" . $this->get_field_id('cssclass') . "' name='" . $this->get_field_name('cssclass') . "' value='" . $inst['cssclass'] . "' style='width: 99%;'>
        </p>
        <p>
            <label for='" . $this->get_field_id('categories') . "'>Categories:</label><br />
            <select multiple='multiple' id='" . $this->get_field_id('categories') . "' name='" . $this->get_field_name('categories') . "[]' style='width: 99%;'>
                ";
                foreach (get_terms('link_category', '') as $category) {
                    echo "<option value='" . $category->term_id . "' " . ((in_array($category->term_id, $inst['categories'])) ? "selected" : "") . ">" . $category->name . "</option>";
                }
                echo "
            </select>
        </p>
        ";
    }
    
    function printcontent($vararr) {
        echo "<style>
        .blogsiread-css { ; }
        .blogsiread-css > *[rel=sitetitle] { font-weight: bold; text-decoration: none;}
        .blogsiread-css > *[rel=posttitle] { font-size: 10px; text-decoration: underline; }
        .blogsiread-css > *[rel=desc] { font-size: 10px; }
        .blogsiread-css > *[rel=timeago] { font-size: 10px; font-style: italic; }
        </style>";
        
        foreach ($vararr['feedarray'] as $entry) {
            $entry = (object)$entry;
            echo "<p class='" . $vararr['cssclass'] . "'>";
                if ($vararr['showtitle'] === 1) { echo (!empty($entry->siteurl)) ? "<a rel='sitetitle' href='" . $entry->siteurl . "' target='" . $vararr['target'] . "'>" . $entry->sitetitle . "</a>" : "<span rel='sitetitle'>" . $entry->sitetitle . "</span>"; echo "<br />"; }
                if ($vararr['showposttitle'] === 1) { echo (!empty($entry->link)) ? "<a rel='posttitle' href='" . $entry->link . "' target='" . $vararr['target'] . "'>" . $entry->posttitle . "</a>" : "<span rel='posttitle'>" . $entry->posttitle . "</span>"; echo "<br />";  }
                if ($vararr['showpostexcerpt'] === 1) { echo "<span rel='desc'>" . $entry->desc . "</span>"; echo "<br />";  }
                echo "<span rel='timeago'>" . $this->timeago($entry->date, time()) . "</span>";
            echo "</p>";
            
        }
    }
    
    function timeago($time, $now = null) {
        if ($now == null) { $now = time(); }
        if ($time > $now) { return "in the future"; }
        
        $diff = $now - $time;
        
        if ($diff > 604800) {
            return "a long time ago";
        } else if ($diff > 259200) { //more than 3 days
            return "a few days ago";
        } else if ($diff >= 172800) { //2 days or more
            return "more than 2 days ago";
        } else if ($diff >= 86400) { //a day or more
            return "more than 1 day ago";
        } else if ($diff > 7200) { //more than 2 hours
            return "more than " . round(($diff/3600), 0, PHP_ROUND_HALF_DOWN) . " hours ago";
        } else if ($diff > 3600) { //more than an hour
            return "more than 1 hour ago";
        } else if ($diff >= 120) { //more than 2 minutes
            return "more than " . round(($diff/60), 0, PHP_ROUND_HALF_DOWN) . " minutes ago";
        } else { 
            return "a few moments ago";
        }
    }
}
?>