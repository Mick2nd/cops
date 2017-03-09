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


/**
 * This class is used to test new functionality for Virtual Libraries
 * @author Jürgen
 *
 */
class BookServicesVL extends BookServices
{
	const SQL_BOOKS_BY_PUBLISHER_DATA = SQL_BOOKS_BY_PUBLISHER_DATA;
	const SQL_BOOKS_BY_FIRST_LETTER_DATA = SQL_BOOKS_BY_FIRST_LETTER_DATA;
	const SQL_BOOKS_BY_AUTHOR_DATA = SQL_BOOKS_BY_AUTHOR_DATA;
	const SQL_BOOKS_BY_SERIE_DATA = SQL_BOOKS_BY_SERIE_DATA;
	const SQL_BOOKS_BY_TAG_DATA = SQL_BOOKS_BY_TAG_DATA;
	const SQL_BOOKS_BY_LANGUAGE_DATA = SQL_BOOKS_BY_LANGUAGE_DATA;
	const SQL_BOOKS_BY_RATING_DATA = SQL_BOOKS_BY_RATING_DATA;
	const SQL_BOOKS_RECENT_DATA = SQL_BOOKS_RECENT_DATA;
	const SQL_BOOKS_BY_CUSTOM_DATA = SQL_BOOKS_BY_CUSTOM_DATA;
	const SQL_BOOKS_ALL_DATA = SQL_BOOKS_ALL_DATA;

	private $dbProxy;
	private $bookFilter;

	/**
	 * Prepares for parsing and filtering
	 */
	public function __construct()
	{
		parent::__construct();

		$this->dbProxy = new DbProxy();
		$this->dbProxy->open(Base::getDb());

		$this->bookFilter = new BookFilter();
	}

	/**
	 * Do we really need to override this method or the underlying getEntryArray?
	 * The overridden getEntryArray needs 2 queries in an array, one for initial
	 * filtering and one to acquire the final data.
	 * {@inheritDoc}
	 * @see BookServices::getBooksByStartingLetter()
	 */
	public function getBooksByStartingLetter($letter, $n, $database = NULL, $numberPerPage = NULL)
	{
		return $this->getEntryArray (
				array(self::SQL_BOOKS_BY_FIRST_LETTER, self::SQL_BOOKS_BY_FIRST_LETTER_DATA),
				array ($letter . '%'),
				$n,
				$database,
				$numberPerPage);

	}

	public function getBooks($n)
	{
		list ($entryArray, $totalNumber) = $this->getEntryArray (
				array(self::SQL_BOOKS_ALL, self::SQL_BOOKS_ALL_DATA) ,
				array (),
				$n);
		return array ($entryArray, $totalNumber);
	}

	public function getBooksByPublisher($publisherId, $n)
	{
		return $this->getEntryArray (
				array(self::SQL_BOOKS_BY_PUBLISHER, self::SQL_BOOKS_BY_PUBLISHER_DATA),
				array ($publisherId),
				$n);
	}

	public function getBooksByAuthor($authorId, $n)
	{
		return $this->getEntryArray (
				array(self::SQL_BOOKS_BY_AUTHOR, self::SQL_BOOKS_BY_AUTHOR_DATA),
				array ($authorId),
				$n);
	}

	public function getBooksBySeries($serieId, $n)
	{
		return $this->getEntryArray (
				array(self::SQL_BOOKS_BY_SERIE, self::SQL_BOOKS_BY_SERIE_DATA),
				array ($serieId),
				$n);
	}

	public function getBooksByTag($tagId, $n)
	{
		return $this->getEntryArray (
				array(self::SQL_BOOKS_BY_TAG, self::SQL_BOOKS_BY_TAG_DATA),
				array ($tagId),
				$n);
	}

	public function getBooksByLanguage($languageId, $n)
	{
		return $this->getEntryArray (
				array(self::SQL_BOOKS_BY_LANGUAGE, self::SQL_BOOKS_BY_LANGUAGE_DATA),
				array ($languageId),
				$n);
	}

	public function getBooksByRating($ratingId, $n)
	{
		return $this->getEntryArray (
				array(self::SQL_BOOKS_BY_RATING, self::SQL_BOOKS_BY_RATING_DATA),
				array ($ratingId),
				$n);
	}

	public function getAllRecentBooks()
	{
		global $config;

		list ($entryArray, ) = $this->getEntryArray (
				array(self::SQL_BOOKS_RECENT . '1000', self::SQL_BOOKS_RECENT_DATA . $config['cops_recentbooks_limit']),
				array (),
				-1);
		return $entryArray;
	}

	/**
	 * Assumption: custom column required for filtering only
	 * @param $customColumn CustomColumn
	 * @param $id integer
	 * @param $n integer
	 * @return array
	 */
	public function getBooksByCustom($customColumn, $id, $n)
	{
		list($query, $params) = $customColumn->getQuery($id);
		$res = $this->getEntryArray (
				array($query, SQL_BOOKS_BY_CUSTOM_DATA),
				$params,
				$n);

		return $res;
	}

	/**
	 * Called by several "clients". Calls base method if $query is a single string.
	 * {@inheritDoc}
	 * @see BookServices::getEntryArray()
	 */
	public function getEntryArray ($query, $params, $n, $database = NULL, $numberPerPage = NULL)
	{
		if (gettype($query) === 'string')
		{
			$this->log->Info("'getEntryArray', invoking parent method.");
			return parent::getEntryArray($query, $params, $n, $database, $numberPerPage);
		}

		/* @var $totalNumber integer */
		/* @var $result PDOStatement */
		list($totalNumber, $result) = Base::executeQuery(
				$query[0],
				'books.id',
				'',
				$params,
				-1,
				$database,
				$numberPerPage);

		$ids = $result->fetchAll(PDO::FETCH_COLUMN);
		$result->closeCursor();
		$totalNumber = count($ids);

		$this->log->debug("Got $totalNumber book entries.");

		$vlib = $this->getFilterString();                                                       // the filter string from Facet -> virtual library
		if ($vlib !== '')
		{
			$this->log->debug("Applying a virtual lib filter '$vlib'");

			$this->bookFilter->prepareFilter($vlib, $this->dbProxy);                            // book filter / parser responsible for filtering
			$ids = $this->bookFilter->filterIds($ids);
		}

		list($totalNumber, $result) = Base::executeQuery(                                       // execute query
				$query[1],
				self::BOOK_COLUMNS,
				implode(', ', $ids),
				array(),
				$n,
				$database,
				$numberPerPage);

		if ($result === false)
		{
			$this->log->fatal(Base::getDb()->errorInfo());
			return array(array(), 0);
		}

		$entryArray = array();
		while ($post = $result->fetchObject())
		{
			$book = new Book ($post);
			array_push ($entryArray, $book->getEntry());
		}

		return array ($entryArray, $totalNumber);
	}

	/**
	 * Instead of returning the filter expression for a tag this function returns the vlib expression
	 * of Calibre. The function overrides of this class must use this filter appropriately.
	 * {@inheritDoc}
	 * @see BookServices::getFilterString()
	 */
	public function getFilterString()
	{
		$filter = getURLParam ('search', NULL);
		if (empty ($filter))
			return '';

			$filter = hex2bin($filter);                                         // get the virtual library back
			$this->log->info("Request for faceted list: '$filter'");

			return $filter;
	}
}
