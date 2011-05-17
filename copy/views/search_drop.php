<?php
/**
 *    This file is part of OXID eShop Community Edition.
 *
 *    OXID eShop Community Edition is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    OXID eShop Community Edition is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with OXID eShop Community Edition.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @package   views
 * @copyright (C) OXID eSales AG 2003-2011
 * @version OXID eShop CE
 * @version   SVN: $Id: search.php 28473 2010-06-19 13:40:35Z arvydas $
 */

/**
 * Articles searching class.
 * Performs searching through articles in database.
 */
class Search_drop extends oxUBase
{
    /**
     * Count of all found articles.
     * @var integer
     */
    protected $_iAllArtCnt     = 0;

    /**
     * Number of possible pages.
     * @var integer
     */
    protected $_iCntPages = null;

    /**
     * Current class template name.
     * @var string
     */
    protected $_sThisTemplate = 'search_drop.tpl';

    /**
     * List type
     * @var string
     */
    protected $_sListType = 'search';

    /**
     * Marked which defines if current view is sortable or not
     * @var bool
     */
    protected $_blShowSorting = true;

    /**
     * If search was empty
     * @var bool
     */
    protected $_blEmptySearch = null;

    /**
     * Similar recommendation lists
     * @var object
     */
    protected $_oRecommList = null;

    /**
     * Search parameter for Html
     * @var string
     */
    protected $_sSearchParamForHtml = null;

    /**
     * Search parameter
     * @var string
     */
    protected $_sSearchParam = null;

    /**
     * Searched category
     * @var string
     */
    protected $_sSearchCatId = null;

    /**
     * Searched vendor
     * @var string
     */
    protected $_sSearchVendor = null;

    /**
     * Searched manufacturer
     * @var string
     */
    protected $_sSearchManufacturer = null;

    /**
     * If called class is search
     * @var bool
     */
    protected $_blSearchClass = null;

    /**
     * Page navigation
     * @var object
     */
    protected $_oPageNavigation = null;

    /**
     * Current view search engine indexing state
     *
     * @var int
     */
    protected $_iViewIndexState = VIEW_INDEXSTATE_NOINDEXNOFOLLOW;

    /**
     * Fetches search parameter from GET/POST/session, prepares search
     * SQL (search::GetWhere()), and executes it forming the list of
     * found articles. Article list is stored at search::_aArticleList
     * array.
     *
     * Session variables:
     * <b>searchparam</b>
     *
     * Template variables:
     * <b>emptysearch</b>
     *
     * @return null
     */
    public function init()
    {
        parent::init();

        $myConfig = $this->getConfig();

        // #1184M - specialchar search
        $sSearchParamForQuery = oxConfig::getParameter( 'searchparam', true );

        // searching in category ?
        $sInitialSearchCat = $this->_sSearchCatId = rawurldecode( oxConfig::getParameter( 'searchcnid' ) );

        // searching in vendor #671
        $sInitialSearchVendor = $this->_sSearchVendor = rawurldecode( oxConfig::getParameter( 'searchvendor' ) );

        // searching in Manufacturer #671
        $sInitialSearchManufacturer = $this->_sSearchManufacturer = rawurldecode( oxConfig::getParameter( 'searchmanufacturer' ) );

        $this->_blEmptySearch = false;
        if ( !$sSearchParamForQuery && !$sInitialSearchCat && !$sInitialSearchVendor && !$sInitialSearchManufacturer ) {
            //no search string
            $this->_aArticleList = null;
            $this->_blEmptySearch = true;
            return false;
        }

        // config allows to search in vendors ?
        if ( !$myConfig->getConfigParam( 'bl_perfLoadVendorTree' ) ) {
            $sInitialSearchVendor = null;
        }

        // config allows to search in Manufacturers ?
        if ( !$myConfig->getConfigParam( 'bl_perfLoadManufacturerTree' ) ) {
            $sInitialSearchManufacturer = null;
        }

        // searching ..
        $oSearchHandler = oxNew( 'oxsearch' );
        $oSearchList = $oSearchHandler->getSearchArticles( $sSearchParamForQuery, $sInitialSearchCat, $sInitialSearchVendor, $sInitialSearchManufacturer, $this->getSortingSql( 'oxsearch' ) );

        // list of found articles
        $this->_aArticleList = $oSearchList;
        $this->_iAllArtCnt    = 0;

        // skip count calculation if no articles in list found
        if ( $oSearchList->count() ) {
            $this->_iAllArtCnt = $oSearchHandler->getSearchArticleCount( $sSearchParamForQuery, $sInitialSearchCat, $sInitialSearchVendor, $sInitialSearchManufacturer );
        }

        $iNrofCatArticles = (int) $myConfig->getConfigParam( 'iNrofCatArticles' );
        $iNrofCatArticles = $iNrofCatArticles?$iNrofCatArticles:1;
        $this->_iCntPages  = round( $this->_iAllArtCnt / $iNrofCatArticles + 0.49 );
    }

