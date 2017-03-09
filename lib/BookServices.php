<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Jürgen Habelt <juergen@habelt-jena.de>
 */

// Insert the path where you unpacked log4php
require_once('log4php/Logger.php');

require_once('virtualLibraries/virtualLibraries.php');
require_once('virtualLibraries/bookFilter.php');
require_once('virtualLibraries/dbProxy.php');

use VirtualLibraries\VirtualLibraries;
use VirtualLibraries\BookFilter;
use VirtualLibraries\DbProxy;


define ('SQL_BOOKS_BY_PUBLISHER_DATA',
		'select {0} from books_publishers_link, books ' . SQL_BOOKS_LEFT_JOIN . '
    where books_publishers_link.book = books.id and books.id in ({1}) order by publisher');

define ('SQL_BOOKS_BY_FIRST_LETTER_DATA',
		'select {0} from books \n' . SQL_BOOKS_LEFT_JOIN .
		'where books.id in ({1}) order by books.sort');

define ('SQL_BOOKS_BY_AUTHOR_DATA',
		'select {0} from books_authors_link, books ' . SQL_BOOKS_LEFT_JOIN . '
     left outer join books_series_link on books_series_link.book = books.id
     where books_authors_link.book = books.id and books.id in ({1}) order by series desc, series_index asc, pubdate asc');

define ('SQL_BOOKS_BY_SERIE_DATA',
		'select {0} from books_series_link, books ' . SQL_BOOKS_LEFT_JOIN . '
     where books_series_link.book = books.id and books.id in ({1}) order by series_index');

define ('SQL_BOOKS_BY_TAG_DATA',
		'select distinct {0} from books_tags_link, books ' . SQL_BOOKS_LEFT_JOIN . '
    where books_tags_link.book = books.id and books.id in ({1}) order by sort');

define ('SQL_BOOKS_BY_LANGUAGE_DATA',
		'select {0} from books_languages_link, books ' . SQL_BOOKS_LEFT_JOIN . '
    where books_languages_link.book = books.id and books.id in ({1}) order by sort');

define ('SQL_BOOKS_BY_RATING_DATA',
		'select {0} from books ' . SQL_BOOKS_LEFT_JOIN . '
    where books_ratings_link.book = books.id and books.id in ({1}) order by sort');

define ('SQL_BOOKS_RECENT_DATA',
		'select {0} from books ' . SQL_BOOKS_LEFT_JOIN . '
    where 1=1 and books.id in ({1}) order by timestamp desc limit ');

define ('SQL_BOOKS_BY_CUSTOM_DATA',
		'select {0} from books ' . SQL_BOOKS_LEFT_JOIN . '
    where books.id in ({1}) order by sort');

define ('SQL_BOOKS_ALL_DATA',
		'select {0} from books ' . SQL_BOOKS_LEFT_JOIN . ' where books.id in ({1}) order by books.sort ');

/**
 * Makes the static methods of Book to instance methods of this class
 * This makes it easier to overwrite methods and implement new functionality
 * @author Jürgen
 *
 */
class BookServices
{
	const ALL_BOOKS_UUID = 'urn:uuid';
	const ALL_BOOKS_ID = 'cops:books';
	const ALL_RECENT_BOOKS_ID = 'cops:recentbooks';
	const BOOK_COLUMNS = 'books.id as id, books.title as title, text as comment, path, timestamp, pubdate, series_index, uuid, has_cover, ratings.rating';

	const SQL_BOOKS_LEFT_JOIN = SQL_BOOKS_LEFT_JOIN;
	const SQL_BOOKS_ALL = SQL_BOOKS_ALL;
	const SQL_BOOKS_BY_PUBLISHER = SQL_BOOKS_BY_PUBLISHER;
	const SQL_BOOKS_BY_FIRST_LETTER = SQL_BOOKS_BY_FIRST_LETTER;
	const SQL_BOOKS_BY_AUTHOR = SQL_BOOKS_BY_AUTHOR;
	const SQL_BOOKS_BY_SERIE = SQL_BOOKS_BY_SERIE;
	const SQL_BOOKS_BY_TAG = SQL_BOOKS_BY_TAG;
	const SQL_BOOKS_BY_LANGUAGE = SQL_BOOKS_BY_LANGUAGE;
	const SQL_BOOKS_BY_CUSTOM = SQL_BOOKS_BY_CUSTOM;
	const SQL_BOOKS_BY_CUSTOM_BOOL_TRUE = SQL_BOOKS_BY_CUSTOM_BOOL_TRUE;
	const SQL_BOOKS_BY_CUSTOM_BOOL_FALSE = SQL_BOOKS_BY_CUSTOM_BOOL_FALSE;
	const SQL_BOOKS_BY_CUSTOM_BOOL_NULL = SQL_BOOKS_BY_CUSTOM_BOOL_NULL;
	const SQL_BOOKS_BY_CUSTOM_RATING = SQL_BOOKS_BY_CUSTOM_RATING;
	const SQL_BOOKS_BY_CUSTOM_RATING_NULL = SQL_BOOKS_BY_CUSTOM_RATING_NULL;
	const SQL_BOOKS_BY_CUSTOM_DATE = SQL_BOOKS_BY_CUSTOM_DATE;
	const SQL_BOOKS_BY_CUSTOM_DIRECT = SQL_BOOKS_BY_CUSTOM_DIRECT;
	const SQL_BOOKS_BY_CUSTOM_DIRECT_ID = SQL_BOOKS_BY_CUSTOM_DIRECT_ID;
	const SQL_BOOKS_QUERY = SQL_BOOKS_QUERY;
	const SQL_BOOKS_RECENT = SQL_BOOKS_RECENT;
	const SQL_BOOKS_BY_RATING = SQL_BOOKS_BY_RATING;

	const BAD_SEARCH = 'QQQQQ';

	static private $default;
	protected $log;

	/**
	 * Error handler added to the php error handler
	 * @param integer $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param integer $errline
	 * @param array $errcontext
	 * @return boolean
	 */
	public function errorHandler ($errno, $errstr, $errfile = null, $errline = 0, array $errcontext = null)
	{
		$this->log->error("$errstr in $errfile on $errline");
		return false;
	}

	/**
	 * Performs initialisation, here: prepares for logging
	 * advantage of log4php - no absolute file path, it's relative to config file config.xml
	 */
	protected function __construct()
	{
		// log4php framework
		Logger::configure('config.xml');
		$this->log = Logger::getLogger(__CLASS__);
		$this->log->debug("Test log with log4php");

		// php error handling
		set_error_handler(array($this, 'errorHandler'), E_ALL);
		trigger_error("Test trigger error", E_USER_NOTICE);
	}

	/**
	 * The one and only instance
	 * @return BookServices
	 */
	static public function getDefault()
	{
		global $config;
		 
		if (BookServices::$default === null)
		{
			ini_set("log_errors", 1);                                           // do this before instantiating ... php logging prepared !
			ini_set("error_reporting", E_ALL);
			ini_set("error_log", "/share/MD0_DATA/Web/cops/phplog.txt");        // TODO: change for production!

			if (is_null($config[VirtualLibraries::USE_VIRTUAL_LIBRARIES]) ||
					$config[VirtualLibraries::USE_VIRTUAL_LIBRARIES] == "0")
			{
				BookServices::$default = new BookServices();
			}
			else
			{
				BookServices::$default = new BookServicesVL();
			}
		}
		return BookServices::$default;
	}

	public function getEntryIdByLetter($startingLetter)
	{
		return self::ALL_BOOKS_ID . ':letter:' . $startingLetter;
	}

	public function getFilterString()
	{
		$filter = getURLParam ('tag', NULL);
		if (empty ($filter))
			return '';

			$exists = true;
			if (preg_match ('/^!(.*)$/', $filter, $matches))
			{
				$exists = false;
				$filter = $matches[1];
			}

			$result = 'exists (select null from books_tags_link, tags where books_tags_link.book = books.id and books_tags_link.tag = tags.id and tags.name = "' . $filter . '")';
			if (!$exists)
			{
				$result = 'not ' . $result;
			}

			return 'and ' . $result;
	}

	public function getBookCount($database = NULL)
	{
		return Base::executeQuerySingle ('select count(*) from books', $database);
	}

	public function getCount ()
	{
		global $config;

		$nBooks = Base::executeQuerySingle('select count(*) from books');
		$result = array();
		$entry = new Entry(
				localize('allbooks.title'), self::ALL_BOOKS_ID,
				str_format(localize('allbooks.alphabetical', $nBooks), $nBooks),
				'text',
				array(new LinkNavigation('?page=' . Base::PAGE_ALL_BOOKS)),
				'', $nBooks);

				array_push($result, $entry);

				if ($config['cops_recentbooks_limit'] > 0)
				{
					$entry = new Entry(localize('recent.title'),
							self::ALL_RECENT_BOOKS_ID,
							str_format(localize('recent.list'), $config['cops_recentbooks_limit']), 'text',
							array(new LinkNavigation('?page=' . Base::PAGE_ALL_RECENT_BOOKS)),
							'', $config['cops_recentbooks_limit']);

							array_push($result, $entry);
				}
				return $result;
	}

	public function getBooksByAuthor($authorId, $n)
	{
		return $this->getEntryArray (self::SQL_BOOKS_BY_AUTHOR, array ($authorId), $n);
	}

	public function getBooksByRating($ratingId, $n)
	{
		return $this->getEntryArray (self::SQL_BOOKS_BY_RATING, array ($ratingId), $n);
	}

	public function getBooksByPublisher($publisherId, $n)
	{
		return $this->getEntryArray (self::SQL_BOOKS_BY_PUBLISHER, array ($publisherId), $n);
	}

	public function getBooksBySeries($serieId, $n)
	{
		return $this->getEntryArray (self::SQL_BOOKS_BY_SERIE, array ($serieId), $n);
	}

	public function getBooksByTag($tagId, $n)
	{
		return $this->getEntryArray (self::SQL_BOOKS_BY_TAG, array ($tagId), $n);
	}

	public function getBooksByLanguage($languageId, $n)
	{
		return $this->getEntryArray (self::SQL_BOOKS_BY_LANGUAGE, array ($languageId), $n);
	}

	/**
	 * @param $customColumn CustomColumn
	 * @param $id integer
	 * @param $n integer
	 * @return array
	 */
	public function getBooksByCustom($customColumn, $id, $n)
	{
		list($query, $params) = $customColumn->getQuery($id);
		$res = $this->getEntryArray ($query, $params, $n);

		return $res;
	}

	public function getBookById($bookId)
	{
		$result = Base::getDb ()->prepare(
				'select ' . self::BOOK_COLUMNS .
				'\nfrom books ' . self::SQL_BOOKS_LEFT_JOIN .
				'\nwhere books.id = ?');

		$result->execute (array ($bookId));
		while ($post = $result->fetchObject ())
		{
			$book = new Book ($post);
			return $book;
		}
		return NULL;
	}

	public function getBookByDataId($dataId)
	{
		$result = Base::getDb ()->prepare(
				'select ' . self::BOOK_COLUMNS .
				'\n, data.name, data.format \nfrom data, books ' . self::SQL_BOOKS_LEFT_JOIN .
				'\nwhere data.book = books.id and data.id = ?');

		$result->execute (array ($dataId));
		while ($post = $result->fetchObject ())
		{
			$book = new Book ($post);
			$data = new Data ($post, $book);
			$data->id = $dataId;
			$book->datas = array ($data);
			return $book;
		}
		return NULL;
	}

	public function getBooksByQuery($query, $n, $database = NULL, $numberPerPage = NULL)
	{
		$i = 0;
		$critArray = array ();
		foreach (array (
				PageQueryResult::SCOPE_AUTHOR,
				PageQueryResult::SCOPE_TAG,
				PageQueryResult::SCOPE_SERIES,
				PageQueryResult::SCOPE_PUBLISHER,
				PageQueryResult::SCOPE_BOOK) as $key)
		{
			if (in_array($key, getCurrentOption ('ignored_categories')) ||
					(!array_key_exists ($key, $query) && !array_key_exists ('all', $query)))
			{
				$critArray [$i] = self::BAD_SEARCH;
			}
			else
			{
				if (array_key_exists ($key, $query))
				{
					$critArray [$i] = $query [$key];
				}
				else
				{
					$critArray [$i] = $query ['all'];
				}
			}
			$i++;
		}
		return $this->getEntryArray (self::SQL_BOOKS_QUERY, $critArray, $n, $database, $numberPerPage);
	}

	public function getBooks($n)
	{
		list ($entryArray, $totalNumber) = $this->getEntryArray (self::SQL_BOOKS_ALL , array (), $n);
		return array ($entryArray, $totalNumber);
	}

	public function getAllBooks()
	{
		/* @var $result PDOStatement */

		list (, $result) = Base::executeQuery (
				"select {0}\nfrom books\ngroup by substr (upper (sort), 1, 1)\norder by substr (upper (sort), 1, 1)",
				"substr (upper (sort), 1, 1) as title, count(*) as count",
				$this->getFilterString(),
				array (), -1);

		$entryArray = array();
		while ($post = $result->fetchObject ())
		{
			array_push (
					$entryArray,
					new Entry (
							$post->title,
							$this->getEntryIdByLetter($post->title),
							str_format (localize('bookword', $post->count), $post->count),
							'text',
							array (new LinkNavigation('?page=' . Base::PAGE_ALL_BOOKS_LETTER . '&id=' . rawurlencode ($post->title))),
							'', $post->count));
		}
		return $entryArray;
	}

	public function getBooksByStartingLetter($letter, $n, $database = NULL, $numberPerPage = NULL)
	{
		return $this->getEntryArray (self::SQL_BOOKS_BY_FIRST_LETTER, array ($letter . '%'), $n, $database, $numberPerPage);
	}

	public function getEntryArray ($query, $params, $n, $database = NULL, $numberPerPage = NULL)
	{
		/* @var $totalNumber integer */
		/* @var $result PDOStatement */
		list($totalNumber, $result) = Base::executeQuery($query, self::BOOK_COLUMNS, $this->getFilterString (), $params, $n, $database, $numberPerPage);

		$entryArray = array();
		while ($post = $result->fetchObject())
		{
			$book = new Book ($post);
			array_push ($entryArray, $book->getEntry());
		}
		return array ($entryArray, $totalNumber);
	}

	public function getAllRecentBooks()
	{
		global $config;

		list ($entryArray, ) = $this->getEntryArray (self::SQL_BOOKS_RECENT . $config['cops_recentbooks_limit'], array (), -1);
		return $entryArray;
	}

}
