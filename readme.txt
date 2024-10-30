=== Plugin Name ===
Contributors: fusionstream
Donate link: N/A
Tags: blogs,blogroll,excerpts,blogsiread,blogs i read,xml,rss,atom,feed,
Requires at least: 3.3.1
Tested up to: 3.5.1
Stable tag: 0.1.2

Displays user-definable content from other blogs (via wordpress Links [RSS]) as a widget in your blog in a highly customisable format.

== Description ==

blogsiread is a highly customisable widget inspired by Blogger's "Blogs I Read" function that takes excerpts from other blogs (via Links) and displays them in a small widget on your blog.

= Features =
*   Change widget title
*   Automatically selects blogs from "Links" based on "Categories"
*   Optional - Show Site Title (either from link name or meta tags)
*   Optional - Show Post Title
*   Optional - Show Post Excerpt (250 characters max, plain text)
*   Optional - Order listing by "as ordered in Links page", Post Published Date, Link Name, Site Title, Post Title. All can be additionally sorted in descending or ascending order.
*   Clicking on entries can open in a new window or in the same window
*   You can customise your widget by adding a css class.

= TODO =
*   More customisable layout
*   Limit total entries and more importantly, before loading their xml-s
*   Complete or co-operative ajax to reduce server load and more importantly, page loading times
*   Make the category selector look pretty
*   Allow customisable Post Excerpt character limit
*   Do that cool thing where the widget title in the admin page is "blogsiread: <widget title>"

For those who are using a custom CSS Class, structure of widget content is as follows (some lines may not be displayed at all depending on widget options - see #):
`&lt;p class="yourCustomClassName"&gt;
#    &lt;a rel="sitetitle"....&lt;/a&gt; OR if site url is blank: <span rel="sitetitle"......&lt;/span&gt;
#    &lt;a rel="posttitle"....&lt;/a&gt; OR if post url is blank: <span rel="posttitle"......&lt;/span&gt;
#    &lt;span rel="desc"&gt;...CONTENT...&lt;/span&gt;
     &lt;span rel="timeago"&gt;...TIME...&lt;/span&gt;
&lt;/p&gt;`

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload `blogsiread.php` to the `/wp-content/plugins/blogsiread/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. In Appearance->Widgets, drag and Drop the 'blogsiread' widget to your sidebar.
4. Select the options that best suit your needs and click save.
5. Enjoy.

== Frequently Asked Questions ==

= How do I use this? =

1) Add a link (Links->Add New)
2) Fill in the Name, Web Address and RSS Address
3) Also choose an appropriate category (e.g. Blogroll).
4) Save your link
5) Add the widget "blogsiread" to your sidebar (Appearance->Widgets)
6) Select the appropriate options (e.g. Site Title Source selects between Name (in point 2) or the website name given in the feed)
7) Also select the category that you chose in point 3.
8) Click save.
9) Enjoy.

= Not all of the blogs are showing up on my sidebar? =

If the server is unable to connect or retrieve the feeds in time, it skips it.

This is a safety feature. Tying up the server's resources trying to retrieve data from a slow page is bad for your webhost's health. Not loading the page in your browser quickly because the server is still trying to retrieve the data is bad for YOUR health.

Until the feeds can be solely retrieved by a browser instead of the server (upcoming feature!) or until I can code some ajax to augment the loading speeds, this will always be an issue.

= What if I want to donate? =

Donate? Very nice of you to ask that question. At the moment however, I am not in need of donations. If you'd like to feel like your gave something back, help out at your local charity or donate to them, or pay it forward however you wish.

== Screenshots ==

1. None yet.

== Changelog ==

= 0.1.2 =
Update FAQ

= 0.1.1 =
Compatible with 3.5.1

= 0.1.0 =
* Initial.

== Upgrade Notice ==

= nil =