    /**
     * Forms serach navigation URLs, executes parent::render() and
     * returns name of template to render search::_sThisTemplate.
     *
     * Template variables:
     * <b>articlelist</b>, <b>searchparam</b>, <b>searchparamforhtml</b>
     * <b>searchcnid</b>, <b>searchvendor</b>, <b>searchlink</b>,
     * <b>pageNavigation</b>, <b>searchlink</b>,
     * <b>articlebargainlist</b>, <b>additionalparams</b>,
     * <b>searchmanufacturer</b>
     *
     * @return  string  current template file name
     */
    public function render()
    {
        $this->_aViewData['emptysearch'] = $this->isEmptySearch();

        $this->_aViewData['articlelist'] = $this->getArticleList();

        $this->_aViewData['similarrecommlist']  = $this->getSimilarRecommLists();

        $this->_aViewData['searchparamforhtml'] = $this->getSearchParamForHtml();
        $this->_aViewData['searchparam']        = $sSearchParamForQuery;//$this->getSearchParam();
        $this->_aViewData['searchcnid']         = $this->getSearchCatId();
        $this->_aViewData['searchvendor']       = $this->getSearchVendor();
        $this->_aViewData['searchmanufacturer']       = $this->getSearchManufacturer();

        $this->_aViewData['pageNavigation'] = $this->getPageNavigation();
        $this->_aViewData['actCategory']    = $this->getActiveCategory();

        parent::render();

        $myConfig = $this->getConfig();
        if ( $myConfig->getConfigParam( 'bl_rssSearch' ) ) {
            $oRss = oxNew('oxrssfeed');
            $sSearch = oxConfig::getParameter( 'searchparam', true );
            $sCnid = oxConfig::getParameter( 'searchcnid', true );
            $sVendor = oxConfig::getParameter( 'searchvendor', true );
            $sManufacturer = oxConfig::getParameter( 'searchmanufacturer', true );
            $this->addRssFeed($oRss->getSearchArticlesTitle($sSearch, $sCnid, $sVendor, $sManufacturer), $oRss->getSearchArticlesUrl($sSearch, $sCnid, $sVendor, $sManufacturer), 'searchArticles');
        }

        // processing list articles
        $this->_processListArticles();

        return $this->_sThisTemplate;
    }

    /**
     * Iterates through list articles and performs list view specific tasks:
     *  - sets type of link whicn needs to be generated (Manufacturer link)
     *
     * @return null
     */
    protected function _processListArticles()
    {
        $sAddDynParams = $this->getAddUrlParams();
        if ( $sAddDynParams && ( $aArtList = $this->getArticleList() ) ) {
            $blSeo = oxUtils::getInstance()->seoIsActive();
            foreach ( $aArtList as $oArticle ) {
                // appending std and dynamic urls
                if ( !$blSeo ) {
                    // only if seo is off..
                    $oArticle->appendStdLink( $sAddDynParams );
                }
                $oArticle->appendLink( $sAddDynParams );
            }
        }
    }

    /**
     * Returns additional URL parameters which must be added to list products urls
     *
     * @return string
     */
    public function getAddUrlParams()
    {
        $sAddParams  = parent::getAddUrlParams();
        $sAddParams .= ($sAddParams?'&amp;':'') . "listtype={$this->_sListType}";

        if ( $sParam = oxConfig::getParameter( 'searchparam', true ) ) {
            $sAddParams .= "&amp;searchparam=".rawurlencode($sParam);
        }

        if ( $sParam = oxConfig::getParameter( 'searchcnid' ) ) {
            $sAddParams .= "&amp;searchcnid=$sParam";
        }

        if ( $sParam = rawurldecode( oxConfig::getParameter( 'searchvendor' ) ) ) {
            $sAddParams .= "&amp;searchvendor=$sParam";
        }

        if ( $sParam = rawurldecode( oxConfig::getParameter( 'searchmanufacturer' ) ) ) {
            $sAddParams .= "&amp;searchmanufacturer=$sParam";
        }
        return $sAddParams;
    }

