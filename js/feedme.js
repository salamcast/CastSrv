/*
 
//#############################################################################################
// FeedMe - jQuery RSS 2.0 + ATOM/iTunes parser
//#############################################################################################
 Reference for RSS 2.0
 
 http://www.rssboard.org/rss-2-0

 Reference for iTunes Podcasting 

 http://www.apple.com/itunes/podcasts/specs.html
 http://www.podcast411.com/howto_1.html
 
 Reference for ATOM
 
 http://tools.ietf.org/html/rfc4287
 
*/



var feedMe = function($url, ask) {
// XML Namespaces used in RSS, ATOM, iTunes and other common feeds
  var opts = {
    itunes:      'http://www.itunes.com/dtds/podcast-1.0.dtd',
    atom:        'http://www.w3.org/2005/Atom',
    cc:          'http://backend.userland.com/creativeCommonsRssModule',  // http://www.rssboard.org/creative-commons
    trackback:   'http://madskills.com/public/xml/rss/module/trackback/', // http://www.rssboard.org/trackback
    blogChannel: "http://backend.userland.com/blogChannelModule"
  };
  // ********************************************************************************
  // RSS items in both channel and item
  // ********************************************************************************
  // RSS feed title, should be the same as the HTML title for channel and title for the item
  var title = function (c) { return c.find('title').first().text(); };
  // RSS feed link for main site for channel and url for feed item
  var link = function (c) { return c.find('link').first().text(); };
  // RSS description for both channel and item
  var description = function(c) { return c.find('description').first().text(); };
  // RSS category, this just selects everything with a category, even in items
  // i'll need to figure out something for later to not select the categores in items
  var category = function(c) { return c.find('category').text(); };
  // ********************************************************************************
  // iTunes items both in channel and item/entry tags
  // ********************************************************************************
  // <itunes:author> - Artist column
  var i_author = function (c) { return c.find('*').ns_filter(opts.itunes, 'author').first().text(); };
  // <itunes:block> - prevent an episode or podcast from appearing
  var i_block = function (c) { return c.find('*').ns_filter(opts.itunes, 'block').first().text(); };
  //<itunes:image> - Same location as album art
  var i_image = function (c) { return c.find('*').ns_filter(opts.itunes, 'image').first().attr('href'); };
  //<itunes:explicit> - parental advisory graphic in Name column
  var i_explicit = function (c) { return c.find('*').ns_filter(opts.itunes, 'explicit').first().text(); };
  //<itunes:keywords> - not visible but can be searched
  var i_keywords = function (c) { return c.find('*').ns_filter(opts.itunes, 'keywords').first().text(); };
  //<itunes:subtitle> - Description column
  var i_subtitle = function(c) { return c.find('*').ns_filter(opts.itunes, 'subtitle').first().text(); };
  //<itunes:summary> - when the "circled i" in Description column is clicked
  var i_summary = function(c) { return c.find('*').ns_filter(opts.itunes, 'summary').first().text(); };  
  // ********************************************************************************
  //  ATOM items in both channel/feed and item/entry
  // ********************************************************************************
  //<atom:id> ATOM id
  var a_id = function(c) { return c.find('*').ns_filter(opts.atom, 'id').first().text();  }
  //<atom:title> ATOM title
  var a_title = function(c) { return c.find('*').ns_filter(opts.atom, 'title').first().text(); };
  //<atom:summary> ATOM summary
  var a_summary = function(c) { return c.find('*').ns_filter(opts.atom, 'summary').first().text(); };
  // ********************************************************************************
  // iTunes Channel items
  // ********************************************************************************
  /* itunes tags in channel tag
    xml tag			channel	item	where content appears in iTunes
    <itunes:category>   	Y	 	Category column and in iTunes Store Browse
    <itunes:complete>   	Y		indicates completion of podcasts; no more episodes
    <itunes:new-feed-url>	Y		not visible, used to inform iTunes of new feed URL location
    <itunes:owner>		Y	 	not visible, used for contact only
  // ********************************************************************************  */
  var i_channel = function(c) {
    return {
      author: i_author(c),
      block: i_block(c),
      category:c.find('*').ns_filter(opts.itunes, 'category').attr('text'),
      img: i_image(c), 
      explicit: i_explicit(c),
      complete: c.find('*').ns_filter(opts.itunes, 'complete').first().text(),
      keywords: i_keywords(c),
      new_feed_url: c.find('*').ns_filter(opts.itunes, 'new-feed-url').first().text(),
      owner: c.find('*').ns_filter(opts.itunes, 'owner').first().text(),
      subtitle: i_subtitle(c),
      desc: i_summary(c),		
    };
  };
  // ********************************************************************************
  // ATOM items in RSS channel
  // ********************************************************************************
  var a_channel = function(c) {
    return {
      id: a_id(c),
      title: a_title(c),
      summary: a_summary(c)
    }; 
  };

  // ********************************************************************************
  /* itunes tags in item/entry tag
    xml tag			channel	item	where content appears in iTunes
    <itunes:duration>		 	Y	Time column
    <itunes:isClosedCaptioned>		Y	Closed Caption graphic in Name column
    <itunes:order>			Y	override the order of episodes on the store
  // ******************************************************************************** */
  var i_item = function($item) {
    return {
      author: i_author($item),
      block: 	i_block($item),
      img: i_image($item),
      time: $item.find('*').ns_filter(opts.itunes, 'duration').first().text(),
      explicit: i_explicit($item),
      isCC: $item.find('*').ns_filter(opts.itunes, 'isClosedCaptioned').first().text(),
      order: $item.find('*').ns_filter(opts.itunes, 'order').first().text(),
      keywords: i_keywords($item),
      subtitle: i_subtitle($item),
    };
  };
  // ********************************************************************************
  // ATOM items in RSS Item
  // ********************************************************************************
  var a_item = function(c) {
    return {
      id: a_id(c),
      title: a_title(c),
      summary: a_summary(c)
    };
  };

  // ********************************************************************************
  // ********************************************************************************
  // ********************************************************************************
  var chan;
  var base;
  var item = [];
  $.ajax({ type: 'GET', url: $url, dataType: 'xml', async: false, success: function(d){
    $(d).find('channel').each(function(){
      var c = jQuery(this);
      chan = {  //rss 2.0
	title: title(c), // Name column (itunes)
 	link:link(c), //website link and arrow in Name column (itunes)
        description: description(c),
	lang: c.find('language').first().text(),
	copyright: c.find('copyright').first().text(), // not visible (itunes)
	managingEditor: c.find('managingEditor').first().text(),
	webMaster: c.find('webMaster').first().text(),
	pubDate:c.find('pubDate').first().text(),
	lastBuildDate:c.find('lastBuildDate').first().text(),
        category: category(c),
	generator:c.find('generator').first().text(),
	docs: c.find('docs').first().text(),
        cloud: {
          domain: c.find('cloud').attr('domain'),
          port: c.find('cloud').attr('port'),
          path: c.find('cloud').attr('path'),
          registerProcedure: c.find('cloud').attr('registerProcedure'),
          protocol: c.find('cloud').attr('protocol')
        },
	ttl: c.find('ttl').first().text(),
        image: {
          url: c.find('image').find('url').first().text(),
          title: c.find('image').find('title').first().text(),
          link: c.find('image').find('link').first().text(),
          width: c.find('image').find('width').first().text(), // max 144, default 88
          height: c.find('image').find('height').first().text(), // max 400, default 31
          description: c.find('image').find('description').first().text()
        },
        rating: c.find('rating').first().text(), // PICS rating - http://www.w3.org/PICS/ - FYI not maintained
        textInput: {
	  title: c.find('textInput').find('title').first().text(),
	  link: c.find('textInput').find('link').first().text(),
	  name: c.find('textInput').find('name').first().text(),
	  description: c.find('image').find('description').first().text()
	},
//         skipHours: '', skipDays: '',  // i'll have to look into this later on, many rss readers ignore this, so i will too for now
        licence: c.find('*').ns_filter(opts.cc, 'license').first().text(),
        itunes: i_channel(c),
        atom: a_channel(c)
      }
// rss 2.0 item
    });
    
    $(d).find('item').each(function(){
      var $item = jQuery(this);
      var I = {
	title: title($item), // Name column(itunes)
        link: link($item),
        description: description($item),
        author: $item.find('author').first().text(),
        category: category($item),
	comments: $item.find('comments').text(),
        enclosure: {	// enclosure
          url: $item.find('enclosure').attr("url"),
          type: $item.find('enclosure').attr("type"),
          size: $item.find('enclosure').attr("length"),    
        },
	guid: $item.find('guid').first().text(),
        isPermaLink: $item.find('guid').attr('isPermaLink'),
	pubdate: $item.find('pubDate').text(), // Release Date column (itunes)
	source: $item.find('source').first().text(),
	source_url: $item.find('source').first().attr('url'),
        // extras
        licence: $item.find('*').ns_filter(opts.cc, 'license').first().text(),
        trackback: {
          about: $item.find('*').ns_filter(opts.trackback, 'about').first().text(),
          ping:  $item.find('*').ns_filter(opts.trackback, 'ping').first().text()
        },
        itunes: i_item($item),
        atom: a_item($item)
      };
      item.push(I);
    });
    chan.items = item;
  }});
  
  switch (ask) {
    case 'base': return Base64.encode(JSON.stringify(chan, null, 2)); break;
    case 'json': return JSON.stringify(chan, null, 2); break;
    default:  return chan;
  }
  return chan;
};

//#############################################################################################
// Extras to make this plugin work
//#############################################################################################
// ns_filter, a jQuery plugin for XML namespace queries.
// http://www.ibm.com/developerworks/xml/library/x-feedjquery/#listing2
(function($) {
 $.fn.ns_filter = function(namespaceURI, localName) {
  return $(this).filter(function() {
   var domnode = $(this)[0];
   return (domnode.namespaceURI == namespaceURI && domnode.localName == localName);
  });
 };
})(jQuery);