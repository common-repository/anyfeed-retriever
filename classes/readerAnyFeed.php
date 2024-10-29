<?php
namespace anyfeedretriever;
/**
 *  Any Feed Retriever;
 *
 * SimpleXML based feed reader class. A very specific feed reader designed
 * to working with no or little configurations.
 *
 * - This class can read an rss feed into array from a given url.
 * - Also can render html widget with plugable RayFeedWidget Class.
 *
 * Supports following feed types:
 *  - RSS 0.92
 *  - RSS 2.0
 *  - Atom
 *
 * Configuration Options
 *  - array
 *      - url: (string)
 *          - feed url
 *
 *      - httpClient: (string)
 *          - default SimpleXML
 *          - value rayHttp or SimpleXML
 *
 *      - type: ([optional] string
 *          - auto detect
 *          - value rss or rss2 or atom
 *
 *      - widget: ([optional] string)
 *          - feed widget class name for rendering html
 *
 *      - rayHttp: (array)
 *          - only if httpClient is set to rayHttp
 *          - rayHttp Options if you want to modify rayHttml CURL options
 *          - generally not required.
 *
 *
 *
 *
 * @version 1.0.1
 * @author Anushka Rajasingha
 * @package anyfeedretriever;
 * @license GPL
 */
Class AnyFeedReader{

    /**
     * Self Instance for Singleton Pattern
     *
     * @var object
     * @access protected
     */
    static private  $__instance;

    /**
     * Instance of Parser Class.
     *
     * @var object Parser Class
     * @access protected
     */
    Protected       $_Parser;

    /**
     * Feed Url
     *
     * @var string feed url
     * @access protected
     */
    protected       $_url;

    /**
     * Runtime Options for reader
     *
     * @var array
     * @access protected
     */
    protected       $_options = array('rayHttp' => array());

    /**
     * Type of feed to be parsed.
     *
     * @var string
     * @access protected
     */
    protected       $_type = "rss";

    /**
     * HttpClient to be used for loading feed content.
     *
     *  - default SimpleXML
     *
     * @var string 'SimpleXML' or 'rayHttp'
     * @access protected
     */
    protected       $_httpClient = "SimpleXML";

    /**
     * Widget Class Name
     *
     * @var string
     * @access protected
     */
    protected       $_widget;

    /**
     * Parsed result data
     *
     * @var array
     * @access protected
     */
    protected       $_content;

    /**
     * Class construct
     *
     * @param array $options
     */
    function __construct($options = array()) {
        $this->setOptions($options);
    }

    /**
     * Get Instance of the class.
     *
     * @param array $options
     * @return object self instance.
     * @access public
     * @static
     */
    static function &getInstance($options = array()) {
        if (is_null(self::$__instance)) {
            self::$__instance = new self($options);
        }
        return self::$__instance;
    }

    /**
     * Set Options for the class
     *
     *
     * @param array $options
     * @return object self instance
     * @access public
     */
    function &setOptions($options) {
        if (!empty($options['url'])) {
            $this->_url = $options['url'];
        }

        if (!empty($options['type'])) {
            $this->_type = $options['type'];
        }

        if (!empty($options['httpClient'])) {
            $this->_httpClient = $options['httpClient'];
        }

        if (!empty($options['widget'])) {
            $this->_widget = $options['widget'];
        }

        $this->_options = array_merge($this->_options, $options);

        return $this;
    }

    /**
     * Parse feed contents into an array and return self object
     *
     * @return object self instance
     * @access public
     */
    function &parse() {
        /**
         * Get/load content
         */
        switch ($this->_httpClient) {
            case 'SimpleXML':
                $content = new \SimpleXMLElement($this->_url, LIBXML_NOCDATA, true);
                break;

            case 'rayHttp':

                $content = \RayHttp::getInstance()->setOptions($this->_options['rayHttp'])->get($this->_url);

                if (!empty($content)) {
                    $content = new \SimpleXMLElement($content, LIBXML_NOCDATA);
                }
                break;
        }

        if (empty($content)) {
            trigger_error("XML format is invalid or broken.", E_USER_ERROR);
        }

        /**
         * Detect Feed Type
         */

        if (empty($this->_type)) {

            switch ($content->getName()) {
                case 'rss':
                    foreach ($content->attributes() as $attribute) {
                        if ($attribute->getName() == 'version') {
                            if ('2.0' == $attribute) {
                                self::setOptions(array('type' => 'rss2'));
                            } elseif (in_array($attribute, array('0.92', '0.91'))) {
                                self::setOptions(array('type' => 'rss'));
                            }
                        }
                    }
                    break;

                case 'feed':
                    self::setOptions(array('type' => 'atom'));

                    break;
            }

        }

        if (!in_array($this->_type, array('rss', 'rss2', 'atom'))) {

            trigger_error("Feed type is either invalid or not supported.", E_USER_ERROR);

            return false;
        }


        /**
         * Parse Feed Content
         */
        switch ($this->_type) {
            case 'rss':
                $content = $this->parseRss($content);
                break;

            case 'rss2':
                $content = $this->parseRss2($content);
                break;

            case 'atom':
                $content = $this->parseAtom($content);
                break;
        }

        if (empty($content)) {
            trigger_error("No content is found.", E_USER_ERROR);
        }

        $this->_content = $content;

        return $this;

    }

    /**
     * Get Array of Parsed XML feed data.
     *
     * @return array parsed feed content.
     * @access public
     */
    function getData() {
        return $this->_content;
    }

    /**
     * Return html widget based rendered by widget class
     *
     *
     * @param array $options for html widget class
     * @return string html widget
     * @access public
     */
    function widget($options = array('widget' => 'brief')) {
        if (!empty($this->_widget) && !empty($this->_content)) {
            $Widget = new $this->_widget;

            return $Widget->widget($this->_content, $options);

        } else {
            return false;
        }
    }

    /**
     * Parse feed xml into an array.
     *
     * @param object $feedXml SimpleXMLElementObject
     * @return array feed content
     * @access public
     */
    function parseRss($feedXml) {
        $data = array();

        $data['title'] = $feedXml->channel->title . '';
        $data['link'] = $feedXml->channel->link . '';
        $data['description'] = $feedXml->channel->description . '';
        $data['parser'] = __CLASS__;
        $data['type'] = 'rss';
        if(isset($feedXml->channel->updated))
        $data['updated'] = $feedXml->channel->updated;


        foreach ($feedXml->channel->item as $item) {

            $date = empty($item->pubDate) ? $item->updated :$item->pubDate;

            $result = parse_url($item->link);

            $data['items'][] = array(
                'type' => 'rss',
                'title' =>  $item->title . '',
                'link' =>   $item->link . '',
                'description' => $item->description . '',
                //'date' =>   date('m-d-Y H:i', strtotime($date . '')),
                'date' => $this->splitTextByWords(  $item->summary , 40),
                'timestamp' =>  strtotime($date ),
                'from' => $result['host'],

            );
        }

        return $data;
    }


    /**
     * Parse feed xml into an array.
     *
     * @param object $feedXml SimpleXMLElementObject
     * @return array feed content
     * @access public
     */
    function parseRss2($feedXml) {
        $data = array();

        $data['title'] = $feedXml->channel->title . '';
        $data['link'] = $feedXml->channel->link . '';
        $data['description'] = $feedXml->channel->description . '';
        $data['parser'] = __CLASS__;
        $data['type'] = 'rss2';
        if(isset($feedXml->channel->updated))
            $data['updated'] = $feedXml->channel->updated;


        $namespaces = $feedXml->getNamespaces(true);
        foreach ($namespaces as $namespace => $namespaceValue) {
            $feedXml->registerXPathNamespace($namespace, $namespaceValue);
        }

        foreach ($feedXml->channel->item as $item) {
            $categories = array();
            foreach ($item->children() as $child) {
                if ($child->getName() == 'category') {
                    $categories[] = (string) $child;
                }
            }

            $author = null;
            if (!empty($namespaces['dc']) && $creator = $item->xpath('dc:creator')) {
                $author = (string) $creator[0];
            }

            $content = null;
            if (!empty($namespaces['encoded']) && $encoded = $item->xpath('content:encoded')) {
                $content = (string) $encoded[0];
            }

            $date = empty($item->pubDate) ? $item->updated :$item->pubDate;

            $result = parse_url($item->link);

            $data['items'][] = array(
                'type' => 'rss2',
                'title' =>  $item->title . '',
                'link' =>   $item->link . '',
                //'date' =>   date('m-d-Y H:i', strtotime($date . '')),
                'date' => $this->humanTiming(strtotime($date . '')),
                'timestamp' =>  strtotime($date ),
                'description' => $this->splitTextByWords(  $item->summary , 40),
                'categories' => $categories,
                'author' => array( 'name' => $author),
                'content' => $content,
                'from' => $result['host'],

            );

        }

        return $data;
    }

    /**
     * Parse feed xml into an array.
     *
     * @param object $feedXml SimpleXMLElementObject
     * @return array feed content
     * @access public
     */
    function parseAtom($feedXml) {
        $data = array();

        $data['title'] = $feedXml->title . '';
        foreach ($feedXml->link as $link) {
            $data['link'] = $link['href'] . '';
            break;
        }

        $data['description'] = $feedXml->subtitle . '';
        $data['parser'] = __CLASS__;
        $data['type'] = 'atom';
        if(isset($feedXml->channel->updated))
            $data['updated'] = $feedXml->channel->updated;


        foreach ($feedXml->entry as $item) {
            foreach ($item->link as $link) {
                $itemLink = $link['href'] . '';
                break;
            }

            $categories = array();
            foreach ($item->category as $category) {
                $categories[] = $category['term'] . '';
            }

            $date = empty($item->pubDate) ? $item->updated :$item->pubDate;

            $result = parse_url($itemLink);

            $data['items'][] = array(
                'type' => 'atom',
                'title' =>  $item->title . '',
                'link' =>   $itemLink . '',
               // 'date' =>   date('Y-m-d H:i:s', strtotime($date . '')),
                'date' => $this->humanTiming(strtotime($date . '')),
                'timestamp' =>  strtotime($date ),
                'description' => $this->splitTextByWords(  $item->summary , 40),
                'content' => $item->content . '',
                'categories' => $categories,
                'author' => array('name' => $item->author->name . '', 'url' => $item->author->uri . ''),
                'extra' => array('contentType' => $item->content['type'] . '', 'descriptionType' => $item->summary['type'] . ''),
                'from' => $result['host'],

            );
        }

        return $data;
    }

    /*
     * Convert date time in to Elapsed Time
     * param  Datetime
     * return Elapsed time
     * */
    function humanTiming ($time)
    {

        $time = time() - $time; // to get the time since that moment
        $time = ($time<1)? 1 : $time;
        $tokens = array (
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        foreach ($tokens as $unit => $text) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
        }

    }

    /*
     * Limit the contents to 40 number of words
     *
     * */
    function splitTextByWords($str, $words = 10)
    {
        $arr = preg_split("/[\s]+/", $str, $words+1);
        $arr = array_slice($arr, 0, $words);
        return join(' ', $arr);
    }
}

