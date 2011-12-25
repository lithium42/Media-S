=== Display Last Post(s) ===
Contributors: Francois Barret
Tags: last,post,posts,page,shortcode,include, article,article,dernier,derniers,
Requires at least: 1.5
Tested up to: 2.9
Stable tag: trunk
Donate Link: http://www.fb-graphic.com/dons/

Allows to display the last post(s) anywhere on your WordPress site/blog, using a shortcode or a short PHP code (for the templates).


== Description ==

**Display Last Posts** is a plugin that allows you to display the last post(s) (by default, the last one only) anywhere on your blog.

Official page and french translation :  http://www.fb-graphic.com/bonus/display-last-posts-wordpress-plugin

Two ways are available: using a shortcode in posts or pages, within the CMS, or putting a small PHP code in a template of your theme.

**shortcode** : [last-posts] or [last-posts nb="5" cont="excerpt"]

**code PHP** : < ?php lastPosts('',''); ?> or < ?php lastPosts(5,'excerpt'); ?> (suppress space between < and ?)

The shortcode is: **[last-posts]**
By default, only the last post is displayed, with its whole content.
Optional arguments, ex.:  **[last-posts nb="5" cont="excerpt"]** - with [nb] you specify the quantity of posts you want to display (always newest first), and with [cont="excerpt"] you specify that only the excerpt of the posts will be displayed.

The PHP code for templates is: **< ?php lastPosts('',''); ?>**
By default, only the last post is displayed, with its whole content.
Optional arguments, ex.: **< ?php lastPosts(5,'extrait'); ?>** - the first argument specify the number of posts to display (always newest first), and the second one specify that only the excerpt must be displayed.

**Display Last Post(s)** is widely inspired by the sc_liste() function given here:
http://www.webinventif.fr/wordpress-creation-de-shortcode-avance/


== Installation ==

Realised under WordPress 2.9.2 - at least valid for this version.

Download and unzip "display-last-post.zip" - place the "display-last-post" folder in the wp-content/plugins folder. Then activate Display Last Post(s) form the Plugins panel.


== CSS styling ==

The code produced is included in a <div> with class="display-last-post" (<div class="display-last-posts">). You can then easily apply a remote CSS.


== Bug Reports / Suggestions ==

You can contact me here: http://www.fb-graphic.com/fb-contact.php
Thanks to specify [ Display Last Post ] in the title.

== Frequently Asked Questions ==
/

== Changelog ==
/

== Upgrade Notice ==
/

== Screenshots ==

Can be seen in action on my homepage http://www.fb-graphic.com


== Donate Link ==

If you wish to donate, please go there: http://www.fb-graphic.com/dons/
Thanks!


== License ==

copyright (c) 2010 Francois Barret < www.fb-graphic.com >

The free distribution, use and modification of this source are permitted provided they meet the following clauses:

- This entire notice and the entire license must be preserved.
- The name and internet references (web adresses) of the author must stay visible whatever the mode of distribution and modification of the source.

THIS SOFTWARE IS PROVIDED BY THE AUTHOR "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
