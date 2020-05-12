<?php

 /**
 * Univarsel Feed Writer class
 *
 * Genarate RSS 1.0, RSS2.0 and ATOM Feed
 *                             
 * @package     UnivarselFeedWriter
 * @author      Anis uddin Ahmad <anisniit@gmail.com>
 * @link        http://www.ajaxray.com/projects/rss
 */
 class FeedWriter
 {
	const RSS1 = 'RSS 1.0';
	const RSS2 = 'RSS 2.0';
	const ATOM = 'ATOM';

	private $channels      = array();  // Collection of channel elements
	private $items         = array();  // Collection of items as object of FeedItem class.
	private $data          = array();  // Store some other version wise data
	private $CDATAEncoding = array();  // The tag names which have to encoded as CDATA
	
	private $version   = null; 
	
	/**
	* Constructor
	* 
	* @param    constant    the version constant (RSS1/RSS2/ATOM).       
	*/ 
	function __construct($version = self::RSS2)
	{	
		$this->version = $version;
			
		// Setting default value for assential channel elements
		$this->channels['title']        = $version . ' Feed';
		$this->channels['link']         = $_SERVER["REQUEST_URL"];
				
		//Tag names to encode in CDATA
		$this->CDATAEncoding = array('title', 'description', 'content:encoded', 'summary');
	}

	// Start # public functions ---------------------------------------------
	
	/**
	* Set a channel element
	* @access   public
	* @param    srting  name of the channel tag
	* @param    string  content of the channel tag
	* @return   void
	*/
	public function setChannelElement($elementName, $content)
	{
		$this->channels[$elementName] = $content ;
	}
	
	/**
	* Set multiple channel elements from an array. Array elements 
	* should be 'channelName' => 'channelContent' format.
	* 
	* @access   public
	* @param    array   array of channels
	* @return   void
	*/
	public function setChannelElementsFromArray($elementArray)
	{
		if(! is_array($elementArray)) return;
		foreach ($elementArray as $elementName => $content) 
		{
			$this->setChannelElement($elementName, $content);
		}
	}
	
	/**
	* Genarate the actual RSS/ATOM file
	* 
	* @access   public
	* @return   void
	*/ 
	public function genarateFeed()
	{
		header("Content-type: text/xml");
		
		$this->printHead();
		$this->printChannels();
		$this->printItems();
		$this->printTale();
	}
	
	/**
	* Create a new FeedItem.
	* 
	* @access   public
	* @return   object  instance of FeedItem class
	*/
	public function createNewItem()
	{
		$Item = new FeedItem($this->version);
		return $Item;
	}
	
	/**
	* Add a FeedItem to the main class
	* 
	* @access   public
	* @param    object  instance of FeedItem class
	* @return   void
	*/
	public function addItem($feedItem)
	{
		$this->items[] = $feedItem;    
	}
	
	
	// Wrapper functions -------------------------------------------------------------------
	
	/**
	* Set the 'title' channel element
	* 
	* @access   public
	* @param    srting  value of 'title' channel tag
	* @return   void
	*/
	public function setTitle($title)
	{
		$this->setChannelElement('title', $title);
	}
	
	/**
	* Set the 'description' channel element
	* 
	* @access   public
	* @param    srting  value of 'description' channel tag
	* @return   void
	*/
	public function setDescription($desciption)
	{
		$this->setChannelElement('description', $desciption);
	}
	
	/**
	* Set the 'link' channel element
	* 
	* @access   public
	* @param    srting  value of 'link' channel tag
	* @return   void
	*/
	public function setLink($link)
	{
		$this->setChannelElement('link', $link);
	}
	
	/**
	* Set the 'image' channel element
	* 
	* @access   public
	* @param    srting  title of image
	* @param    srting  link url of the imahe
	* @param    srting  path url of the image
	* @return   void
	*/
	public function setImage($title, $link, $url)
	{
		$this->setChannelElement('image', array('title'=>$title, 'link'=>$link, 'url'=>$url));
	}
	
	/**
	* Set the 'about' channel element. Only for RSS 1.0
	* 
	* @access   public
	* @param    srting  value of 'about' channel tag
	* @return   void
	*/
	public function setChannelAbout($url)
	{
		$this->data['ChannelAbout'] = $url;    
	}
		
	/**
	* Genarates an UUID
	* @author     Anis uddin Ahmad <admin@ajaxray.com>
	* @param      string  an optional prefix
	* @return     string  the formated uuid
	*/
	public function uuid($key = null, $prefix = '') 
	{
		$key = ($key == null)? uniqid(rand()) : $key;
		$chars = md5($key);
		$uuid  = substr($chars,0,8) . '-';
		$uuid .= substr($chars,8,4) . '-';
		$uuid .= substr($chars,12,4) . '-';
		$uuid .= substr($chars,16,4) . '-';
		$uuid .= substr($chars,20,12);

		return $prefix . $uuid;
	}
	// End # public functions ----------------------------------------------
	
	// Start # private functions ----------------------------------------------
	
	/**
	* Prints the xml and rss namespace
	* 
	* @access   private
	* @return   void
	*/
	private function printHead()
	{
		$out  = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
		
		if($this->version == self::RSS2)
		{
			$out .= '<rss version="2.0"
					xmlns:content="http://purl.org/rss/1.0/modules/content/"
					xmlns:wfw="http://wellformedweb.org/CommentAPI/"
					encoding="utf-8">' . PHP_EOL;
		}    
		elseif($this->version == self::RSS1)
		{
			$out .= '<rdf:RDF 
					 xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
					 xmlns="http://purl.org/rss/1.0/"
					 xmlns:dc="http://purl.org/dc/elements/1.1/"
					 encoding="utf-8">' . PHP_EOL;;
		}
		else if($this->version == self::ATOM)
		{
			$out .= '<feed xmlns="http://www.w3.org/2005/Atom" encoding="utf-8">' . PHP_EOL;;
		}
		echo $out;
	}
	
	/**
	* Closes the open tags at the end of file
	* 
	* @access   private
	* @return   void
	*/
	private function printTale()
	{
		if($this->version == self::RSS2)
		{
			echo '</channel>' . PHP_EOL . '</rss>'; 
		}    
		elseif($this->version == self::RSS1)
		{
			echo '</rdf:RDF>';
		}
		else if($this->version == self::ATOM)
		{
			echo '</feed>';
		}
	  
	}

	/**
	* Creates a single node as xml format
	* 
	* @access   private
	* @param    srting  name of the tag
	* @param    mixed   tag value as string or array of nested tags in 'tagName' => 'tagValue' format
	* @param    array   Attributes(if any) in 'attrName' => 'attrValue' format
	* @return   string  formatted xml tag
	*/
	private function makeNode($tagName, $tagContent, $attributes = null)
	{        
		$nodeText = '';
		$attrText = '';

		if(is_array($attributes))
		{
			foreach ($attributes as $key => $value) 
			{
				$attrText .= " $key=\"$value\" ";
			}
		}
		
		if(is_array($tagContent) && $this->version == self::RSS1)
		{
			$attrText = ' rdf:parseType="Resource"';
		}
		
		
		$attrText .= (in_array($tagName, $this->CDATAEncoding) && $this->version == self::ATOM)? ' type="html" ' : '';
		$nodeText .= (in_array($tagName, $this->CDATAEncoding))? "<{$tagName}{$attrText}><![CDATA[" : "<{$tagName}{$attrText}>";
		 
		if(is_array($tagContent))
		{ 
			foreach ($tagContent as $key => $value) 
			{
				$nodeText .= $this->makeNode($key, $value);
			}
		}
		else
		{
			$nodeText .= (in_array($tagName, $this->CDATAEncoding))? $tagContent : htmlentities($tagContent);
		}           
			
		$nodeText .= (in_array($tagName, $this->CDATAEncoding))? "]]></$tagName>" : "</$tagName>";

		return $nodeText . PHP_EOL;
	}
	
	/**
	* @desc     Print channels
	* @access   private
	* @return   void
	*/
	private function printChannels()
	{
		//Start channel tag
		switch ($this->version) 
		{
			case self::RSS2: 
				echo '<channel>' . PHP_EOL;        
				break;
			case self::RSS1: 
				echo (isset($this->data['ChannelAbout']))? "<channel rdf:about=\"{$this->data['ChannelAbout']}\">" : "<channel rdf:about=\"{$this->channels['link']}\">";
				break;
		}
		
		//Print Items of channel
		foreach ($this->channels as $key => $value) 
		{
			if($this->version == self::ATOM && $key == 'link') 
			{
				// ATOM prints link element as href attribute
				echo $this->makeNode($key,'',array('href'=>$value));
				//Add the id for ATOM
				echo $this->makeNode('id',$this->uuid($value,'urn:uuid:'));
			}
			else
			{
				echo $this->makeNode($key, $value);
			}    
			
		}
		
		//RSS 1.0 have special tag <rdf:Seq> with channel 
		if($this->version == self::RSS1)
		{
			echo "<items>" . PHP_EOL . "<rdf:Seq>" . PHP_EOL;
			foreach ($this->items as $item) 
			{
				$thisItems = $item->getElements();
				echo "<rdf:li resource=\"{$thisItems['link']['content']}\"/>" . PHP_EOL;
			}
			echo "</rdf:Seq>" . PHP_EOL . "</items>" . PHP_EOL . "</channel>" . PHP_EOL;
		}
	}
	
	/**
	* Prints formatted feed items
	* 
	* @access   private
	* @return   void
	*/
	private function printItems()
	{    
		foreach ($this->items as $item) 
		{
			$thisItems = $item->getElements();
			
			//the argument is printed as rdf:about attribute of item in rss 1.0 
			echo $this->startItem($thisItems['link']['content']);
			
			foreach ($thisItems as $feedItem ) 
			{
				echo $this->makeNode($feedItem['name'], $feedItem['content'], $feedItem['attributes']); 
			}
			echo $this->endItem();
		}
	}
	
	/**
	* Make the starting tag of channels
	* 
	* @access   private
	* @param    srting  The vale of about tag which is used for only RSS 1.0
	* @return   void
	*/
	private function startItem($about = false)
	{
		if($this->version == self::RSS2)
		{
			echo '<item>' . PHP_EOL; 
		}    
		elseif($this->version == self::RSS1)
		{
			if($about)
			{
				echo "<item rdf:about=\"$about\">" . PHP_EOL;
			}
			else
			{
				die('link element is not set .\n It\'s required for RSS 1.0 to be used as about attribute of item');
			}
		}
		else if($this->version == self::ATOM)
		{
			echo "<entry>" . PHP_EOL;
		}    
	}
	
	/**
	* Closes feed item tag
	* 
	* @access   private
	* @return   void
	*/
	private function endItem()
	{
		if($this->version == self::RSS2 || $this->version == self::RSS1)
		{
			echo '</item>' . PHP_EOL; 
		}    
		else if($this->version == self::ATOM)
		{
			echo "</entry>" . PHP_EOL;
		}
	}
	

	
	// End # private functions ----------------------------------------------
	
} // end of class FeedWriter
	 



