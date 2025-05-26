/*
 ################################################################################
 #
 #   FeedMe 2.0 - jQuery RSS 2.0 + ATOM/iTunes parser, July 2020
 #      feedme-2.0.js
 #
 #   Copyright 2020 Abu Khadeejah Karl Holz
 #
 #   https://github.com/salamcast
 #
 #   Licensed under the Apache License, Version 2.0 (the "License");
 #   you may not use this file except in compliance with the License.
 #   You may obtain a copy of the License at
 #
 #       http://www.apache.org/licenses/LICENSE-2.0
 #
 #   Unless required by applicable law or agreed to in writing, software
 #   distributed under the License is distributed on an "AS IS" BASIS,
 #   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 #   See the License for the specific language governing permissions and
 #   limitations under the License.
 #
 ################################################################################

 Reference for RSS 2.0

 http://www.rssboard.org/rss-2-0

 Reference for iTunes Podcasting 

 http://www.apple.com/itunes/podcasts/specs.html
 http://www.podcast411.com/howto_1.html

 Reference for ATOM

 http://tools.ietf.org/html/rfc4287

 */


var feedMe = function ($url) {
// XML Namespaces used in RSS, ATOM, iTunes and other common feeds
    var opts = {
        itunes: 'http://www.itunes.com/dtds/podcast-1.0.dtd',
        atom: 'http://www.w3.org/2005/Atom',
        cc: 'http://backend.userland.com/creativeCommonsRssModule',  // http://www.rssboard.org/creative-commons
        trackback: 'http://madskills.com/public/xml/rss/module/trackback/', // http://www.rssboard.org/trackback
        blogChannel: "http://backend.userland.com/blogChannelModule",
        media: "http://search.yahoo.com/mrss/",
        yt: "http://www.youtube.com/xml/schemas/2015"
    };
    // ********************************************************************************
    // RSS items in both channel and item
    // ********************************************************************************
    // RSS feed title, should be the same as the HTML title for channel and title for the item
    var title = function (c) {
        return c.find('title').first().text();
    };
    // RSS feed link for main site for channel and url for feed item
    var link = function (c) {
        return c.find('link').first().text();
    };
    // RSS description for both channel and item
    var description = function (c) {
        return c.find('description').first().text();
    };
    // RSS category, this just selects everything with a category, even in items
    // i'll need to figure out something for later to not select the categores in items
    var category = function (c) {
        return c.find('category').text();
    };
    // ********************************************************************************
    // iTunes items both in channel and item/entry tags
    // ********************************************************************************
    // <itunes:author> - Artist column
    var i_author = function (c) {
        return c.find('*').ns_filter(opts.itunes, 'author').first().text();
    };
    // <itunes:block> - prevent an episode or podcast from appearing
    var i_block = function (c) {
        return c.find('*').ns_filter(opts.itunes, 'block').first().text();
    };
    //<itunes:image> - Same location as album art
    var i_image = function (c) {
        return c.find('*').ns_filter(opts.itunes, 'image').first().attr('href');
    };
    //<itunes:explicit> - parental advisory graphic in Name column
    var i_explicit = function (c) {
        return c.find('*').ns_filter(opts.itunes, 'explicit').first().text();
    };
    //<itunes:keywords> - not visible but can be searched
    var i_keywords = function (c) {
        return c.find('*').ns_filter(opts.itunes, 'keywords').first().text();
    };
    //<itunes:subtitle> - Description column
    var i_subtitle = function (c) {
        return c.find('*').ns_filter(opts.itunes, 'subtitle').first().text();
    };
    //<itunes:summary> - when the "circled i" in Description column is clicked
    var i_summary = function (c) {
        return c.find('*').ns_filter(opts.itunes, 'summary').first().text();
    };
    // ********************************************************************************
    // iTunes Channel items
    // ********************************************************************************
    /* itunes tags in channel tag
     xml tag			      channel	item	where content appears in iTunes
     <itunes:category>   	    Y	 	        Category column and in iTunes Store Browse
     <itunes:complete>   	    Y		        indicates completion of podcasts; no more episodes
     <itunes:new-feed-url>	    Y		        not visible, used to inform iTunes of new feed URL location
     <itunes:owner>		        Y	 	        not visible, used for contact only
     // ********************************************************************************  */
    var i_channel = function (c) {
        var i = {};
        i.author = i_author(c);
        i.block = i_block(c);
        i.category = c.find('*').ns_filter(opts.itunes, 'category').attr('text');
        i.img = i_image(c);
        i.explicit = i_explicit(c);
        i.complete = c.find('*').ns_filter(opts.itunes, 'complete').first().text();
        i.keywords = i_keywords(c);
        i.new_feed_url = c.find('*').ns_filter(opts.itunes, 'new-feed-url').first().text();
        i.owner = c.find('*').ns_filter(opts.itunes, 'owner').first().text();
        i.subtitle = i_subtitle(c);
        i.desc = i_summary(c);
        return i;
    };
    // ********************************************************************************
    /* itunes tags in item/entry tag
     xml tag			            channel	  item  	where content appears in iTunes
     <itunes:duration>		 	                Y	    Time column
     <itunes:isClosedCaptioned>		            Y	    Closed Caption graphic in Name column
     <itunes:order>		            	        Y   	override the order of episodes on the store
     // ******************************************************************************** */
    var i_item = function ($item) {
        return {
            author: i_author($item),
            block: i_block($item),
            img: i_image($item),
            time: $item.find('*').ns_filter(opts.itunes, 'duration').first().text(),
            explicit: i_explicit($item),
            isCC: $item.find('*').ns_filter(opts.itunes, 'isClosedCaptioned').first().text(),
            order: $item.find('*').ns_filter(opts.itunes, 'order').first().text(),
            keywords: i_keywords($item),
            subtitle: i_subtitle($item)
        };
    };
    // ********************************************************************************
    //  ATOM items in both channel/feed and item/entry
    // ********************************************************************************
    //<atom:id> ATOM id
    var a_id = function (c) {
        return c.find('*').ns_filter(opts.atom, 'id').first().text();
    };
    //<atom:title> ATOM title
    var a_title = function (c) {
        return c.find('*').ns_filter(opts.atom, 'title').first().text();
    };
    //<atom:summary> ATOM summary
    var a_summary = function (c) {
        return c.find('*').ns_filter(opts.atom, 'summary').first().text();
    };
    // ********************************************************************************
    // ATOM items in RSS
    // ********************************************************************************
    var atom = function (c) {
        a = {};
        a.id = a_id(c);
        a.title = a_title(c);
        a.summary = a_summary(c);
        return a;
    };

    // ********************************************************************************
    var cloud = function (c) {
        var cd = {};
        cd.domain = c.find('cloud').attr('domain');
        cd.port = c.find('cloud').attr('port');
        cd.path = c.find('cloud').attr('path');
        cd.registerProcedure = c.find('cloud').attr('registerProcedure');
        cd.protocol = c.find('cloud').attr('protocol');
        return cd;
    };
    // ********************************************************************************
    var rss_img = function (c) {
        var img = {};
        img.url = c.find('image').find('url').first().text();
        img.title = c.find('image').find('title').first().text();
        img.link = c.find('image').find('link').first().text();
        img.width = c.find('image').find('width').first().text(); // max 144, default 88
        img.height = c.find('image').find('height').first().text(); // max 400, default 31
        img.description = c.find('image').find('description').first().text();
        return img;
    };
    // ********************************************************************************
    var text_input = function (c) {
        var txt = {};
        txt.title = c.find('textInput').find('title').first().text();
        txt.link = c.find('textInput').find('link').first().text();
        txt.name = c.find('textInput').find('name').first().text();
        txt.description = c.find('image').find('description').first().text();
        return txt;
    };
    // ********************************************************************************
    var track_back = function ($item) {
        var tb = {
            about: $item.find('*').ns_filter(opts.trackback, 'about').first().text(),
            ping: $item.find('*').ns_filter(opts.trackback, 'ping').first().text()
        };
        return tb;
    };
    // ********************************************************************************
    var enclosure = function ($item) {

        var e = {	// enclosure
            url: $item.find('enclosure').attr("url"),
            type: $item.find('enclosure').attr("type"),
            size: $item.find('enclosure').attr("length")
        };
        return e;
    };
    // ********************************************************************************
    // ********************************************************************************
    var chan = {};
    chan.items = [];
    $.ajax({
        type: 'GET', url: $url, dataType: 'xml', async: false, success: function (d) {
            $(d).find('channel').each(function () {
                var c = jQuery(this);

                chan.title = title(c); // Name column (itunes)
                chan.link = link(c); //website link and arrow in Name column (itunes)
                chan.description = description(c);
                chan.lang = c.find('language').first().text();
                chan.copyright = c.find('copyright').first().text(); // not visible (itunes)
                chan.managingEditor = c.find('managingEditor').first().text();
                chan.webMaster = c.find('webMaster').first().text();
                chan.pubDate = c.find('pubDate').first().text();
                chan.lastBuildDate = c.find('lastBuildDate').first().text();
                chan.category = category(c);
                chan.generator = c.find('generator').first().text();
                chan.docs = c.find('docs').first().text();
                chan.cloud = cloud(c);
                chan.ttl = c.find('ttl').first().text();
                chan.image = rss_img(c);
                chan.rating = c.find('rating').first().text(); // PICS rating - http://www.w3.org/PICS/ - FYI not maintained
                chan.textInput = text_input(c);
//         skipHours: '', skipDays: '',  // i'll have to look into this later on, many rss readers ignore this, so i will too for now
                chan.licence = c.find('*').ns_filter(opts.cc, 'license').first().text();
                chan.itunes = i_channel(c);
                chan.atom = atom(c);

            });

            // this is based on youtubes feed
            // found the link in this article
            // http://authorityseolab.com/how-to-find-your-youtube-feeds-2015-and-later/
            $(d).find('feed').each(function () {
                var c = jQuery(this);

                chan.title = title(c); // Name column (itunes)
                chan.link = c.find("author").find('uri').first().text();
//                chan.description = description(c);

                chan.author = c.find('author').find('name').first().text();
                chan.pubDate = c.find('published').first().text();




            });

            // rss 2.0 item
            $(d).find('item').each(function () {
                var $item = jQuery(this);
                var I = {};
                I.title = title($item); // Name column(itunes)
                I.link = link($item);
                I.description = description($item);
                I.author = $item.find('author').first().text();
                I.category = category($item);
                I.comments = $item.find('comments').text();

                I.enclosure = enclosure($item);
                I.guid = $item.find('guid').first().text();
                I.isPermaLink = $item.find('guid').attr('isPermaLink');
                I.pubdate = $item.find('pubDate').text(); // Release Date column (itunes)
                I.source = $item.find('source').first().text();
                I.source_url = $item.find('source').first().attr('url');
                // extras
                I.licence = $item.find('*').ns_filter(opts.cc, 'license').first().text();
                I.trackback = track_back($item);

                I.itunes = i_item($item);

                I.atom = atom($item);

                chan.items.push(I);
            });

            $(d).find('entry').each(function () {
                var $item = jQuery(this);
                var I = {};
                I.title = title($item); // Name column(itunes)
                I.link = $item.find("author").find('uri').first().text();
                I.description = $item.find('*').ns_filter(opts.media, 'description').first().text();
                I.author = $item.find('author').find('name').first().text();


                I.id = $item.find('id').first().text();
                I.pubdate = $item.find('published').text(); // Release Date column (itunes)



                chan.items.push(I);
            });

        }
    });

    //filter function to remove all empty and undefined elements
    var channel = {};
    $.each(chan, function (k, v) {
        if (v === '' || typeof v == "undefined") {
            //place holder for skipping this type of value
        } else if (typeof v == "object") {
            channel[k] = {};
            $.each(v, function (k2, v2) {
                if (v2 === '' || typeof v2 == "undefined") {
                    //place holder for skipping this type of value
                } else if (typeof v2 === "object") {
                    channel[k][k2] = {};
                    $.each(v2, function (k3, v3) {
                        if (v3 == '' || typeof v3 == "undefined") {
                            //place holder for skipping this type of value
                        } else if (typeof v3 === "object") {
                            channel[k][k2][k3] = {};
                            $.each(v3, function (k4, v4) {
                                if (v4 == '' || typeof v4 == "undefined") {
                                    //place holder for skipping this type of value
                                } else if (typeof v4 === "object") {
                                    channel[k][k2][k3][k4] = {};
                                    $.each(v4, function (k5, v5) {
                                        if (v5 == '' || typeof v5 == "undefined") {
                                            //place holder for skipping this type of value
                                        } else if (typeof v5 === "object") {
                                            channel[k][k2][k3][k4][k5] = {};
                                            //don't think there is anymore objects this deep, but you'll see an empty one if there is


//                                            if (jQuery.isEmptyObject(channel[k][k2][k3][k4][k5])) {
//                                                delete channel[k][k2][k3][k4][k5];
//                                            }
                                        } else {
                                            channel[k][k2][k3][k4][k5] = v5;
                                        }
                                    });
                                    if (jQuery.isEmptyObject(channel[k][k2][k3][k4])) {
                                        delete channel[k][k2][k3][k4];
                                    }
                                } else {
                                    channel[k][k2][k3][k4] = v4;
                                }
                            });
                            if (jQuery.isEmptyObject(channel[k][k2][k3])) {
                                delete channel[k][k2][k3];
                            }

                        } else {
                            channel[k][k2][k3] = v3;
                        }
                    });
                    if (jQuery.isEmptyObject(channel[k][k2])) {
                        delete channel[k][k2];
                    }
                } else {
                    channel[k][k2] = v2;
                }
            });
            if (jQuery.isEmptyObject(channel[k])) {
                delete channel[k];
            }
        } else {
            channel[k] = v;
        }
    });

    return channel;

};

