<?php
/**
 * COPS (Calibre OPDS PHP Server) class file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Jürgen Habelt <juergen@habelt-jena.de>
 */

// Insert the path where you unpacked log4php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use VirtualLibraries\VirtualLibraries;


// Silly thing because PHP forbid string concatenation in class const
define ('SQL_BOOKS_LEFT_JOIN', 'left outer join comments on comments.book = books.id
                                left outer join books_ratings_link on books_ratings_link.book = books.id
                                left outer join ratings on books_ratings_link.rating = ratings.id ');
define ('SQL_BOOKS_ALL', 'select {0} from books ' . SQL_BOOKS_LEFT_JOIN . ' order by books.sort ');
define ('SQL_BOOKS_BY_PUBLISHER', 'select {0} from books_publishers_link, books ' . SQL_BOOKS_LEFT_JOIN . '
                                                    where books_publishers_link.book = books.id and publisher = ? {1} order by publisher');
define ('SQL_BOOKS_BY_FIRST_LETTER', 'select {0} from books ' . SQL_BOOKS_LEFT_JOIN . '
                                                    where upper (books.sort) like ? order by books.sort');
define ('SQL_BOOKS_BY_AUTHOR', 'select {0} from books_authors_link, books ' . SQL_BOOKS_LEFT_JOIN . '
                                                    left outer join books_series_link on books_series_link.book = books.id
                                                    where books_authors_link.book = books.id and author = ? {1} order by series desc, series_index asc, pubdate asc');
define ('SQL_BOOKS_BY_SERIE', 'select {0} from books_series_link, books ' . SQL_BOOKS_LEFT_JOIN . '
                                                    where books_series_link.book = books.id and series = ? {1} order by series_index');
define ('SQL_BOOKS_BY_TAG', 'select {0} from books_tags_link, books ' . SQL_BOOKS_LEFT_JOIN . '
                                                    where books_tags_link.book = books.id and tag = ? {1} order by sort');
define ('SQL_BOOKS_BY_LANGUAGE', 'select {0} from books_languages_link, books ' . SQL_BOOKS_LEFT_JOIN . '
                                                    where books_languages_link.book = books.id and lang_code = ? {1} order by sort');
define ('SQL_BOOKS_BY_CUSTOM', 'select {0} from {2}, books ' . SQL_BOOKS_LEFT_JOIN . '
                                                    where {2}.book = books.id and {2}.{3} = ? {1} order by sort');
define ('SQL_BOOKS_BY_CUSTOM_BOOL_TRUE', 'select {0} from {2}, books ' . SQL_BOOKS_LEFT_JOIN . '
                                                    where {2}.book = books.id and {2}.value = 1 {1} order by sort');
define ('SQL_BOOKS_BY_CUSTOM_BOOL_FALSE', 'select {0} from {2}, books ' . SQL_BOOKS_LEFT_JOIN . '
                                                    where {2}.book = books.id and {2}.value = 0 {1} order by sort');
define ('SQL_BOOKS_BY_CUSTOM_BOOL_NULL', 'select {0} from books ' . SQL_BOOKS_LEFT_JOIN . '
                                                    where books.id not in (select book from {2}) {1} order by sort');
define ('SQL_BOOKS_BY_CUSTOM_RATING', 'select {0} from books ' . SQL_BOOKS_LEFT_JOIN . '
                                                    left join {2} on {2}.book = books.id
                                                    left join {3} on {3}.id = {2}.{4}
                                                    where {3}.value = ?  order by sort');
define ('SQL_BOOKS_BY_CUSTOM_RATING_NULL', 'select {0} from books ' . SQL_BOOKS_LEFT_JOIN . '
								                    left join {2} on {2}.book = books.id
								                    left join {3} on {3}.id = {2}.{4}
                                                    where ((books.id not in (select {2}.book from {2})) or ({3}.value = 0)) {1} order by sort');
define ('SQL_BOOKS_BY_CUSTOM_DATE', 'select {0} from {2}, books ' . SQL_BOOKS_LEFT_JOIN . '
                                                    where {2}.book = books.id and date({2}.value) = ? {1} order by sort');
define ('SQL_BOOKS_BY_CUSTOM_DIRECT', 'select {0} from {2}, books ' . SQL_BOOKS_LEFT_JOIN . '
                                                    where {2}.book = books.id and {2}.value = ? {1} order by sort');
define ('SQL_BOOKS_BY_CUSTOM_DIRECT_ID', 'select {0} from {2}, books ' . SQL_BOOKS_LEFT_JOIN . '
                                                    where {2}.book = books.id and {2}.id = ? {1} order by sort');
define ('SQL_BOOKS_QUERY', 'select {0} from books ' . SQL_BOOKS_LEFT_JOIN . '
                                                    where (
                                                    exists (select null from authors, books_authors_link where book = books.id and author = authors.id and authors.name like ?) or
                                                    exists (select null from tags, books_tags_link where book = books.id and tag = tags.id and tags.name like ?) or
                                                    exists (select null from series, books_series_link on book = books.id and books_series_link.series = series.id and series.name like ?) or
                                                    exists (select null from publishers, books_publishers_link where book = books.id and books_publishers_link.publisher = publishers.id and publishers.name like ?) or
                                                    title like ?) {1} order by books.sort');
define ('SQL_BOOKS_RECENT', 'select {0} from books ' . SQL_BOOKS_LEFT_JOIN . '
                                                    where 1=1 {1} order by timestamp desc limit ');
define ('SQL_BOOKS_BY_RATING', 'select {0} from books ' . SQL_BOOKS_LEFT_JOIN . '
                                                    where books_ratings_link.book = books.id and ratings.id = ? {1} order by sort');



define ('SQL_BOOKS_BY_PUBLISHER_DATA',
		'select {0} from books_publishers_link, books ' . SQL_BOOKS_LEFT_JOIN . '
    where books_publishers_link.book = books.id and books.id in ({1}) order by publisher');

define ('SQL_BOOKS_BY_FIRST_LETTER_DATA',
		'select {0} from books ' . SQL_BOOKS_LEFT_JOIN .
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
		$this->log = \Logger::getLogger(__CLASS__);
		$this->log->debug("Test log with log4php");

		// php error handling
		set_error_handler(array($this, 'errorHandler'), E_ALL);
		trigger_error("Test trigger", E_USER_NOTICE);
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