/*
 EXAMPLES

	// IMPORTANT : No need to add id for feed or channel. It will be automatically created from link.

	//Creating an instance of FeedWriter class. 
	//The constant ATOM is passed to mention the version
	$TestFeed = new FeedWriter(ATOM);

	//Setting the channel elements
	//Use wrapper functions for common elements
	$TestFeed->setTitle('Testing the RSS writer class');
	$TestFeed->setLink('http://www.ajaxray.com/rss2/channel/about');
	
	//For other channel elements, use setChannelElement() function
	$TestFeed->setChannelElement('updated', date(DATE_ATOM , time()));
	$TestFeed->setChannelElement('author', array('name'=>'Anis uddin Ahmad'));

	//Adding a feed. Genarally this protion will be in a loop and add all feeds.

	//Create an empty FeedItem
	$newItem = $TestFeed->createNewItem();
	
	//Add elements to the feed item
	//Use wrapper functions to add common feed elements
	$newItem->setTitle('The first feed');
	$newItem->setLink('http://www.yahoo.com');
	$newItem->setDate(time());
	//Internally changed to "summary" tag for ATOM feed
	$newItem->setDescription('This is test of adding CDATA Encoded description by the php <b>Universal Feed Writer</b> class');

	//Now add the feed item	
	$TestFeed->addItem($newItem);
	
	//OK. Everything is done. Now genarate the feed.
	$TestFeed->genarateFeed();
  
?>
<?php
  // This is a minimum example of using the class
  include("FeedWriter.php");
  
  //Creating an instance of FeedWriter class. 
  $TestFeed = new FeedWriter(RSS2);
  
  //Setting the channel elements
  //Use wrapper functions for common channel elements
  $TestFeed->setTitle('Testing & Checking the RSS writer class');
  $TestFeed->setLink('http://www.ajaxray.com/projects/rss');
  $TestFeed->setDescription('This is test of creating a RSS 2.0 feed Universal Feed Writer');
  
  //Image title and link must match with the 'title' and 'link' channel elements for valid RSS 2.0
  $TestFeed->setImage('Testing the RSS writer class','http://www.ajaxray.com/projects/rss','http://www.rightbrainsolution.com/images/logo.gif');
  
	//Detriving informations from database addin feeds
	$db->query($query);
	$result = $db->result;

	while($row = mysql_fetch_array($result, MYSQL_ASSOC))
	{
		//Create an empty FeedItem
		$newItem = $TestFeed->createNewItem();
		
		//Add elements to the feed item    
		$newItem->setTitle($row['title']);
		$newItem->setLink($row['link']);
		$newItem->setDate($row['create_date']);
		$newItem->setDescription($row['description']);
		
		//Now add the feed item
		$TestFeed->addItem($newItem);
	}
  
  //OK. Everything is done. Now genarate the feed.
  $TestFeed->genarateFeed();
  
?>
<?php
  
  include("FeedWriter.php");
  
  //Creating an instance of FeedWriter class. 
  //The constant RSS1 is passed to mention the version
  $TestFeed = new FeedWriter(RSS1);
  
  //Setting the channel elements
  //Use wrapper functions for common elements
  //For other optional channel elements, use setChannelElement() function
  $TestFeed->setTitle('Testing the RSS writer class');
  $TestFeed->setLink('http://www.ajaxray.com/rss2/channel/about');
  $TestFeed->setDescription('This is test of creating a RSS 1.0 feed by Universal Feed Writer');
   
  //It's important for RSS 1.0 
  $TestFeed->setChannelAbout('http://www.ajaxray.com/rss2/channel/about');
  
  //Adding a feed. Genarally this protion will be in a loop and add all feeds.
  
  //Create an empty FeedItem
  $newItem = $TestFeed->createNewItem();
  
  //Add elements to the feed item
  //Use wrapper functions to add common feed elements
  $newItem->setTitle('The first feed');
  $newItem->setLink('http://www.yahoo.com');
  //The parameter is a timestamp for setDate() function
  $newItem->setDate(time());
  $newItem->setDescription('This is test of adding CDATA Encoded description by the php <b>Universal Feed Writer</b> class');
  //Use core addElement() function for other supported optional elements
  $newItem->addElement('dc:subject', 'Nothing but test');
  
  //Now add the feed item
  $TestFeed->addItem($newItem);
  
  //Adding multiple elements from array
  //Elements which have an attribute cannot be added by this way
  $newItem = $TestFeed->createNewItem();
  $newItem->addElementArray(array('title'=>'The 2nd feed', 'link'=>'http://www.google.com', 'description'=>'This is test of feedwriter class'));
  $TestFeed->addItem($newItem);
  
  //OK. Everything is done. Now genarate the feed.
  $TestFeed->genarateFeed();
  
?>
<?php
  
  include("FeedWriter.php");
  
  //Creating an instance of FeedWriter class. 
  //The constant RSS2 is passed to mention the version
  $TestFeed = new FeedWriter(RSS2);
  
  //Setting the channel elements
  //Use wrapper functions for common channel elements
  $TestFeed->setTitle('Testing & Checking the RSS writer class');
  $TestFeed->setLink('http://www.ajaxray.com/projects/rss');
  $TestFeed->setDescription('This is test of creating a RSS 2.0 feed Universal Feed Writer');
  
  //Image title and link must match with the 'title' and 'link' channel elements for RSS 2.0
  $TestFeed->setImage('Testing the RSS writer class','http://www.ajaxray.com/projects/rss','http://www.rightbrainsolution.com/images/logo.gif');
  
  //Use core setChannelElement() function for other optional channels
  $TestFeed->setChannelElement('language', 'en-us');
  $TestFeed->setChannelElement('pubDate', date(DATE_RSS, time()));
  
  //Adding a feed. Genarally this portion will be in a loop and add all feeds.
  
  //Create an empty FeedItem
  $newItem = $TestFeed->createNewItem();
  
  //Add elements to the feed item
  //Use wrapper functions to add common feed elements
  $newItem->setTitle('The first feed');
  $newItem->setLink('http://www.yahoo.com');
  //The parameter is a timestamp for setDate() function
  $newItem->setDate(time());
  $newItem->setDescription('This is test of adding CDATA Encoded description by the php <b>Universal Feed Writer</b> class');
  $newItem->setEncloser('http://www.attrtest.com', '1283629', 'audio/mpeg');
  //Use core addElement() function for other supported optional elements
  $newItem->addElement('author', 'admin@ajaxray.com (Anis uddin Ahmad)');
  //Attributes have to passed as array in 3rd parameter
  $newItem->addElement('guid', 'http://www.ajaxray.com',array('isPermaLink'=>'true'));
  
  //Now add the feed item
  $TestFeed->addItem($newItem);
  
  //Another method to add feeds from array()
  //Elements which have attribute cannot be added by this way
  $newItem = $TestFeed->createNewItem();
  $newItem->addElementArray(array('title'=>'The 2nd feed', 'link'=>'http://www.google.com', 'description'=>'This is test of feedwriter class'));
  $TestFeed->addItem($newItem);
  
  //OK. Everything is done. Now genarate the feed.
  $TestFeed->genarateFeed();
  
 */