//#############################################################################################
// Extras to make this plugin work
//#############################################################################################
// ns_filter, a jQuery plugin for XML namespace queries.
// http://www.ibm.com/developerworks/xml/library/x-feedjquery/#listing2
(function ($) {
    $.fn.ns_filter = function (namespaceURI, localName) {
        return $(this).filter(function () {
            var domnode = $(this)[0];
            return (domnode.namespaceURI == namespaceURI && domnode.localName == localName);
        });
    };
})(jQuery);

//feedRead
/**
 * The goal of this plugin is to demonstrate how feedme works as an RSS 2.0 feed reader for your webpage
 * This is only aimed at reading feeds, it won't act as a podcast media player ... yet.  this is my research plugin,
 * I might tryout the HTML 5 Audio/Video tags, but there will be not guarantees of the content playing since not all
 * feeds contain HTML 5 compatible content.
 */
(function ($) {
    $.feedRead = function (newopt) {
        var opt = {
            itunes: true, //podcast url
            debug: false,
            feed_list: '#RSS_feeds',
            feed: '#feed',
            channel: '#chan_info',
            item: '#item_info'

        };

        $.extend(opt, newopt);

        // common html
        var div_row = function (txt) {
            return '<div class="row"><div class="col-md-12 ">' + txt + '</div></div>';
        };

        var rss_link = function (link, title) {
            return div_row('<a href="' + link + '" target="_blank">' + title + '</a>');
        };

        var rss_img = function (img) {
            return div_row('<img src="' + img + '"/>');
        };

        // no div
        var mail_link = function (e, t) {
            return '<a href="mailto:' + e + '" >' + t + '</a>';
        };

        // leaving room for other debug options
        var debug_chan_menu = function(){
            var debug_chan;
            debug_chan = '<ul class="nav navbar-nav ">';
            // chan array
            debug_chan += '<li class="dropdown" ><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Debug Channel<span class="caret"></span></a>';
            debug_chan += '<ul class="dropdown-menu" id="debug_chan"><li><a href="#">Nothing Loaded ...</a></li></ul></li>';
            // item array
            debug_chan += '<li class="dropdown" ><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Debug item<span class="caret"></span></a>';
            debug_chan += '<ul class="dropdown-menu" id="debug_item"><li><a href="#">Nothing Loaded ...</a></li></ul>';
            // should add menus for print pretty
            debug_chan += '<li class="dropdown" ><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Print Pretty<span class="caret"></span></a>';
            debug_chan += '<ul class="dropdown-menu" id="debug"><li><a href="#">Nothing Loaded ...</a></li></ul>';
            debug_chan += '</li></ul>';
            return debug_chan;
        };

        if (opt.debug) {
            $('#debug_menu').append(debug_chan_menu());
        }


        // empty feed lists
        $(opt.feed_list).empty();
        $('link[type="application/rss+xml"]').each(function () {
            var link = $(this).attr('href');
            var title = $(this).attr('title');
            $(opt.feed_list).append('<li><a href="' + link + '">' + title + '</a></li>');

        });
        $(opt.feed_list + ' > li > a').click(function (e) {
            e.preventDefault();
            var url = $(this).attr('href');
            var feed = feedMe(url);
            $(opt.feed).empty();
            $(opt.channel).empty();
            $(opt.item).empty();
            //subtitle
            var subtitle;
            if (opt.itunes && feed.itunes && typeof feed.itunes['subtitle'] == 'string') {
                subtitle = '<h5>' + feed.itunes['subtitle'] + '</h5>';
            }

            //description
            var desc;
            if (opt.itunes && feed.itunes && typeof feed.itunes['desc'] == 'string') {
                desc = div_row('<p>' + feed.itunes['desc'] + '</p>');
            } else {
                desc = div_row('<p>' + feed.description + '</p>');
            }

            //image
            var img;

            if (opt.itunes && feed.itunes && typeof feed.itunes['img'] == 'string') {
                img = rss_img(feed.itunes['img']);
            } else {
                if (typeof feed.image != 'undefined') {
                    img = rss_img(feed.image['url']);
                }
            }

            // channel emails
            var author;
            var email;
            if (typeof feed.managingEditor != 'undefined') {
                email = mail_link(feed.managingEditor, '[ Editor ]');
                email += " ";
            }
            if (typeof feed.webMaster != 'undefined') {
                email += mail_link(feed.webMaster, '[ WebMaster ]');
            }
            if (opt.itunes && feed.itunes && typeof feed.itunes['author'] == 'string') {
                if (typeof email == 'string') {
                    author = div_row('Contact <em>' + feed.itunes['author'] + '</em>: ' + email);
                } else {
                    author = rss_link(feed.link, feed.itunes['author']);
                }
            } else {
                if (typeof email == 'string') {
                    author = div_row('Contact: ' + email);
                }
            }
            var copyright;
            if (typeof feed.copyright == 'string') {
                copyright=rss_link(feed.link, '<strong>' + feed.copyright + '</strong>');
            }

            var pubdate;
            if (typeof feed.pubDate == 'string') {
                pubdate = feed.pubDate;
            }

            //title
            $(opt.channel).append(rss_link(feed.link, '<h3>' + feed.title + '</h3>'));
            if (typeof subtitle == 'string') {
                $(opt.channel).append(subtitle);
            }
            if(typeof pubdate == 'string') {
                $(opt.channel).append(pubdate);
            }
            if (typeof author == 'string') {
                $(opt.channel).append(author);
            }
            if (typeof desc == 'string') {
                $(opt.channel).append(desc);
            }
            if (typeof img == 'string') {
                $(opt.channel).append(img);
            }

            if (typeof copyright == 'string') {
                $(opt.channel).append(copyright);
            }





          //  $(opt.channel).append('<div class="row"><div class="col-md-8 "><p>Last Build Date: ' + feed.lastBuildDate + '</p></div></div>');


            if (opt.debug) {

                $('#debug_chan').empty();
                $.each(feed, function (k, v) {
                    if (typeof v != 'object') {
                        if (v != '') {
                            $('#debug_chan').append('<li><strong>' + k + ':</strong> ' + v + '</li>');
                        }
                    } else if (typeof v == 'object') {
                        $.each(v, function (k2, v2) {
                            if (typeof v2 != 'object') {
                                $('#debug_chan').append('<li><strong>' + k + '</strong> -&gt; <em>' + k2 + '</em>: ' + v2 + '</li>');
                            }

                        });

                    }
                });


            }
            /*

             */
            $.each(feed.items, function (n, i) {
                $(opt.feed).prepend('<li><a id="fItem' + n + '" href="#" media="">' + i.title + '</a></li>');

                $('#fItem' + n).click(function (e) {
                    e.preventDefault();
                    $(opt.item).empty();
                    $.each(i, function (k, v) {
                        if (typeof v != 'object') {
                            if (v != '') {
                                switch (k) {
                                    case 'title':
                                        $(opt.item).append(div_row('<h3>' + v + '</h3>'));
                                        break;
                                    case 'link':
                                        $(opt.item).append(div_row('<a href="' + v + '" target="_blank">' + v + '</a>'));
                                        break;
                                    case 'guid':
                                        $(opt.item).append(div_row('<a href="' + v + '" target="_blank">GUID: ' + v + '</a>'));
                                        break;
                                    case 'description':
                                        $(opt.item).append(div_row('<p>' + v + '</p>'));
                                        break;


                                    case 'author':
                                        $(opt.item).append('<div class="row"><div class="col-md-8 "><strong>' + v + '</strong></div></div>');
                                        break;

                                    case 'pubDate':
                                        $(opt.item).append('<div class="row"><div class="col-md-8 "><p>Release Date' + v + '</p></div></div>');
                                        break;
                                    case 'lastBuildDate':
                                        $(opt.item).append('<div class="row"><div class="col-md-8 "><p>Last Build Date: ' + v + '</p></div></div>');
                                        break;

                                    default:
                                        $(opt.item).append('<div class="row"><div class="col-md-4 "><strong>' + k + ':</strong></div><div class="col-md-8 ">' + v + '<br/></div></div>');
                                }

                            }
                        } else if (typeof v == 'object') {
                            if (v != '') {
                                switch (k) {
                                    case 'enclosure':

                                        $(opt.item).append('<div class="row"><div class="col-md-8 "><a href="' + v.url + '">' + v.url + '</a></div></div>');

                                        break;
                                    case 'itunes':
                                        if (opt.itunes) {
                                            $(opt.item).append('<h3>itunes</h3>');
                                            $.each(v, function (k2, v2) {
                                                $(opt.item).append('<div class="row"><div class="col-md-8 "><p>' + k2 + ': ' + v2 + '</p></div></div>');
                                            });
                                        }
                                        break;
                                    default:

                                }

                            }
                        }
                    });
                    $('#debug > li').empty().append(prettyPrint(i));
                });

            });

            $('#debug > li').empty().append(prettyPrint(feed));

        });

        return true;
    };
})(jQuery);