    /**
     * Sets search sorting config
     *
     * @param string $sCnid      sortable item id
     * @param string $sSortBy    sort field
     * @param string $sSortOrder sort order
     *
     * @return null
     */
    public function setItemSorting( $sCnid, $sSortBy, $sSortOrder  = null )
    {
        parent::setItemSorting( "oxsearch", $sSortBy, $sSortOrder );
    }

    /**
     * Returns search sorting config
     *
     * @param string $sCnid sortable item id
     *
     * @return string
     */
    public function getSorting( $sCnid )
    {
        return parent::getSorting( "oxsearch" );
    }

    /**
     * Template variable getter. Returns similar recommendation lists
     *
     * @return object
     */
    protected function _isSearchClass()
    {
        if ( $this->_blSearchClass === null ) {
            $this->_blSearchClass = false;
            if ( strtolower(oxConfig::getParameter( 'cl' )) == 'search' ) {
                $this->_blSearchClass = true;
            }
        }
        return $this->_blSearchClass;
    }
    /**
     * Template variable getter. Returns if searched was empty
     *
     * @return bool
     */
    public function isEmptySearch()
    {
        return $this->_blEmptySearch;
    }

    /**
     * Template variable getter. Returns searched article list
     *
     * @return array
     */
    public function getArticleList()
    {
        return $this->_aArticleList;
    }

    /**
     * Template variable getter. Returns similar recommendation lists
     *
     * @return object
     */
    public function getSimilarRecommLists()
    {
        if (!$this->getViewConfig()->getShowListmania()) {
            return false;
        }

        if ( $this->_oRecommList === null ) {
            $this->_oRecommList = false;
            $aList = $this->getArticleList();
            if ( $aList && $aList->count() > 0 ) {
                // loading recommlists
                $oRecommList = oxNew('oxrecommlist');
                $this->_oRecommList  = $oRecommList->getRecommListsByIds( $aList->arrayKeys());
            }
        }
        return $this->_oRecommList;
    }

    /**
     * Template variable getter. Returns search parameter for Html
     *
     * @return string
     */
    public function getSearchParamForHtml()
    {
        if ( $this->_sSearchParamForHtml === null ) {
            $this->_sSearchParamForHtml = false;
            if ( $this->_isSearchClass() ) {
                $this->_sSearchParamForHtml = oxConfig::getParameter( 'searchparam' );
            }
        }
        return $this->_sSearchParamForHtml;
    }

    /**
     * Template variable getter. Returns search parameter
     *
     * @return string
     */
    public function getSearchParam()
    {
        if ( $this->_sSearchParam === null ) {
            $this->_sSearchParam = false;
            if ( $this->_isSearchClass() ) {
                $this->_sSearchParam = rawurlencode( oxConfig::getParameter( 'searchparam', true ) );
            }
        }
        return $this->_sSearchParam;
    }

    /**
     * Template variable getter. Returns searched category id
     *
     * @return string
     */
    public function getSearchCatId()
    {
        if ( $this->_sSearchCatId === null ) {
            $this->_sSearchCatId = false;
            if ( $this->_isSearchClass() ) {
                $this->_sSearchCatId = rawurldecode( oxConfig::getParameter( 'searchcnid' ) );
            }
        }
        return $this->_sSearchCatId;
    }

    /**
     * Template variable getter. Returns searched vendor id
     *
     * @return string
     */
    public function getSearchVendor()
    {
        if ( $this->_sSearchVendor === null ) {
            $this->_sSearchVendor = false;
            if ( $this->_isSearchClass() ) {
                // searching in vendor #671
                $this->_sSearchVendor = rawurldecode( oxConfig::getParameter( 'searchvendor' ) );
            }
        }
        return $this->_sSearchVendor;
    }

    /**
     * Template variable getter. Returns searched Manufacturer id
     *
     * @return string
     */
    public function getSearchManufacturer()
    {
        if ( $this->_sSearchManufacturer === null ) {
            $this->_sSearchManufacturer = false;
            if ( $this->_isSearchClass() ) {
                // searching in Manufacturer #671
                $this->_sSearchManufacturer = rawurldecode( oxConfig::getParameter( 'searchmanufacturer' ) );
            }
        }
        return $this->_sSearchManufacturer;
    }

    /**
     * Template variable getter. Returns page navigation
     *
     * @return object
     */
    public function getPageNavigation()
    {
        if ( $this->_oPageNavigation === null ) {
            $this->_oPageNavigation = false;
            $this->_oPageNavigation = $this->generatePageNavigation();
        }
        return $this->_oPageNavigation;
    }

    /**
     * Template variable getter. Returns active search
     *
     * @return object
     */
    public function getActiveCategory()
    {
        return $this->getActSearch();
    }

}
