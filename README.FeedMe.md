
# FeedMe 2.0 - jQuery RSS 2.0 + ATOM/iTunes parser

feedMe is a basic RSS feed and Podcast parser with XML namespace support for your Javascript and jQuery web apps.

I have removed both the JSON and Base64 outputs and removed the dependency on the files Base64.js and JSON.js.

I have done some repairs to the code and tested it with jQuery 3.1.0, I have also removed the properties that are left empty or undefined so that the object is smaller and has less wasted space.

Check it out in the prettyprint output to see what is read from your feeds/podcasts

You can view the feed with Bootstrap using the text.html.

test-1.0.html is my first version.

I am thinking of adding an html5 playback support later on.

## Reference

* [RSS 2.0](http://www.rssboard.org/rss-2-0)

* Reference for iTunes Podcasting: [spec](http://www.apple.com/itunes/podcasts/specs.html) [howto](http://www.podcast411.com/howto_1.html)
 
* Reference for [ATOM](http://tools.ietf.org/html/rfc4287)


This class returns a normal Javascript object

### Display Example with my jQuery Plugin, feedRead:

        jQuery(function ($) {
            var feeds = [];

            $.feedRead({
                itunes: true, //podcast url
                debug: true,
                feed_list:  '#podcast',
                feed: '#playlist',
                channel:  '#chan_info',
                item: '#play_info'
            });


### test-1.0.html

This page has an example of printing the returned javascript object with [prettyprint](http://james.padolsey.com/javascript/prettyprint-for-javascript/)
